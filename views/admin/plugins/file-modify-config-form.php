<p>
    <?php echo __('This plugin allows to modify (convert, compress, watermark, rename or any other command) each uploaded file before saving it in archive folder and before creating metadata in Omeka database.'); ?>
</p>
<fieldset id="fieldset-file-modify-backup"><legend><?php echo __('File Backup'); ?></legend>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $view->formLabel('file_modify_backup_path',
                __('Backup original file before modifying')); ?>
        </div>
        <div class='inputs five columns omega'>
            <?php echo $view->formText('file_modify_backup_path', get_option('file_modify_backup_path'), null); ?>
            <p class="explanation">
                <?php echo __('Set a path where to save the original file before processing it.'); ?>
                <?php echo __('All files will be saved, even not processable ones.'); ?>
                <?php echo __('If empty, no backup will be done.'); ?>
            </p>
        </div>
    </div>
</fieldset>
<fieldset id="fieldset-file-modify-simple"><legend><?php echo __('File Modify Simple'); ?></legend>
    <p class="explanation">
        <?php echo __('These fields allow to set basic ImageMagick commands.'); ?>
    </p>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $view->formLabel('file_modify_convert_resolution',
                __('Resolution of images in dot per inch [-resample]')); ?>
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
            <?php echo $view->formLabel('file_modify_convert_quality',
                __('Percentage of compression of images [-quality]')); ?>
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
            <?php echo $view->formLabel('file_modify_convert_resize',
                __('Percentage of image resizing [-resize]')); ?>
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
            <?php echo $view->formLabel('file_modify_convert_append',
                __('Command to append to ImageMagick "convert"')); ?>
        </div>
        <div class='inputs five columns omega'>
            <?php echo $view->formText('file_modify_convert_append', get_option('file_modify_convert_append'), null); ?>
            <p class="explanation">
                <?php echo __("Add a watermark or anything else. Let empty if you don't want to add another parameter."); ?>
            </p>
        </div>
    </div>
</fieldset>
<fieldset id="fieldset-file-modify-advanced"><legend><?php echo __('File Modify Advanced'); ?></legend>
    <p class="explanation">
        <?php echo __('To use advanced parameters, you should adapt the files "libraries/file_modify_preprocess.php" and/or "libraries/file_modify_rename.php" to your specific needs.'); ?>
    </p>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $view->formLabel('file_modify_preprocess',
                __('Pre-process files')); ?>
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
            <?php echo $view->formLabel('file_modify_preprocess_parameters',
                __('Parameters for pre-processing')); ?>
        </div>
        <div class='inputs five columns omega'>
            <?php echo $view->formText('file_modify_preprocess_parameters', get_option('file_modify_preprocess_parameters'), null); ?>
            <p class="explanation">
                <?php echo __('These parameters will be passed to the "libraries/file_modify_preprocess.php" script.') .' '; ?>
                <?php echo __('Multiple values can be separated with a space (default script).'); ?>
            </p>
        </div>
    </div>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $view->formLabel('file_modify_rename',
                __('Rename files')); ?>
        </div>
        <div class='inputs five columns omega'>
            <?php echo $view->formCheckbox('file_modify_rename', TRUE, array('checked' => (boolean) get_option('file_modify_rename'))); ?>
            <p class="explanation">
                <?php
                    echo __('If checked, Omeka will rename files with "libraries/file_modify_rename.php" before the Omeka internal process.');
                    echo ' ' . __('Renaming requires Archive Repertory plugin.');
                    echo ' ' . __('You should take care with non-ascii filenames if your server is not fully UTF-8 compliant.');
                ?>
            </p>
        </div>
    </div>
</fieldset>