<div class="repeater">
    <div class="input" style="margin-bottom: 10px;">
        <div class="input">
	        <?php
	        // Ensure that $current_field array is initialized for PHP 8+.
	        if( false === $current_field)
		        $current_field = ['is_variable' => '', 'foreach' => '']; ?>
            <input type="radio" id="is_variable_<?= str_replace(array('[',']'), '', $field_name);?>_<?= $field['id'];?>_no" class="switcher variable_repeater_mode" name="fields<?= $field_name; ?>[<?= $field['id'];?>][is_variable]" value="no" <?= 'yes' != $current_field['is_variable'] ? 'checked="checked"': '' ?>/>
            <label for="is_variable_<?= str_replace(array('[',']'), '', $field_name);?>_<?= $field['id'];?>_no" class="chooser_label"><?php _e('Fixed Repeater Mode', 'mbai' )?></label>
        </div>
        <div class="input">
            <input type="radio" id="is_variable_<?= str_replace(array('[',']'), '', $field_name);?>_<?= $field['id'];?>_yes" class="switcher variable_repeater_mode" name="fields<?= $field_name; ?>[<?= $field['id'];?>][is_variable]" value="yes" <?= 'yes' == $current_field['is_variable'] ? 'checked="checked"': '' ?>/>
            <label for="is_variable_<?= str_replace(array('[',']'), '', $field_name);?>_<?= $field['id'];?>_yes" class="chooser_label"><?php _e('Variable Repeater Mode (XML)', 'mbai' )?></label>
        </div>
        <div class="input">
            <input type="radio" id="is_variable_<?= str_replace(array('[',']'), '', $field_name);?>_<?= $field['id'];?>_yes_csv" class="switcher variable_repeater_mode" name="fields<?= $field_name; ?>[<?= $field['id'];?>][is_variable]" value="csv" <?= 'csv' == $current_field['is_variable'] ? 'checked="checked"': '' ?>/>
            <label for="is_variable_<?= str_replace(array('[',']'), '', $field_name);?>_<?= $field['id'];?>_yes_csv" class="chooser_label"><?php _e('Variable Repeater Mode (CSV)', 'mbai' )?></label>
        </div>
        <div class="input sub_input">
            <input type="hidden" name="fields<?= $field_name; ?>[<?= $field['id'];?>][is_ignore_empties]" value="0"/>
            <input type="checkbox" value="1" id="is_ignore_empties<?= str_replace(array('[',']'), '', $field_name);?>_<?= $field['id'];?>" name="fields<?= $field_name; ?>[<?= $field['id'];?>][is_ignore_empties]" <?php if ( ! empty($current_field['is_ignore_empties'])) echo 'checked="checked';?>/>
            <label for="is_ignore_empties<?= str_replace(array('[',']'), '', $field_name);?>_<?= $field['id'];?>"><?php _e('Ignore blank fields', 'mbai'); ?></label>
            <a href="#help" class="wpallimport-help" style="top:0;" title="<?php _e('If the value of the element or column in your file is blank, it will be ignored. Use this option when some records in your file have a different number of repeating elements than others.', 'mbai') ?>">?</a>
        </div>
        <div class="wpallimport-clear"></div>
        <div class="switcher-target-is_variable_<?= str_replace(array('[',']'), '', $field_name);?>_<?= $field['id'];?>_yes">
            <div class="input sub_input">
                <div class="input">
                    <p>
                        <?php printf(__("For each %s do ..."), '<input type="text" name="fields' . $field_name . '[' . $field["key"] . '][foreach]" value="'. esc_html($current_field["foreach"]) .'" class="pmai_foreach widefat rad4"/>'); ?>
                        <a href="http://www.wpallimport.com/documentation/advanced-custom-fields/repeater-fields/" target="_blank"><?php _e('(documentation)', 'mbai'); ?></a>
                    </p>
                </div>
            </div>
        </div>
        <div class="switcher-target-is_variable_<?= str_replace(array('[',']'), '', $field_name);?>_<?= $field['id'];?>_yes_csv">
            <div class="input sub_input">
                <div class="input">
                    <p>
                        <?php printf(__("Separator Character %s"), '<input type="text" name="fields' . $field_name . '[' . $field["key"] . '][separator]" value="'. ( (empty($current_field["separator"])) ? '|' : $current_field["separator"] ) .'" class="pmai_variable_separator widefat rad4"/>'); ?>
                        <a href="#help" class="wpallimport-help" style="top:0;" title="<?php _e('Use this option when importing a CSV file with a column or columns that contains the repeating data, separated by separators. For example, if you had a repeater with two fields - image URL and caption, and your CSV file had two columns, image URL and caption, with values like \'url1,url2,url3\' and \'caption1,caption2,caption3\', use this option and specify a comma as the separator.', 'mbai') ?>">?</a>
                    </p>
                </div>
            </div>
        </div>
    </div>