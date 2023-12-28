<?php
// For every field type, we just need to create a simple text field with no attributes
$wpai_attr = $field['_wpai'];
$id = 'fields[' . $field['id'] . '][xpath]';

function add_repeater_field( $group ) {
	foreach ( $group['fields'] as $index => $field ) {
		if ( $field['type'] === 'group' ) {
			$group['fields'][ $index ] = add_repeater_field( $field );
		} else {
			$group['fields'][ $index ] = $field;
		}
	}

	$group['fields'] = array_merge( [ 
		[ 
			'id' => 'foreach',
			'type' => 'text',
			'name' => 'For each',
			'field_name' => 'foreach',
		],
	], $group['fields']);

	return $group;
}

$group_field = $field;
$group_field['id'] = $id;
$group_field['field_name'] = $id;
$group_field['std'] = $group_field['_wpai']['xpath'];
$group_field = add_repeater_field( $group_field );

$group_fields = \RW_Meta_Box::normalize_fields( [ $group_field ] );
RWMB_Field::call( 'show', $group_fields[0], false );
?>
<input type="hidden" name="fields[<?= esc_attr( $field['id'] ) ?>][reference]"
	value="<?= esc_attr( $wpai_attr['reference'] ) ?>" />
<input type="hidden" name="fields[<?= esc_attr( $field['id'] ) ?>][options]"
	value="<?= esc_attr( $wpai_attr['options'] ) ?>" />