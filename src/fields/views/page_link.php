<input
    type="text"
    placeholder=""
    value="<?= esc_attr( $current_field );?>"
    name="fields<?= $field_name;?>[<?= $field['id'];?>]"
    class="text w95 widefat rad4"/>

<a
    href="#help"
    class="wpallimport-help"
    title="<?php _e('Enter the ID, slug, or Title. Separate multiple entries with commas.', 'mbai'); ?>"
    style="top:0;">?</a>