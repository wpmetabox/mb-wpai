<div class="switcher-target-is_multiple_field_value_<?= str_replace(array('[',']'), '', $field_name);?>_<?= $field['id'];?>_yes">
    <div class="input sub_input">
        <div class="input">
            <?php
                $field_class = 'acf_field_' . $field['type'];
                $new_field = new $field_class();
                $field['other_choice'] = false;
                $field['name'] = 'multiple_value'. $field_name .'[' . $field['id'] . ']';
                $field['value'] = $current_multiple_value;
                $field['prefix'] = '';
                $new_field->create_field( $field );
            ?>
        </div>
    </div>
</div>
