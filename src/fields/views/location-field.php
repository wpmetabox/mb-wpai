<div class="input">
    <label><?php _e("Address"); ?></label>
    <input
        type="text"
        placeholder=""
        value="<?= esc_attr( $current_field['address'] );?>"
        name="fields<?= $field_name; ?>[<?= $field['id'];?>][address]"
        class="text widefat rad4"/>
</div>
<div class="input">
    <label><?php _e("Lat"); ?></label>
    <input
        type="text"
        placeholder=""
        value="<?= esc_attr( $current_field['lat'] );?>"
        name="fields<?= $field_name; ?>[<?= $field['id'];?>][lat]"
        class="text widefat rad4"/>
</div>
<div class="input">
    <label><?php _e("Lng"); ?></label>
    <input
        type="text"
        placeholder=""
        value="<?= esc_attr( $current_field['lng'] );?>"
        name="fields<?= $field_name; ?>[<?= $field['id'];?>][lng]"
        class="text widefat rad4"/>
</div>