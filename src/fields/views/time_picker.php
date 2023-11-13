<input
    type="text"
    placeholder=""
    value="<?= esc_attr( $current_field );?>"
    name="fields<?= $field_name; ?>[<?= $field['key'];?>]"
    class="text widefat rad4"
    style="width:200px;"/>

<a
    href="#help"
    class="wpallimport-help"
    title="<?php _e('Use H:i:s format.', 'mbai'); ?>"
    style="top:0;">?</a>