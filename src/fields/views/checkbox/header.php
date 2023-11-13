<div class="input">
    <div class="main_choise">
        <input type="radio" id="is_multiple_field_value_<?= str_replace(array('[',']'), '', $field_name);?>_<?= $field['id'];?>_yes" class="switcher" name="is_multiple_field_value<?= $field_name; ?>[<?= $field['id'];?>]" value="yes" <?= 'no' != $current_is_multiple_field_value ? 'checked="checked"': '' ?>/>
        <label for="is_multiple_field_value_<?= str_replace(array('[',']'), '', $field_name);?>_<?= $field['id'];?>_yes" class="chooser_label"><?php _e("Select value for all records", 'mbai'); ?></label>
    </div>
    <div class="wpallimport-clear"></div>