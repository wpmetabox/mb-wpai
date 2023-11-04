<input
    type="text"
    placeholder=""
    value="<?php echo ( ! is_array($current_field)) ? esc_attr($current_field) : esc_attr( $current_field['value'] );?>"
    name="fields<?php echo $field_name;?>[<?php echo $field['key'];?>][value]"
    class="text widefat rad4"
    style="width: 75%;"/>

<input
    type="text"
    style="text-align:center;"
    value="<?php echo (!empty($current_field['delim'])) ? esc_attr( $current_field['delim'] ) : ',';?>"
    name="fields<?php echo $field_name;?>[<?php echo $field['key'];?>][delim]"
    class="small rad4">

<a
    href="#help"
    class="wpallimport-help"
    title="<?php _e('Enter the ID, slug, or Title. Separate multiple entries with commas.', 'mbai'); ?>"
    style="top:0;">?</a>