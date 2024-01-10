<?php
/**
 * @param $cur_meta_key
 * @param $options
 *
 * @return mixed|void
 */
function pmai_is_mb_update_allowed( $cur_meta_key, $options ) {
	if ( $options['is_keep_former_posts'] == 'yes' ) {
		return false;
	}

	if ( $options['update_all_data'] == 'yes' ) {
		return true;
	}

	if ( ! $options['is_update_mb'] ) {
		return false;
	}

	if ( $options['is_update_mb'] && $options['update_mb_logic'] == 'full_update' ) {
		return true;
	}

	$field_list = $options['mb_field_list'];

	// Update only these fields, leave the rest alone
	if ( $options['update_all_data'] == 'no' and $options['is_update_mb'] and $options['update_mb_logic'] == 'only' ) {
		if ( ! empty( $field_list ) and is_array( $field_list ) ) {
			foreach ( $field_list as $key => $field ) {
				if ( $cur_meta_key == $field ) {
					return true;
				}
			}
		}
	}

	// Leave these fields alone, update all other fields
	if ( $options['update_all_data'] == 'no' and $options['is_update_mb'] and $options['update_mb_logic'] == 'all_except' ) {
		if ( ! empty( $field_list ) and is_array( $field_list ) ) {
			foreach ( $field_list as $key => $field ) {
				if ( $cur_meta_key == $field ) {
					return false;
				}
			}
		}
	}

	if ( $options['update_all_data'] == 'no' and $options['is_update_mb'] and $options['update_mb_logic'] == 'mapped' ) {
		// Update only mapped fields
		$mapped_mb = $options['meta_box'];

		return pmai_is_field_included( $cur_meta_key, $mapped_mb );
	}

	return false;
}
