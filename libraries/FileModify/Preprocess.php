﻿<?php
/**
 * This file should be adapted to your needs in order to transform uploaded
 * files before import in the archive folder and the database of Omeka.
 *
 * @note This file must be adapted to your needs.
 *
 * @todo Create specific exception class.
 * @todo Use Imagick & GD for image watermark.
 */

function file_modify_default_parameters()
{
    // Values for watermark are set via config form and default are set below.
    return array_values(array(
        'imageLibrary' => 'ExternalImageMagick',
        // 'imageLibrary' => 'Imagick',
        // 'imageLibrary' => 'GD',
        // Image magick: limit parameters for some shared host.
        // Memory area can be set directly by arguments.
        // 'hostLimit' => ' ' . '-limit memory 50MB -limit map 100MB -limit area 25MB -limit disk 1GB -limit thread 2' . ' ',
        'hostLimit' => '',
    ));
}

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
    list($imageLibrary, $hostLimit) = file_modify_default_parameters();

    $filepath = $file->getPath('original');

    // Check the file before processing.

    // Only process images.
    if (strstr($file->mime_type, '/', TRUE) != 'image') {
        return;
    }

    // Initialize default values (no watermark).
    $watermark = '';
    $gravity = 'South';
    $size = 25;
    $dissolve = 95;
    $police = dirname(__FILE__)
        . DIRECTORY_SEPARATOR . 'Fonts'
        . DIRECTORY_SEPARATOR . 'LiberationSans-Regular.ttf';

    // Get parameters.
    $args = explode(',', $args);
    if (isset($args[0]) && trim($args[0]) != '') {
        $watermark = trim($args[0]);
    }
    if (isset($args[1]) && trim($args[1]) != '') {
        $gravity = trim($args[1]);
    }
    if (isset($args[2]) && trim($args[2]) != '') {
        $size = trim($args[2]);
    }
    if (isset($args[3]) && trim($args[3]) != '') {
        $dissolve = trim($args[3]);
    }
    if (isset($args[4]) && trim($args[4]) != '') {
        $police = trim($args[4]);
    }
    // Manage memory area for Image Magick.
    if (isset($args[5]) && trim($args[5]) != '') {
        $hostLimit .= ' -limit area ' . escapeshellarg(trim($args[5]));
    }

    // Check watermark.
    if ($watermark == '') {
        return;
    }

    $watermarkType = file_exists($watermark) ? 'file' : 'text';
    switch ($watermarkType) {
        case 'file':
            switch ($imageLibrary) {
                case 'ExternalImageMagick':
                    // Get some values from the current file.
                    // Fix a bug with libpng 1.2 and bad formatted png.
                    // @see http://www.imagemagick.org/discourse-server/viewtopic.php?f=3&t=22119
                    if ($file->mime_type == 'image/png') {
                        $quality = 109;
                    }
                    else {
                        $command = 'identify -format "%Q" %filepath%';
                        $command = str_replace('%filepath%', escapeshellarg($filepath), $command);
                        unset($error);
                        unset($output);
                        exec($command, $output, $error);
                        if ($error) {
                            _log('[FileModify]: Error: ' . $error . PHP_EOL . 'Command:' . PHP_EOL . $command, Zend_Log::ERR);
                            return $error;
                        }
                        $quality = $output[0];
                    }

                    if ($size == 'fixe') {
                        $command = 'composite'
                            . $hostLimit
                            . ' -dissolve ' . escapeshellarg($dissolve . '%')
                            . ' -gravity ' . escapeshellarg($gravity)
                            . ' -quality ' . escapeshellarg($quality)
                            . ' ' . escapeshellarg($watermark)
                            . ' %filepath%'
                            . ' %filepath%';
                    }
                    else {
                        $command = 'identify -format "%[width]" %filepath%';
                        $command = str_replace('%filepath%', escapeshellarg($filepath), $command);
                        unset($error);
                        unset($output);
                        exec($command, $output, $error);
                        if ($error) {
                            _log('[FileModify]: Error: ' . $error . PHP_EOL . 'Command:' . PHP_EOL . $command, Zend_Log::ERR);
                            return $error;
                        }
                        $width = $output[0];

                        $width_watermark = $width * $size / 100;
                        $command = 'composite'
                            . $hostLimit
                            . ' -dissolve ' . escapeshellarg($dissolve . '%')
                            . ' -gravity ' . escapeshellarg($gravity)
                            . ' -quality ' . escapeshellarg($quality)
                            . ' \( ' . escapeshellarg($watermark) . ' -resize ' . escapeshellarg($width_watermark) . ' \)'
                            . ' %filepath%'
                            . ' %filepath%';
                    }
                    $command = str_replace('%filepath%', escapeshellarg($filepath), $command);
                    unset($error);
                    unset($output);
                    exec($command, $output, $error);
                    if ($error) {
                        _log('[FileModify]: Error: ' . $error . PHP_EOL . 'Command:' . PHP_EOL . $command, Zend_Log::ERR);
                        return $error;
                    }
                    break;

                case 'Imagick':
                case 'GD':
                    _log('[FileModify]: Error: Imagick and GD are not managed.', Zend_Log::ERR);
                    return 1;
            }
            break;

        case 'text':
            switch ($imageLibrary) {
                case 'ExternalImageMagick':
                    // See http://www.imagemagick.org/Usage/annotating/
                    $command = 'identify -format "%[width] %[height] %Q" %filepath%';
                    $command = str_replace('%filepath%', escapeshellarg($filepath), $command);
                    unset($error);
                    unset($output);
                    exec($command, $output, $error);
                    if ($error) {
                        _log('[FileModify]: Error: ' . $error . PHP_EOL . 'Command:' . PHP_EOL . $command, Zend_Log::ERR);
                        return $error;
                    }
                    list($width, $height, $quality) = explode(' ', $output[0]);

                    $width_watermark = $width * $size / 100;
                    $height_watermark = $height * $size / 100;
                    switch ($height) {
                        case $height <= 256: $pointsize = 12; break;
                        case $height <= 512: $pointsize = 14; break;
                        case $height <= 1024: $pointsize = 16; break;
                        case $height <= 1536: $pointsize = 24; break;
                        case $height <= 2048: $pointsize = 36; break;
                        case $height <= 3072: $pointsize = 48; break;
                        case $height <= 4000: $pointsize = 72; break;
                        case $height <= 6000: $pointsize = 100; break;
                        case $height > 6000: $pointsize = 120; break;
                    }

                    $posX = round($width * 2.5 / 100);
                    $posY = round($height * 2.5 / 100);

                    // Works too.
                    // convert w6-1.jpg \( -size 1555x2560 xc:none -gravity south-east -fill red -font "$police" -pointsize 36 -annotate 0x0+0+0 "Testing" \) -compose dissolve -define compose:args=100,100 -geometry +72+72 -composite z1.jpg
                    $command = 'convert'
                        . $hostLimit
                        . ' %filepath%'
                        . ' -font "' . $police . '"'
                        . ' -pointsize ' . $pointsize
                        . ' -draw "gravity ' . escapeshellarg($gravity)
                            // Warning: don't use escapeshellarg() and use ' and not " to enclose string!
                            . sprintf(' fill white text %d,%d %s', $posX + 1, $posY + 1, escapeshellarg($watermark))
                            . sprintf(' fill #084B7B text %d,%d %s', $posX, $posY, escapeshellarg($watermark))
                            . '"'
                        . ' %filepath%';

                    $command = str_replace('%filepath%', escapeshellarg($filepath), $command);
                    unset($error);
                    unset($output);
                    exec($command, $output, $error);
                    if ($error) {
                        _log('[FileModify]: Error: ' . $error . PHP_EOL . 'Command:' . PHP_EOL . $command, Zend_Log::ERR);
                        return $error;
                    }
                    break;

                case 'Imagick':
                    _log('[FileModify]: Error: Imagick is not managed.', Zend_Log::ERR);
                    return 1;

                case 'GD':
                    // GD uses multiple functions to load an image, so this one manages all.
                    try {
                        $image = imagecreatefromstring(file_get_contents($filepath));
                    } catch (Exception $e) {
                        _log('GD failed to open the file. Details:' . PHP_EOL . $e->getMessage(), Zend_Log::ERR);
                        return $e->getMessage();
                    }
                    if (empty($image)) {
                        _log('GD returned an empty image.', Zend_Log::ERR);
                        return 1;
                    }

                    list($width, $height, $format) = getimagesize($filepath);

                    // Calculate maximum height of a character, depending on used font.
                    switch ($height) {
                        case $height <= 256: $pointsize = 12; break;
                        case $height <= 512: $pointsize = 14; break;
                        case $height <= 1024: $pointsize = 16; break;
                        case $height <= 1536: $pointsize = 24; break;
                        case $height <= 2048: $pointsize = 36; break;
                        case $height <= 3072: $pointsize = 48; break;
                        case $height <= 4000: $pointsize = 72; break;
                        case $height <= 6000: $pointsize = 100; break;
                        case $height > 6000: $pointsize = 120; break;
                    }
                    // $bbox = imagettfbbox($pointsizeize, 0, $police, 'ky');
                    // $x = 8;
                    // $y = 8 - $bbox[5];
                    $x = 24;
                    $y = $height - 24;

                    shadow_text($image, $pointsize, $x, $y, $police, $watermark);

                    $error = imagejpeg($image, $filepath, 85);
                    // Should return 0 if no error, not true.
                    $error = $error ? 0 : 1;
                    break;
            }
    }

    // Return error code if any.
    if ($error) {
        _log('[FileModify]: Unknown Error.', Zend_Log::ERR);
    }
    return $error;
}

function shadow_text($image, $size, $x, $y, $font, $text)
{
    $black = imagecolorallocate($image, 0, 0, 0);
    // $white = imagecolorallocate($image, 255, 255, 255);
    // Yellow
    $white = imagecolorallocate($image, 255, 255, 0);
    $error = imagettftext($image, $size, 0, $x + 1, $y + 1, $black, $font, $text);
    $error = imagettftext($image, $size, 0, $x + 0, $y + 1, $black, $font, $text);
    $error = imagettftext($image, $size, 0, $x + 0, $y + 0, $white, $font, $text);
}
