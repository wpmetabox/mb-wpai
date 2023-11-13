<input
    type="text"
    placeholder=""
    value="<?= esc_attr( $current_field );?>"
    name="fields<?= $field_name;?>[<?= $field['id'];?>]"
    class="text w95 widefat rad4"/>

<a
    href="#help"
    class="wpallimport-help"
    title="<?php _e('Specify the hex code the color preceded with a # - e.g. #ea5f1a.', 'mbai'); ?>"
    style="top:0;">?</a>