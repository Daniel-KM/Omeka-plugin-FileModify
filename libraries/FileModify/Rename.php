<?php

/**
 * @file
 * This file should be adapted to your needs in order to rename uploaded
 * files before import in the archive folder and the database of Omeka.
 * This function requires Archive Repertory plugin.
 *
 * @note This file must be adapted to your needs.
 *
 * @warning Code can manage or not files uploaded via "add content" of via
 * "CsvImport".
 *
 * @todo Create specific exception class.
 */

/**
 * Get new name of a file. Use of this function requires Archive Repertory.
 *
 * @note This function must be adapted to your needs.
 *
 * @example This example does a simple lowercase of the extension.
 *
 * @return string
 *   Return the new name of the file, that will be updated if not empty.
 *
 */
function file_modify_rename($file)
{
    // Use regex if naming convention is more complex.

    // Only rename images.
    // if (strstr($file->mime_type, '/', TRUE) != 'image') {
    //     return '';
    // }

    // Get filename and the two last folders.
    $parts = pathinfo($file->original_filename);
    $dirname = $parts['dirname'];
    $basename = $parts['basename'];
    $extension = $parts['extension'];
    $filename = $parts['filename'];

    // Files uploaded via user interface (admin/items/add).
    if ($dirname == '.') {
        $newFilename = $filename . '.' . strtolower($extension);
    }
    // Normal files.
    else {
        $newFilename = $dirname . DIRECTORY_SEPARATOR . $filename . '.' . strtolower($extension);
    }

    return $newFilename;
}
