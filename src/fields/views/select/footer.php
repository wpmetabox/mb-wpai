</div>

<div class="clear"></div>

<div class="input">
    <div class="main_choise">
        <input type="radio" id="is_multiple_field_value_<?= str_replace(array('[',']'), '', $field_name);?>_<?= $field['id'];?>_no" class="switcher" name="is_multiple_field_value<?= $field_name; ?>[<?= $field['id'];?>]" value="no" <?= 'no' == $current_is_multiple_field_value ? 'checked="checked"': '' ?>/>
        <label for="is_multiple_field_value_<?= str_replace(array('[',']'), '', $field_name);?>_<?= $field['id'];?>_no" class="chooser_label"><?php _e('Set with XPath', 'mbai' )?></label>
    </div>
    <div class="wpallimport-clear"></div>
    <div class="switcher-target-is_multiple_field_value_<?= str_replace(array('[',']'), '', $field_name);?>_<?= $field['id'];?>_no">
        <div class="input sub_input">
            <div class="input">
                <input type="text" class="smaller-text widefat rad4" name="fields<?= $field_name; ?>[<?= $field['id'];?>]" style="width:300px;" value="<?= esc_attr($current_field); ?>"/>
                <a href="#help" class="wpallimport-help" style="top:0;" title="<?php _e('Specify the value. For multiple values, separate with commas. If the choices are of the format option : Option, option-2 : Option 2, use option and option-2 for values.', 'mbai') ?>">?</a>
            </div>
        </div>
    </div>
</div>