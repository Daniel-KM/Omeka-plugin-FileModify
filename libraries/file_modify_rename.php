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
 * @return string
 *   Return the new name of the file, that will be updated if not empty.
 *
 */
function file_modify_rename($file)
{
    // This example is adapted to the configuration of the École des Ponts.
    //
    // Filenames varies with category and digitalization provider.
    // Each file should be named "dc:identifier_xxxx.ext".
    // No change:
    // - Journal de mission / ENPC01_Ms_0375 / ENPC01_Ms_0375_0001.jpg
    // - Phare / ENPC01_PH_230 / ENPC01_PH_230_P06.jpg
    // Change 1:
    // - Cours / ENPC02_COU_4_19539_1893 / JENPC02_COU_4_19539_1893 / J0000001.jpg
    //   => Cours / ENPC02_COU_4_19539_1893 / ENPC02_COU_4_19539_1893_0001.jpg
    // - Cours / ENPC02_COU_4_29840_1938 / J0000001
    //   => Cours / ENPC02_COU_4_29840_1938 / ENPC02_COU_4_29840_1938_0001.jpg
    // Change 2 (manages an error of the provider of the scanned images):
    // - Phare / ENPC01_PH_663 / ENPC01_PH_663_G001_1873.jpg
    //   => ENPC01_PH_663_1873_G001.jpg
    // Finally, this second change is not used, but kept for information.
    //
    // TODO Use regex or xml files if naming convention is more complex.

    // Only rename images.
    if (strstr($file->mime_type, '/', TRUE) != 'image') {
        return '';
    }

    // Get filename and the two last folders.
    $parts = pathinfo($file->original_filename);
    $dirname = $parts['dirname'];
    // $basename = $parts['basename'];
    $extension = $parts['extension'];
    $filename = $parts['filename'];

    // Don't rename files uploaded via user interface (admin/items/add).
    if (empty($dirname)) {
        return '';
    }

    // Get the name of the two last folders without path.
    $lastFolder_1 = basename($dirname);
    $lastFolder_2 = basename(dirname($dirname));

    // Default : no renaming.
    $result = '';

    // No change if the filename matchs the last folder name and a page number.
    if ((strpos($filename, $lastFolder_1 . '_') === 0)
            // In our files, the page number never contain a '_'.
            && (strpos($filename, '_', strlen($lastFolder_1) + 1) === FALSE)
        ) {
        // No renaming.
    }

    // Change 1.
    elseif (strpos($lastFolder_1, $lastFolder_2) === 1
            && ((strlen($lastFolder_1)) == strlen($lastFolder_2) + 1)
            && (strpos($filename, '_') === FALSE)
        ) {
        $result = $lastFolder_2 . '_' . substr($filename, -4) . '.' . $extension;
    }

    // Change 2.
    elseif ((strpos($filename, $lastFolder_1 . '_') === 0)
            // An element is added at the end of the filename, so only one '_'.
            && (substr_count($filename, '_', strlen($lastFolder_1) + 1) == 1)
            // Last element (1873) is the year and the previous one is the page.
            && (list($page, $year) = explode('_', substr($filename, strlen($lastFolder_1) + 1)))
            && !empty($page)
            && !empty($year)
        ) {
        $result = $lastFolder_1 . '_' . $year . '_' . $page . '.' . $extension;
    }

    return $result;
}
