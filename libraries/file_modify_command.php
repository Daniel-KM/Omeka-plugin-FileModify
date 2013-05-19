<?php

/**
 * @file
 * This file should be adapted to your needs in order to transform uploaded
 * files before import in the archive folder and the database of Omeka.
 *
 * @note This file must be adapted to your needs.
 *
 * @todo Create specific exception class.
 */

/**
 * Run a general command on a file. Function should be executed here.
 *
 * The command can be hard coded here or filled by the admin of the site on the
 * config page of this plugin. You should check mime type when necessary.
 *
 * @warning This command should be enabled in the plugin.ini file only if you
 * trust the site manager.
 *
 * @note ImageMagick convert command is directly managed by the plugin.
 *
 * @return NULL if there is no error.
 */
function file_modify_command($file)
{
    // Remove this option if you want to hard code the command.
    $command = get_option('file_modify_command');
    if ($command) {
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $file->filename;

        // Placeholder is %filepath%.
        if (strpos($command, '%filepath%')) {
            $command = str_replace('%filepath%', escapeshellarg($filePath), $command);
        }

        // Execute command.
        exec($command, $result_array, $result_value);

        // Return error code if any.
        return $result_value;
    }
}
