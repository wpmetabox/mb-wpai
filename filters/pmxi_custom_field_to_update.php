<?php
/**
 * @param $field_to_update
 * @param $post_type
 * @param $options
 * @param $m_key
 * @return mixed|void
 */
function mbai_pmxi_custom_field_to_update($field_to_update, $post_type, $options, $m_key ) {
    // In case this is sub-field, try to get name of parent field.
    error_log('mbai_pmxi_custom_field_to_update');
    $m_key_parent = preg_replace('%(.*)(_[0-9]{1,}_).*%', '$1', $m_key);
    
	if ( ! in_array($m_key, MBAI_Plugin::get_available_acf_fields()) && ! in_array($m_key_parent, MBAI_Plugin::get_available_acf_fields()) ) return $field_to_update;

	return mbai_is_acf_update_allowed($m_key, $options);
}
