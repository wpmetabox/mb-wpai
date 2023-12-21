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
	$mb_fields = PMAI_Plugin::get_available_mb_fields();
	
	// If this is not a MB field, let WP AI handle it.
	if (! isset( $mb_fields[$cur_meta_key] ) ) {
		return $field_to_delete;
	}

	return pmai_is_mb_update_allowed( $cur_meta_key, $options );
}
