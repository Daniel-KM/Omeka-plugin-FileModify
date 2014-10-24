<?php
/**
 * Modify (convert, compress, watermark, rename or any other command) uploaded
 * file before saving it in archive folder and before creating metadata in Omeka
 * database. Renaming requires Archive Repertory plugin.
 *
 * @copyright Daniel Berthereau, 2012-2013
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2-en.txt
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
        'file_modify_convert_resolution' => '',
        'file_modify_convert_quality' => '',
        'file_modify_convert_resize' => '',
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
            . ' South 25 95';
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
        $view = $args['view'];
        echo $view->partial(
            'plugins/file-modify-config-form.php',
            array(
                'view' => $view,
            )
        );
    }

    /**
     * Processes the configuration form.
     *
     * @return void
     */
    public function hookConfig($args)
    {
        $post = $args['post'];
        foreach ($post as $key => $value) {
            set_option($key, $value);
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
            // Uses ImageMagick convert command only on images.
            if (strstr($file->mime_type, '/', TRUE) == 'image') {
                self::_convert($file);
            }

            // Preprocess command.
            if ((boolean) get_option('file_modify_preprocess')) {
                require_once 'libraries' . DIRECTORY_SEPARATOR . 'file_modify_preprocess.php';
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
                require_once 'libraries' . DIRECTORY_SEPARATOR . 'file_modify_rename.php';
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
     * Convert an image with ImageMagick.
     */
    public static function _convert($file)
    {
        $convertPath = self::_getPathToImageMagick();

        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $file->filename;
        $filePathTemp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . pathinfo($file->filename, PATHINFO_FILENAME) . '_' . date('Ymd-His') . '.' . pathinfo($file->filename, PATHINFO_EXTENSION);

        $resolution = get_option('file_modify_convert_resolution') ?
            '-resample ' . escapeshellarg(get_option('file_modify_convert_resolution')) :
            '';

        $quality = get_option('file_modify_convert_quality') ?
            '-quality ' . escapeshellarg(get_option('file_modify_convert_quality')) :
            '';

        $resize = get_option('file_modify_convert_resize') ?
            '-resize ' . escapeshellarg(get_option('file_modify_convert_resize')) :
            '';

        $append = get_option('file_modify_convert_append') ?
            escapeshellarg(get_option('file_modify_convert_append')) :
            '';

        if ($resolution . $quality . $resize . $append == '') {
            return true;
        }

        // Convert command.
        $command = join(' ', array(
            $convertPath,
            escapeshellarg($filePath),
            $resolution,
            $quality,
            $resize,
            $append,
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
     * @see application/libraries/Omeka/File/Derivative/Image.php
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