<input
    type="text"
    placeholder=""
    value="<?= (!is_array($current_field)) ? esc_attr( $current_field ) : esc_attr( $current_field['url'] );?>"
    name="fields<?= $field_name;?>[<?= $field['id'];?>][url]"
    class="text w95 widefat rad4"/>

<a
    href="#help"
    class="wpallimport-help"
    title="<?php _e('Specify the URL to the image or file.', 'mbai'); ?>"
    style="top:0;">?</a>

<div class="input">
    <input
        type="hidden"
        name="fields<?= $field_name;?>[<?= $field['id'];?>][search_in_media]"
        value="0"/>
    <input
        type="checkbox"
        id="<?= $field_name . $field['id'] . '_search_in_media';?>"
        name="fields<?= $field_name;?>[<?= $field['id'];?>][search_in_media]"
        value="1" <?= (!empty($current_field['search_in_media'])) ? 'checked="checked"' : '';?>/>
    <label
        for="<?= $field_name . $field['id'] . '_search_in_media';?>">
        <?php _e('Search through the Media Library for existing images before importing new images', 'mbai'); ?></label>
    <a
        href="#help"
        class="wpallimport-help"
        title="<?php _e('If an image with the same file name is found in the Media Library then that image will be attached to this record instead of importing a new image. Disable this setting if your import has different images with the same file name.', 'mbai') ?>"
        style="position: relative; top: -2px;">?</a>
</div>

<div class="input">
    <input
        type="hidden"
        name="fields<?= $field_name;?>[<?= $field['id'];?>][search_in_files]"
        value="0"/>
    <input
        type="checkbox"
        id="<?= $field_name . $field['id'] . '_search_in_files';?>"
        name="fields<?= $field_name;?>[<?= $field['id'];?>][search_in_files]"
        value="1" <?= (!empty($current_field['search_in_files'])) ? 'checked="checked"' : '';?>/>
    <label
        for="<?= $field_name . $field['id'] . '_search_in_files';?>">
        <?php _e('Use images currently uploaded in wp-content/uploads/wpallimport/files/', 'mbai'); ?></label>
</div>

