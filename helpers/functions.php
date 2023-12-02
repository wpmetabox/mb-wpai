<?php

function pmai_get_meta_box_by_slug( string $slug ): ?RW_Meta_Box {
	$meta_box_registry = rwmb_get_registry( 'meta_box' );

	return $meta_box_registry->get( $slug );
}

function pmai_simplify_meta_box( RW_Meta_Box $meta_box ): RW_Meta_Box {
	$meta_box->meta_box['fields'] = pmai_simplify_fields( $meta_box->meta_box['fields'] );

	return $meta_box;
}

function pmai_simplify_fields( array $fields, $parent = null ): array {
	return array_map( function ( $field ) use ( $parent ) {
		$field_name = $parent ? "{$parent['name']}[{$field['name']}]" : $field['name'];

		if (isset($field['fields'])) {
			$field['fields'] = pmai_simplify_fields($field['fields'], $field);
		}

		$field = array_merge( $field, [
			'name' => 'fields[' . $field_name . ']',
			'id' => 'fields[' . $field_name . ']',
			'autocomplete' => false,
			'data_list'     => false,
			'readonly'      => false,
			'disabled'      => false,
			'multiple'      => false,
			'placeholder'   => '',
		] );

		return $field;
	}, $fields );
}
