<div class="input">
    <label><?php _e("Item Name"); ?></label>
    <input
        type="text"
        placeholder=""
        value="<?= esc_attr( $current_field['item_name'] );?>"
        name="fields<?= $field_name; ?>[<?= $field['id'];?>][item_name]"
        class="text widefat rad4"/>
</div>
<div class="input">
    <label><?php _e("Item Description"); ?></label>
    <input
        type="text"
        placeholder=""
        value="<?= esc_attr( $current_field['item_description'] );?>"
        name="fields<?= $field_name; ?>[<?= $field['id'];?>][item_description]"
        class="text widefat rad4"/>
</div>
<div class="input">
    <label><?php _e("Price"); ?></label>
    <input
        type="text"
        placeholder=""
        value="<?= esc_attr( $current_field['price'] );?>"
        name="fields<?= $field_name; ?>[<?= $field['id'];?>][price]"
        class="text widefat rad4"/>
</div>