<div class="input">
    <label><?php _e("Title"); ?></label>
    <input
        type="text"
        placeholder=""
        value="<?= esc_attr( $current_field['title'] );?>"
        name="fields<?= $field_name; ?>[<?= $field['id'];?>][title]"
        class="text widefat rad4"/>
</div>
<div class="input">
    <label><?php _e("URL"); ?></label>
    <input
        type="text"
        placeholder=""
        value="<?= esc_attr( $current_field['url'] );?>"
        name="fields<?= $field_name; ?>[<?= $field['id'];?>][url]"
        class="text widefat rad4"/>

    <a
        href="#help"
        class="wpallimport-help"
        title="<?php _e('Use external URL or post ID, slug or title to link to that post.', 'mbai'); ?>"
        style="top:0;">?</a>

</div>
<div class="input">
    <label><?php _e("Target"); ?></label>
    <input
        type="text"
        placeholder=""
        value="<?= esc_attr( $current_field['target'] );?>"
        name="fields<?= $field_name; ?>[<?= $field['id'];?>][target]"
        class="text widefat rad4"/>
</div>