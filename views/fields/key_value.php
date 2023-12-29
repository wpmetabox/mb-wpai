<?php
$key_value_field = array_merge($field, [
    'id' => $field['_name'],
    'std' => $field_value,
    'field_name' => $field['_name'],
]);

$key_value_fields = \RW_Meta_Box::normalize_fields( [$key_value_field] );

RWMB_Field::call('show', $key_value_fields[0] , false );
?>
<input type="hidden" name="fields[<?= esc_attr( $field['id'] ) ?>][reference]" value="<?= esc_attr($wpai_attr['reference']) ?>" />
<input type="hidden" name="fields[<?= esc_attr( $field['id'] ) ?>][options]" value="[]" />