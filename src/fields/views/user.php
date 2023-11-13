<input
    type="text"
    placeholder=""
    value="<?= esc_attr( $current_field );?>"
    name="fields<?= $field_name;?>[<?= $field['key'];?>]"
    class="text w95 widefat rad4"/>

<a href="#help"
   class="wpallimport-help"
   title="<?php _e('Specify the user ID, username, or user e-mail address. Separate multiple values with commas.', 'mbai'); ?>"
   style="top:0;">?</a>