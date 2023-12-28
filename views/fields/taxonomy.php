<?php
// For every field type, we just need to create a simple text field with no attributes
$wpai_attr = $field['_wpai'];
$id = 'fields[' . $field['id'] . '][xpath]';

$taxonomy_field = $field;
$taxonomy_field['id'] = $id;
$taxonomy_field['field_name'] = $id;
$taxonomy_field['std'] = $taxonomy_field['_wpai']['xpath'];
$taxonomy_field['taxonomy'] = $taxonomy_field['taxonomy'] ?? 'category';
$taxonomy_field['type'] = 'text';
$taxonomy_field['placeholder'] = 'Enter a taxonomy term';

$taxonomy_fields = \RW_Meta_Box::normalize_fields( [ $taxonomy_field ] );
RWMB_Field::call( 'show', $taxonomy_fields[0], false );
?>
<input type="hidden" name="fields[<?= esc_attr( $field['id'] ) ?>][reference]"
	value="<?= esc_attr( $wpai_attr['reference'] ) ?>" />
<input type="hidden" name="fields[<?= esc_attr( $field['id'] ) ?>][options]"
	value="<?= esc_attr( $wpai_attr['options'] ) ?>" />