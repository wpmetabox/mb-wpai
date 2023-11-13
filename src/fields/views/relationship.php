<div class="input">

    <input
        type="text"
        placeholder=""
        value="<?= ( ! is_array($current_field)) ? esc_attr($current_field) : esc_attr( $current_field['value'] );?>"
        name="fields<?= $field_name;?>[<?= $field['id'];?>][value]"
        class="text widefat rad4"
        style="width: 75%;"/>

    <input
        type="text"
        style="width:5%; text-align:center;"
        value="<?= (!empty($current_field['delim'])) ? esc_attr( $current_field['delim'] ) : ',';?>"
        name="fields<?= $field_name;?>[<?= $field['id'];?>][delim]"
        class="small rad4">

    <a
        href="#help"
        class="wpallimport-help"
        title="<?php _e('Enter the ID, slug, or Title. Separate multiple entries with separator character.', 'mbai'); ?>"
        style="top:0;">?</a>
</div>