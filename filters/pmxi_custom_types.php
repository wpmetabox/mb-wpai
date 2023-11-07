<?php
/**
 * @param $custom_types
 * @return mixed
 */
function pmai_pmxi_custom_types( $custom_types ) {
	if ( ! empty( $custom_types['mb'] ) ) {
		unset( $custom_types['mb'] );
	}

	return $custom_types;
}