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
        'file_modify_command' => '',
        'file_modify_rename' => FALSE,
    );

    /**
     * Installs the plugin.
     */
    public function hookInstall()
    {
        $this->_installOptions();
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
    public function hookConfigForm()
    {
        require 'config_form.php';
    }

    /**
     * Saves plugin configuration.
     *
     * @param array Options set in the config form.
     */
    public function hookConfig($args)
    {
        $post = $args['post'];

        // Save settings.
        set_option('file_modify_convert_resolution', $post['file_modify_convert_resolution']);
        set_option('file_modify_convert_quality', $post['file_modify_convert_quality']);
        set_option('file_modify_convert_resize', $post['file_modify_convert_resize']);
        set_option('file_modify_convert_append', $post['file_modify_convert_append']);
        set_option('file_modify_command', $post['file_modify_command']);
        set_option('file_modify_rename', (int) (boolean) $post['file_modify_rename']);
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

            // General command.
            if (get_plugin_ini('FileModify', 'file_modify_allow_command') == 'TRUE') {
                require_once('libraries' . DIRECTORY_SEPARATOR . 'file_modify_command.php');
                $result = file_modify_command($file);
                if (!empty($result)) {
                    throw new Zend_Exception('Something went wrong when applying a command on the uploaded file with File Modify plugin. Please notify an administrator.');
                }
            }

            // Rename command.
            if ((boolean) get_option('file_modify_rename')
                    && plugin_is_active('ArchiveRepertory')
                    && get_option('archive_repertory_keep_original_filename')
                ) {
                // Check if filename is a good one or not.
                require_once('libraries' . DIRECTORY_SEPARATOR . 'file_modify_rename.php');
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
            return TRUE;
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
            // For security reason and to use only Omeka API, we do the move in
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

            return TRUE;
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
