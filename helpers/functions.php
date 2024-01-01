<?php

function pmai_get_meta_box_by_slug( string $slug ): ?RW_Meta_Box {
	$meta_box_registry = rwmb_get_registry( 'meta_box' );

	return $meta_box_registry->get( $slug );
}

function pmai_get_all_mb_fields( $object_type = 'post' ): array {
	$field_registry = rwmb_get_registry( 'field' );
	$fields         = $field_registry->get_by_object_type( 'post' );

	return $fields[ $object_type ] ?? [];
}

function pmai_get_all_mb_field_ids( $parent = [] ): array {
	$fields = empty( $parent ) ? pmai_get_all_mb_fields() : $parent['fields'];

	$ids = [];

	foreach ( $fields as $field ) {
		$field['_id'] = isset( $parent['_id'] ) ? $parent['_id'] . '.' . $field['id'] : $field['id'];

		$ids[] = $field['_id'];

		if ( ! empty( $field['fields'] ) ) {
			$ids = array_merge( $ids, pmai_get_all_mb_field_ids( $field ) );
		}
	}

	return $ids;
}

/**
 * Get meta box fields by meta box ID.
 * 
 * @param string $meta_box_id Meta box ID.
 * 
 * @return string[]
 */
function pmai_get_meta_box_fields_id( $meta_box_id ) {
    $meta_box = pmai_get_meta_box_by_slug( $meta_box_id );

    $meta_box_fields = $meta_box->meta_box;
    
    $fields = pmai_get_all_mb_field_ids( $meta_box_fields );

    return $fields;
}

/**
 * Check if a field is included in a meta box using dot notation syntax
 * 
 * @param string $field Field ID in dot notation.
 * @param {string: bool}[] $mapped_mb Mapped meta boxes.
 * 
 * @return bool
 */
function pmai_is_field_included( $field, $mapped_mb ) {
    if ( ! empty( $mapped_mb ) and is_array( $mapped_mb ) ) {
        foreach ( $mapped_mb as $id => $enabled ) {
            if ( ! $enabled ) {
                continue;
            }
    
            $fields = pmai_get_meta_box_fields_id( $id );

            if ( in_array( $field, $fields ) ) {
                return true;
            }
        }
    }

    return false;
}

function pmai_square_to_dot_notation( $field ) {
    $field = str_replace( '[]', '', $field );
    $field = str_replace( '[', '.', $field );
    $field = str_replace( ']', '', $field );

    return $field;
}


function pmai_get_segment( $string ) {
    $segments = explode( '/', $string );
    
    for ($i = 0; $i < count($segments); $i++) {
        if (strpos($segments[$i], '[.') !== false) {
            return count($segments) - $i - 1;
        }
    }

    return false;
}


function pmai_array_deep( $array, $deepness ) {
    while ( $deepness > 0 ) {
        $new_array = [];

        foreach ( $array as $element ) {
            $new_array[] = [$element];
        }

        $array = $new_array;
        $deepness--;
    }

    return $array;
}
