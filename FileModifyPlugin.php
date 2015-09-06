<?php
/**
 * Modify (convert, compress, watermark, rename or any other command) uploaded
 * file before saving it in archive folder and before creating metadata in Omeka
 * database. Renaming requires Archive Repertory plugin.
 *
 * @copyright Daniel Berthereau, 2012-2015
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt
 * @package FileModify
 */

/**
 * Contains code used to integrate the plugin into Omeka.
 *
 * @package FileModify
 */
class FileModifyPlugin extends Omeka_Plugin_AbstractPlugin
{
    /**
     * Command to call to create derivatives.
     */
    const IMAGEMAGICK_CONVERT_COMMAND = 'convert';

    /**
     * @var array This plugin's hooks.
     */
    protected $_hooks = array(
        'install',
        'upgrade',
        'uninstall',
        'config_form',
        'config',
        'before_save_file',
    );

    /**
     * @var array This plugin's options.
     */
    protected $_options = array(
        'file_modify_backup_path' => '',
        'file_modify_convert_append' => '',
        'file_modify_preprocess' => false,
        'file_modify_preprocess_parameters' => '',
        'file_modify_rename' => false,
    );

    /**
     * Installs the plugin.
     */
    public function hookInstall()
    {
        $this->_options['file_modify_preprocess_parameters'] = realpath(dirname(__FILE__) . '/views/shared/images/qrcode.png')
            . ', South, 25, 95';
        $this->_installOptions();
    }

    /**
     * Upgrades the plugin.
     */
    public function hookUpgrade($args)
    {
        $oldVersion = $args['old_version'];
        $newVersion = $args['new_version'];

        if (version_compare($oldVersion, '2.2', '<')) {
            delete_option('file_modify_command');
        }

        if (version_compare($oldVersion, '2.3', '<')) {
            $append = get_option('file_modify_convert_resolution')
                ? '-resample ' . get_option('file_modify_convert_resolution')
                : '';
            $append .= get_option('file_modify_convert_quality')
                ? ' -quality ' . get_option('file_modify_convert_quality')
                : '';
            $append .= get_option('file_modify_convert_resize')
                ? ' -resize ' . get_option('file_modify_convert_resize')
                : '';
            $append .= ' ' . get_option('file_modify_convert_append');

            set_option('file_modify_convert_append', trim($append));
            delete_option('file_modify_convert_resolution');
            delete_option('file_modify_convert_quality');
            delete_option('file_modify_convert_resize');
        }
    }

    /**
     * Uninstalls the plugin.
     */
    public function hookUninstall()
    {
        $this->_uninstallOptions();
    }

    /**
     * Shows plugin configuration page.
     */
    public function hookConfigForm($args)
    {
        $view = get_view();
        echo $view->partial('plugins/file-modify-config-form.php');
    }

    /**
     * Processes the configuration form.
     *
     * @return void
     */
    public function hookConfig($args)
    {
        $post = $args['post'];

        // Specific check for the backup path.
        if (!empty($post['file_modify_backup_path'])) {
            $post['file_modify_backup_path'] = realpath($post['file_modify_backup_path']);
        }
        if (empty($post['file_modify_backup_path'])) {
            $post['file_modify_backup_path'] = '';
        }

        foreach ($this->_options as $optionKey => $optionValue) {
            if (isset($post[$optionKey])) {
                set_option($optionKey, $post[$optionKey]);
            }
        }
    }

    /**
     * Manages transformation of a file before saving it.
     */
    public function hookBeforeSaveFile($args)
    {
        $post = $args['post'];
        $file = $args['record'];

        if ($args['insert']) {
            // Save the file in the uploaded folder if wanted.
            if ($this->_backup($file) === false) {
                throw new Zend_Exception('Unable to backup original file before processing. Please notify an administrator.');
            }

            // Watermarks images only.
            if (strstr($file->mime_type, '/', TRUE) == 'image') {
                self::_convert($file);
            }

            // Preprocess command.
            if ((boolean) get_option('file_modify_preprocess')) {
                require_once 'libraries' . DIRECTORY_SEPARATOR . 'FileModify' . DIRECTORY_SEPARATOR . 'Preprocess.php';
                $result = file_modify_preprocess($file, get_option('file_modify_preprocess_parameters'));
                if (!empty($result)) {
                    throw new Zend_Exception('Something went wrong when applying a command on the uploaded file with File Modify plugin. Please notify an administrator.');
                }
            }

            // Rename command.
            if ((boolean) get_option('file_modify_rename')
                    && plugin_is_active('ArchiveRepertory')
                    && get_option('archive_repertory_file_keep_original_name')
                ) {
                // Check if filename is a good one or not.
                require_once 'libraries' . DIRECTORY_SEPARATOR . 'FileModify' . DIRECTORY_SEPARATOR . 'Rename.php';
                $new_filename = file_modify_rename($file);

                if (!empty($new_filename)
                        && ($file->filename != $new_filename)
                    ) {
                    $operation = new Omeka_Storage_Adapter_Filesystem(array(
                        'localDir' => sys_get_temp_dir(),
                        'webDir' => sys_get_temp_dir(),
                    ));
                    $operation->move($file->filename, $new_filename);

                    // Update file in database (automatically done because it's an object in a hook).
                    $file->filename = $new_filename;
                    $file->original_filename = $new_filename;
                }
            }
        }
    }

    /**
     * Backup original file if wanted.
     *
     * @return boolean|null Null if not to be done, true if success, false else.
     */
    protected function _backup($file)
    {
        $backupPath = get_option('file_modify_backup_path');
        if (!$backupPath) {
            return null;
        }

        $filePath = $file->getPath('original');

        // TODO Use multiple derivatives when committed.
        $backupItemPath = $backupPath . DIRECTORY_SEPARATOR . $file->item_id;
        if (!is_dir($backupItemPath)) {
            @mkdir($backupItemPath, 0755, true);
            if (!is_dir($backupItemPath)) {
                return false;
            }
        }

        // Avoid overwriting and rename new file.
        $backupFilePath = $backupItemPath . DIRECTORY_SEPARATOR . $file->original_filename;
        if (is_file($backupFilePath)) {
            $folder = $backupItemPath;
            $checkname = $name = pathinfo($file->original_filename, PATHINFO_FILENAME);
            $extension = pathinfo($file->original_filename, PATHINFO_EXTENSION);
            $i = 1;
            while (glob($folder . DIRECTORY_SEPARATOR . $checkName . '{.*,.,\,,}', GLOB_BRACE)) {
                $checkName = $name . '.' . $i++;
            }
            $backupFilePath = $backupItemPath . DIRECTORY_SEPARATOR
                . $checkName
                . ($extension ? '.' . $extension : '');
        }
        $result = copy($filePath, $backupFilePath);
        return $result;
    }

    /**
     * Convert an image with ExternalImageMagick.
     *
     * @todo Possibility to use GD or Imagick.
     */
    public static function _convert($file)
    {
        $append = get_option('file_modify_convert_append');
        if (empty($append)) {
            return true;
        }

        $convertPath = self::_getPathToImageMagick();

        $filePath = $file->getPath('original');
        $filePathTemp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . pathinfo($file->filename, PATHINFO_FILENAME) . '_' . date('Ymd-His') . '.' . pathinfo($file->filename, PATHINFO_EXTENSION);

        // Convert command.
        $command = join(' ', array(
            $convertPath,
            escapeshellarg($filePath),
            ' ' . $append . ' ',
            escapeshellarg($filePathTemp)));

        exec($command, $result_array, $result_value);

        if (empty($result_value)) {
            // For security reason and to use only Omeka Core, we do the move in
            // three times.
            $filePath = pathinfo($filePath, PATHINFO_BASENAME);
            $filePathTemp = pathinfo($filePathTemp, PATHINFO_BASENAME);
            $filePathSave = pathinfo($filePath, PATHINFO_FILENAME) . '_' . date('Ymd-His') . '_ori.' . pathinfo($filePath, PATHINFO_EXTENSION);

            // Save original file.
            $operation = new Omeka_Storage_Adapter_Filesystem(array(
                'localDir' => sys_get_temp_dir(),
                'webDir' => sys_get_temp_dir(),
            ));
            $operation->move($filePath, $filePathSave);

            // Move modified file.
            $operation->move($filePathTemp, $filePath);

            // Delete original file.
            $operation->delete($filePathSave);

            return true;
        }
        else {
            throw new Zend_Exception('Something went wrong with image conversion (File Modify plugin). Please notify an administrator.');
        }
    }

    /**
     * Retrieve the directory path to the ImageMagick 'convert' executable.
     *
     * @see application/libraries/Omeka/File/Derivative/Strategy/ExternalImageMagick.php
     */
    protected static function _getPathToImageMagick()
    {
        $rawPath = get_option('path_to_convert');
        // Assert that this is both a valid path and a directory (cannot be a
        // script).
        if (($cleanPath = realpath($rawPath)) && is_dir($cleanPath)) {
            $imPath = rtrim($cleanPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . self::IMAGEMAGICK_CONVERT_COMMAND;
            return $imPath;
        } else {
            throw new Exception('ImageMagick is not properly configured: invalid directory given for the ImageMagick command!');
        }
    }
}
