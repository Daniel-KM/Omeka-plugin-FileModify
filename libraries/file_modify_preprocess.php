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
 * You should check mime type when necessary.
 *
 * @note ImageMagick convert command is managed by the plugin for basic needs.
 *
 * @example This example adds a watermark to the image, with parameters.
 *
 * @return NULL if there is no error, else the error code.
 */
function file_modify_preprocess($file, $args)
{
    // Only process images.
    if (strstr($file->mime_type, '/', TRUE) != 'image') {
        return;
    }

    // Default values.
    $watermark = realpath(dirname(__FILE__) . '/../views/shared/images/qrcode.png');
    $gravity = 'South';
    $size = 33;
    $dissolve = 95;

    // Get parameters.
    $args = explode(' ', $args);
    if (isset($args[0]) && !empty($args[0])) {
        $watermark = $args[0];
        $watermark = trim($watermark);
    }
    if (isset($args[1]) && !empty($args[1])) {
        $gravity = $args[1];
        $gravity = trim($gravity);
    }
    if (isset($args[2]) && !empty($args[2])) {
        $size = $args[2];
        if (trim($size) == 'fixe') {
            $size = 'fixe';
        }
    }
    if (isset($args[3]) && !empty($args[3])) {
        $dissolve = $args[3];
    }

    $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $file->filename;

    // Get some values from the current file.
    $command = 'identify -format "%Q" %filepath%';
    $command = str_replace('%filepath%', escapeshellarg($filePath), $command);
    exec($command, $output, $error);
    if ($error) {
        return $error;
    }
    $quality = $output[0];
    unset($output);

    if ($size == 'fixe') {
        $command = 'composite'
            . ' -dissolve ' . escapeshellarg($dissolve . '%')
            . ' -gravity ' . escapeshellarg($gravity)
            . ' -quality ' . escapeshellarg($quality)
            . ' ' . escapeshellarg($watermark)
            . ' %filepath%'
            . ' %filepath%';
    }
    else {
        $command = 'identify -format "%[width]" %filepath%';
        $command = str_replace('%filepath%', escapeshellarg($filePath), $command);
        exec($command, $output, $error);
        if ($error) {
            return $error;
        }
        $width = $output[0];
        unset($output);

        $width_watermark = $width * $size / 100;
        $command = 'composite'
            . ' -dissolve ' . escapeshellarg($dissolve . '%')
            . ' -gravity ' . escapeshellarg($gravity)
            . ' -quality ' . escapeshellarg($quality)
            . ' \( ' . escapeshellarg($watermark) . ' -resize ' . escapeshellarg($width_watermark) . ' \)'
            . ' %filepath%'
            . ' %filepath%';
    }
    $command = str_replace('%filepath%', escapeshellarg($filePath), $command);
    exec($command, $output, $error);

    // Return error code if any.
    return $error;
}
