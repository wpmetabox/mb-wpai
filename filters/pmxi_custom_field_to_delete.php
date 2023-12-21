<?php
/**
 * This filter is only called when the Choose which data to update setting is selected 
 * and only for fields entered in the Custom Fields section of the import. 
 * 
 * @param $field_to_delete
 * @param $pid
 * @param $post_type
 * @param $options
 * @param $cur_meta_key
 *
 * @return mixed|void
 */
function pmai_pmxi_custom_field_to_delete( $field_to_delete, $pid, $post_type, $options, $cur_meta_key ) {
	// In case this is sub-field, try to get name of parent field.
	$m_key_parent = preg_replace( '%(.*)(_[0-9]{1,}_).*%', '$1', $cur_meta_key );

	if ( ! in_array( $cur_meta_key, PMAI_Plugin::get_available_mb_fields() ) && ! in_array( $m_key_parent, PMAI_Plugin::get_available_mb_fields() ) ) {
		return $field_to_delete;
	}

	return pmai_is_acf_update_allowed( $cur_meta_key, $options );
}
