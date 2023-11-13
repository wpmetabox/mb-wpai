<input
    type="text"
    placeholder=""
    value="<?= esc_attr( $current_field );?>"
    name="fields<?= $field_name; ?>[<?= $field['id'];?>]"
    class="text datetimepicker widefat rad4"
    style="width:200px;"/>

<a
    href="#help"
    class="wpallimport-help"
    title="<?php _e('Use any format supported by the PHP strtotime function.', 'mbai'); ?>"
    style="top:0;">?</a>