<div class="switcher-target-is_multiple_field_value_<?= str_replace(array('[',']'), '', $field_name);?>_<?= $field['id'];?>_yes">
    <div class="input sub_input">
        <div class="input">
            <?php
                $field_class = 'acf_field_' . $field['type'];
                $field['other_choice'] = false;
                $tmp_key = $field['id'];
                $field['id'] = 'multiple_value'. $field_name .'[' . $field['id'] . ']';
                $field['value'] = $current_multiple_value;
                acf_render_field( $field );
                $field['id'] = $tmp_key;
            ?>
        </div>
    </div>
</div>
