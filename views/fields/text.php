<?php
// For every field type, we just need to create a simple text field with no attributes
$text_field = array_merge( $field, [ 
	'id' => $field['_name'],
	'field_name' => $field['_name'],
	'multiple' => false,
	'type' => 'text',
] );

if ( isset( $field['clone'] ) && $field['clone'] ) {
	$text_field['id'] = $field['_name'] . '[]';
}

$text_fields = \RW_Meta_Box::normalize_fields( [ $text_field ] );

RWMB_Field::call( 'show', $text_fields[0], false );
