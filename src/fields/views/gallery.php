<div class="input">
    <label><?php _e('Enter image URL one per line, or separate them with a', 'mbai'); ?> </label>
    <input
        type="text"
        style="width:5%; text-align:center;"
        value="<?= (!empty($current_field['delim'])) ? esc_attr( $current_field['delim'] ) : '';?>"
        name="fields<?= $field_name;?>[<?= $field['id'];?>][delim]"
        class="small rad4">

    <textarea
        placeholder="http://example.com/images/image-1.jpg"
        style="clear: both; display: block; margin-top: 10px;" class="newline rad4"
        name="fields<?= $field_name;?>[<?= $field['id'];?>][gallery]"><?= ( ! is_array($current_field)) ? esc_attr($current_field) : esc_attr( $current_field['gallery'] );?></textarea>

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

    <div class="input">
        <input
            type="checkbox"
            id="<?= $field_name . $field['id'] . '_only_append_new';?>"
            name="fields<?= $field_name;?>[<?= $field['id'];?>][only_append_new]"
            value="1" <?= (!empty($current_field['only_append_new'])) ? 'checked="checked"' : '';?>/>

        <label
            for="<?= $field_name . $field['id'] . '_only_append_new';?>">
            <?php _e('Append only new images and do not touch existing during updating gallery field.', 'mbai'); ?></label>
    </div>
</div>