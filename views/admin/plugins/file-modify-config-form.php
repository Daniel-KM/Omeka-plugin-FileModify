<p>
    <?php echo __('This plugin allows to modify (convert, compress, watermark, rename or any other command) each uploaded filce before saving it in archive folder and before creating metadata in Omeka database.'); ?>
</p>
<fieldset id="fieldset-file-modify-backup"><legend><?php echo __('File Backup'); ?></legend>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('file_modify_backup_path',
                __('Backup original file before modifying')); ?>
        </div>
        <div class='inputs five columns omega'>
            <p class="explanation">
                <?php echo __('Set a path where to save the original file before processing it.'); ?>
                <?php echo __('All files will be saved, even not processable ones.'); ?>
                <?php echo __('If empty, no backup will be done.'); ?>
            </p>
            <?php echo $this->formText('file_modify_backup_path', get_option('file_modify_backup_path'), null); ?>
        </div>
    </div>
</fieldset>
<fieldset id="fieldset-file-modify-preprocess"><legend><?php echo __('File Modify Preprocess'); ?></legend>
    <p class="explanation">
        <?php echo __('Two process can be used to transform files, a basic one and an advanced one.'); ?>
    </p>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('file_modify_skip_filesize',
                __('Max size to process')); ?>
        </div>
        <div class='inputs five columns omega'>
            <p class="explanation">
                <?php echo __('The modification of a file may fail when it is too big.'); ?>
                <?php echo __('This field allows to define a limit in bytes above which the modification will be skipped, so the file will be uploaded unchanged.'); ?>
                <?php echo __('A warning will be added in "errors.log".'); ?>
                <?php echo __("Let empty if you don't want to use this feature."); ?>
            </p>
            <?php echo $this->formText('file_modify_skip_filesize', get_option('file_modify_skip_filesize'), null); ?>
        </div>
    </div>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('file_modify_convert_append',
                __('Basic command for ImageMagick "convert"')); ?>
        </div>
        <div class='inputs five columns omega'>
            <p class="explanation">
                <?php echo __('This field allow to append directly a basic ImageMagick command, without use of a specific library.'); ?>
                <?php echo __('See examples in ReadMe.'); ?>
                <?php echo __("Let empty if you don't want to use this feature."); ?>
            </p>
            <?php echo $this->formText('file_modify_convert_append', get_option('file_modify_convert_append'), null); ?>
        </div>
    </div>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('file_modify_preprocess',
                __('Enable advanced pre-process')); ?>
        </div>
        <div class='inputs five columns omega'>
            <p class="explanation">
                <?php echo __('If checked, Omeka will pre-process files with a specfic library before the Omeka internal process.'); ?>
                <?php echo __('To use is this feature, the file "libraries/FileModify/Preprocess.php" should be adapted to your specific needs.'); ?>
            </p>
            <?php echo $this->formCheckbox('file_modify_preprocess', true, array('checked' => (boolean) get_option('file_modify_preprocess'))); ?>
        </div>
    </div>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('file_modify_preprocess_parameters',
                __('Parameters for advanced pre-processing')); ?>
        </div>
        <div class='inputs five columns omega'>
            <p class="explanation">
                <?php echo __('These parameters will be passed to the preprocess script.'); ?>
                <?php echo __('Multiple values can be set according to the specific library.'); ?>
            </p>
            <?php echo $this->formText('file_modify_preprocess_parameters', get_option('file_modify_preprocess_parameters'), null); ?>
        </div>
    </div>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('file_modify_rename',
                __('Rename files')); ?>
        </div>
        <div class='inputs five columns omega'>
            <p class="explanation">
                <?php
                    echo __('If checked, Omeka will rename files with "libraries/FileModify/Rename.php" before the Omeka internal process.');
                    echo ' ' . __('You should take care with non-ascii filenames if your server is not fully UTF-8 compliant.');
                    if (!plugin_is_active('ArchiveRepertory')) {
                        echo ' ' . __('Renaming requires Archive Repertory plugin.');
                    }
                ?>
            </p>
            <?php echo $this->formCheckbox('file_modify_rename', true, array(
                'checked' => (boolean) get_option('file_modify_rename'),
                'disable' => !plugin_is_active('ArchiveRepertory'))); ?>
        </div>
    </div>
</fieldset>
