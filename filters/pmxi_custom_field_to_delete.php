<?php
/**
 * @param $field_to_delete
 * @param $pid
 * @param $post_type
 * @param $options
 * @param $cur_meta_key
 * @return mixed|void
 */
function mbai_pmxi_custom_field_to_delete($field_to_delete, $pid, $post_type, $options, $cur_meta_key ) {
    // In case this is sub-field, try to get name of parent field.
    $m_key_parent = preg_replace('%(.*)(_[0-9]{1,}_).*%', '$1', $cur_meta_key);

	if ( ! in_array($cur_meta_key, MBAI_Plugin::get_available_acf_fields()) && ! in_array($m_key_parent, MBAI_Plugin::get_available_acf_fields()) ) return $field_to_delete;

	return mbai_is_acf_update_allowed($cur_meta_key, $options);
}
