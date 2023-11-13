<?php

if ( ! function_exists( 'pmai_get_join_attr' ) ) {
	/**
	 * @param mixed $attributes
	 * @return string
	 */
	function pmai_get_join_attr( $attributes = false ) {
		// validate
		if ( empty( $attributes ) ) {
			return '';
		}
		// vars
		$e = array();
		// loop through and render
		foreach ( $attributes as $k => $v ) {
			$e[] = $k . '="' . esc_attr( $v ) . '"';
		}
		// echo
		return implode( ' ', $e );
	}
}

if ( ! function_exists( 'pmai_join_attr' ) ) {
	/**
	 * @param mixed $attributes
	 */
	function pmai_join_attr( $attributes = false ) {
		echo pmai_get_join_attr( $attributes );
	}
}

function pmai_get_meta_box_by_slug(string $slug)
{	
	$meta_box_registry = rwmb_get_registry( 'meta_box' );
	$meta_box = $meta_box_registry->get($slug);

	return $meta_box->meta_box;
}