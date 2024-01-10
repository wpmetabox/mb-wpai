<?php
// For every field type, we just need to create a simple text field with no attributes
$wpai_attr = $field['_wpai'];
$id        = $field['_id'] ?? 'fields[' . $field['id'] . '][xpath]';

$fieldset_text_field = array_merge( $field, [
	'id'         => $id,
	'field_name' => $id,
	// 'type' => 'text',
	'multiple'   => false, // force single value for file, checkbox_list, select, radio...
	'std'        => $wpai_attr['xpath'],
] );

$fieldset_text_fields = \RW_Meta_Box::normalize_fields( [ $fieldset_text_field ] );
RWMB_Field::call( 'show', $fieldset_text_fields[0], false );
?>
<input type="hidden" name="fields[<?= esc_attr( $field['id'] ) ?>][reference]" value="<?= esc_attr( $field['reference'] ?? '' ) ?>" />