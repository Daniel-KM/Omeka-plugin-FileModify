<?php
    echo '<p>' . __('This plugin allows to modify (convert, compress, watermark, rename or any other command) uploaded file before saving it in archive folder and before creating metadata in Omeka database.');
    echo '</p>';
    echo '<p>';
    echo __('Simply adapt the files libraries/file_modify_command.php and libraries/file_modify_rename.php to your specific needs.') . ' ';
    echo __('See readme.md to allow use of the command functionality.') . ' ';
    if (get_plugin_ini('FileModify', 'file_modify_allow_command') == 'TRUE') {
        echo __('Currently, use of this option is allowed.');
    }
    else {
        echo __('Currently, this option is hidden because use of it is not allowed.');
    }
    echo '</p>';
    echo '<p>';
    echo __('Renaming requires Archive Repertory plugin. Files imported via the user interface (add content) are not renamed. ');
    echo '</p>';
?>
<div class="field">
    <label for="file_modify_convert_resolution">
        <?php echo __('Resolution of images in dot per inch [-resample]');?>
    </label>
    <div class="inputs">
        <?php echo __v()->formText('file_modify_convert_resolution', get_option('file_modify_convert_resolution'), null);?>
        <p class="explanation">
            <?php echo __("Examples: '200x200' or '96'. Let empty if you don't want to change the resolution.") . '<br />';?>
        </p>
    </div>
</div>
<div class="field">
    <label for="file_modify_convert_quality">
        <?php echo __('Percentage of compression of images [-quality]');?>
    </label>
    <div class="inputs">
        <?php echo __v()->formText('file_modify_convert_quality', get_option('file_modify_convert_quality'), null);?>
        <p class="explanation">
            <?php echo __("Let empty if you don't want to change the compression level.") . '<br />';?>
        </p>
    </div>
</div>
<div class="field">
    <label for="file_modify_convert_resize">
        <?php echo __('Percentage of image resizing [-resize]');?>
    </label>
    <div class="inputs">
        <?php echo __v()->formText('file_modify_convert_resize', get_option('file_modify_convert_resize'), null);?>
        <p class="explanation">
            <?php echo __("Let empty if you don't want to change the size of images.") . '<br />';?>
        </p>
    </div>
</div>
<div class="field">
    <label for="file_modify_convert_append">
        <?php echo __('Command to append to ImageMagick "convert"');?>
    </label>
    <div class="inputs">
        <?php echo __v()->formText('file_modify_convert_append', get_option('file_modify_convert_append'), null);?>
        <p class="explanation">
            <?php echo __("Add a watermark or anything else. Let empty if you don't want to add another parameter.") . '<br />';?>
        </p>
    </div>
</div>
<?php if (get_plugin_ini('FileModify', 'file_modify_allow_command') == 'TRUE'): ?>
<div class="field">
    <label for="file_modify_command">
        <?php echo __('Command to execute before saving file');?>
    </label>
    <div class="inputs">
        <?php echo __v()->formText('file_modify_command', get_option('file_modify_command'), null);?>
        <p class="explanation">
            <?php echo __('If this option is allowed by the owner of the site, this command will be executed on each uploaded file before saving. Add "%filepath%" (without quotes) as a placeholder for the file path.') . '<br />';?>
        </p>
    </div>
</div>
<?php endif; ?>
