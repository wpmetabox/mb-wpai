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
	// In case this is sub-field, try to get name of parent field.
	error_log( 'pmai_pmxi_custom_field_to_update' );
	$m_key_parent = preg_replace( '%(.*)(_[0-9]{1,}_).*%', '$1', $m_key );

	if ( ! in_array( $m_key, PMAI_Plugin::get_available_mb_fields() ) && ! in_array( $m_key_parent, PMAI_Plugin::get_available_mb_fields() ) ) {
		return $field_to_update;
	}

	return pmai_is_acf_update_allowed( $m_key, $options );
}
