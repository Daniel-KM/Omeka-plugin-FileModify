<?php
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

    $filePath = $file->getPath('original');

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
                        $quality = 9;
                    }
                    else {
                        $command = 'identify -format "%Q" %filepath%';
                        $command = str_replace('%filepath%', escapeshellarg($filePath), $command);
                        unset($error);
                        unset($output);
                        exec($command, $output, $error);
                        if ($error) {
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
                        $command = str_replace('%filepath%', escapeshellarg($filePath), $command);
                        unset($error);
                        unset($output);
                        exec($command, $output, $error);
                        if ($error) {
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
                    $command = str_replace('%filepath%', escapeshellarg($filePath), $command);
                    unset($error);
                    unset($output);
                    exec($command, $output, $error);
                    break;

                case 'Imagick':
                case 'GD':

            }
            break;

        case 'text':
            switch ($imageLibrary) {
                case 'ExternalImageMagick':
                    // See http://www.imagemagick.org/Usage/annotating/
                    $command = 'identify -format "%[width] %[height] %Q" %filepath%';
                    $command = str_replace('%filepath%', escapeshellarg($filePath), $command);
                    unset($error);
                    unset($output);
                    exec($command, $output, $error);
                    if ($error) {
                        return $error;
                    }
                    list($width, $height, $quality) = explode(' ', $output[0]);

                    $width_watermark = $width * $size / 100;
                    $height_watermark = $height * $size / 100;
                    switch ($height) {
                        case $height <= 256: $pointsize = 12; break;
                        case $height <= 1024: $pointsize = 20; break;
                        case $height <= 2048: $pointsize = 36; break;
                        case $height <= 3072: $pointsize = 48; break;
                        case $height <= 4000: $pointsize = 72; break;
                        case $height <= 6000: $pointsize = 100; break;
                        case $height > 6000: $pointsize = 120; break;
                    }

                    $command = 'convert'
                        . $hostLimit
                        . ' %filepath%'
                        // . ' -font "Liberation-Sans-Regular"'
                        . ' -font "Arial"'
                        . ' -pointsize ' . $pointsize
                        . ' -draw "gravity ' . escapeshellarg($gravity) . ' fill black text 0,12 ' . escapeshellarg($watermark . '  ') . ' fill yellow text 1,11 ' . escapeshellarg($watermark . '  ') . '"'
                        . ' %filepath%';

                    $command = str_replace('%filepath%', escapeshellarg($filePath), $command);
                    unset($error);
                    unset($output);
                    exec($command, $output, $error);
                    break;

                case 'Imagick':
                    break;

                case 'GD':
                    // GD uses multiple functions to load an image, so this one manages all.
                    try {
                        $image = imagecreatefromstring(file_get_contents($filePath));
                    } catch (Exception $e) {
                        _log("GD failed to open the file. Details:\n$e", Zend_Log::ERR);
                        return false;
                    }
                    if (empty($image)) {
                        return false;
                    }

                    list($width, $height, $format) = getimagesize($filePath);

                    // Calculate maximum height of a character, depending on used font.
                    // $font = PUBLIC_THEME_DIR . DIRECTORY_SEPARATOR . 'My Watermark' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'LiberationSans-Regular.ttf';
                    $font = 'arial.ttf';
                    switch ($height) {
                        case $height <= 256: $pointsize = 12; break;
                        case $height <= 1024: $pointsize = 20; break;
                        case $height <= 2048: $pointsize = 36; break;
                        case $height <= 3072: $pointsize = 48; break;
                        case $height <= 4000: $pointsize = 72; break;
                        case $height <= 6000: $pointsize = 80; break;
                        case $height > 6000: $pointsize = 100; break;
                    }
                    // $bbox = imagettfbbox($pointsizeize, 0, $font, 'ky');
                    // $x = 8;
                    // $y = 8 - $bbox[5];
                    $x = 24;
                    $y = $height - 24;

                    shadow_text($image, $pointsize, $x, $y, $font, $watermark);

                    $error = imagejpeg($image, $filePath, 85);
                    // Should return 0 if no error, not true.
                    $error = $error ? 0 : 1;
                    break;
            }
    }

    // Return error code if any.
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
