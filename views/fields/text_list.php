<?php
// For every field type, we just need to create a simple text field with no attributes
$wpai_attr = $field['_wpai'];
$id        = $field['_id'] ?? 'fields[' . $field['id'] . '][xpath]';

$text_list_field = array_merge( $field, [
	'id'         => $id,
	'field_name' => $id,
	// 'type' => 'text',
	'multiple'   => false, // force single value for file, checkbox_list, select, radio...
	'std'        => $wpai_attr['xpath'],
] );

$text_list_fields = \RW_Meta_Box::normalize_fields( [ $text_list_field ] );
RWMB_Field::call( 'show', $text_list_fields[0], false );
if ( $wpai_attr['reference'] !== false ) : ?>
<input type="hidden" name="fields[<?= esc_attr( $field['id'] ) ?>][reference]" value="<?= esc_attr( $wpai_attr['reference'] ?? '' ) ?>" />
<?php endif; ?>