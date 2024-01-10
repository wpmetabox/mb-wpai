<?php
/**
 * This filter is only called when the Choose which data to update setting is selected
 *  and only for fields entered in the Custom Fields section of the import.
 *
 * @param $field_to_update
 * @param $post_type
 * @param $options
 * @param $m_key
 *
 * @return mixed|void
 */
function pmai_pmxi_custom_field_to_update( $field_to_update, $post_type, $options, $m_key ) {
	$mb_fields = PMAI_Plugin::get_available_mb_fields();

	// If this is not a MB field, let WP AI handle it.
	if ( ! isset( $mb_fields[ $m_key ] ) ) {
		return $field_to_update;
	}

	return pmai_is_mb_update_allowed( $m_key, $options );
}
