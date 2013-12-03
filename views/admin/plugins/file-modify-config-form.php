<?php $view = get_view(); ?>
<?php
    echo '<p>';
    echo __('This plugin allows to modify (convert, compress, watermark, rename or any other command) each uploaded file before saving it in archive folder and before creating metadata in Omeka database.');
    echo '</p>';
    echo '<p>';
    echo __('You can use the first fields for basic ImageMagick commands, or enable "Preprocess" or "Rename" for advanced needs.') . ' ';
    echo __('In that case, you should adapt the files "libraries/file_modify_preprocess.php" and/or "libraries/file_modify_rename.php" to your specific needs.');
    echo '</p>';
    echo '<p>';
    echo __('You should take care with non-ascii filenames if your server is not fully UTF-8 compliant.');
    echo '</p>';
?>
<div class="field">
    <div class="two columns alpha">
        <label for="file_modify_convert_resolution">
            <?php echo __('Resolution of images in dot per inch [-resample]'); ?>
        </label>
    </div>
    <div class='inputs five columns omega'>
        <?php echo $view->formText('file_modify_convert_resolution', get_option('file_modify_convert_resolution'), null); ?>
        <p class="explanation">
            <?php echo __("Examples: '200x200' or '96'. Let empty if you don't want to change the resolution."); ?>
            <br />
            <?php echo __("Warning: This option only change the declared resolution and don't resample the image. It can be a issue if you got OCR."); ?>
        </p>
    </div>
</div>
<div class="field">
    <div class="two columns alpha">
        <label for="file_modify_convert_quality">
            <?php echo __('Percentage of compression of images [-quality]'); ?>
        </label>
    </div>
    <div class='inputs five columns omega'>
        <?php echo $view->formText('file_modify_convert_quality', get_option('file_modify_convert_quality'), null); ?>
        <p class="explanation">
            <?php echo __("Let empty if you don't want to change the compression level."); ?>
        </p>
    </div>
</div>
<div class="field">
    <div class="two columns alpha">
        <label for="file_modify_convert_resize">
            <?php echo __('Percentage of image resizing [-resize]'); ?>
        </label>
    </div>
    <div class='inputs five columns omega'>
        <?php echo $view->formText('file_modify_convert_resize', get_option('file_modify_convert_resize'), null); ?>
        <p class="explanation">
            <?php echo __("Let empty if you don't want to change the size of images."); ?>
        </p>
    </div>
</div>
<div class="field">
    <div class="two columns alpha">
        <label for="file_modify_convert_append">
            <?php echo __('Command to append to ImageMagick "convert"'); ?>
        </label>
    </div>
    <div class='inputs five columns omega'>
        <?php echo $view->formText('file_modify_convert_append', get_option('file_modify_convert_append'), null); ?>
        <p class="explanation">
            <?php echo __("Add a watermark or anything else. Let empty if you don't want to add another parameter."); ?>
        </p>
    </div>
</div>
<div class="field">
    <div class="two columns alpha">
        <label for="file_modify_preprocess">
            <?php echo __('Pre-process files'); ?>
        </label>
    </div>
    <div class='inputs five columns omega'>
        <?php echo $view->formCheckbox('file_modify_preprocess', TRUE, array('checked' => (boolean) get_option('file_modify_preprocess'))); ?>
        <p class="explanation">
            <?php echo __('If checked, Omeka will pre-process files with "libraries/file_modify_preprocess.php" before the Omeka internal process.'); ?>
        </p>
    </div>
</div>
<div class="field">
    <div class="two columns alpha">
        <label for="file_modify_preprocess_parameters">
            <?php echo __('Parameters for pre-processing'); ?>
        </label>
    </div>
    <div class='inputs five columns omega'>
        <?php echo $view->formText('file_modify_preprocess_parameters', get_option('file_modify_preprocess_parameters'), null); ?>
        <p class="explanation">
            <?php echo __('These parameters will be passed to the "libraries/file_modify_preprocess.php" script.')  .' '; ?>
            <?php echo __('Multiple values can be separated with a ";".'); ?>
        </p>
    </div>
</div>
<div class="field">
    <div class="two columns alpha">
        <label for="file_modify_rename">
            <?php echo __('Rename files'); ?>
        </label>
    </div>
    <div class='inputs five columns omega'>
        <?php echo $view->formCheckbox('file_modify_rename', TRUE, array('checked' => (boolean) get_option('file_modify_rename'))); ?>
        <p class="explanation">
            <?php
                echo __('If checked, Omeka will rename files with "libraries/file_modify_rename.php" before the Omeka internal process.') . ' ';
                echo __('Renaming requires Archive Repertory plugin.');
            ?>
        </p>
    </div>
</div>
