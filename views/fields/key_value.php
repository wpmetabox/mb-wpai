<?php
// For every field type, we just need to create a simple text field with no attributes
$wpai_attr = $field['_wpai'];
$id = $field['_id'] ?? 'fields[' . $field['id'] . '][xpath]';

$key_value_field = array_merge( $field, [ 
	'id' => $id,
	'field_name' => $id,
	'std' => $wpai_attr['xpath'],
] );

$key_value_fields = \RW_Meta_Box::normalize_fields( [ $key_value_field ] );
RWMB_Field::call( 'show', $key_value_fields[0], false );
?>
<input type="hidden" name="fields[<?= esc_attr( $field['id'] ) ?>][reference]" value="<?= esc_attr($wpai_attr['reference'] ?? '') ?>" />