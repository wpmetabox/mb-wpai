<?php

function pmai_get_meta_box_by_slug( string $slug ): ?RW_Meta_Box {
	$meta_box_registry = rwmb_get_registry( 'meta_box' );

	return $meta_box_registry->get( $slug );
}

function pmai_get_all_mb_fields( $object_type = 'post' ): array {
	$field_registry = rwmb_get_registry( 'field' );
	$fields = $field_registry->get_by_object_type( 'post' );

	return $fields[$object_type] ?? [];
}

function pmai_get_all_mb_field_ids( $fields = null ): array {
	if ( ! $fields ) {
		$fields = pmai_get_all_mb_fields();
	}

	$ids = [];

	foreach ( $fields as $field ) {
		$ids[] = $field['id'];

		if ( ! empty( $field['fields'] ) ) {
			$ids = array_merge( $ids, pmai_get_all_mb_field_ids( $field['fields'] ) );
		}
	}

	return $ids;
}
