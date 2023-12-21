<?php
/**
 * @param $entry
 * @param $post
 */
function pmai_pmxi_reimport( $entry, $post ) {
	global $acf;
	if ( $acf and version_compare( $acf->settings['version'], '5.0.0' ) >= 0 ) {
		$groups = acf_get_field_groups();
		if ( ! empty( $groups ) ) {
			foreach ( $groups as $group ) {
				$fields = acf_get_fields( $group );
				if ( ! empty( $fields ) ) {
					foreach ( $fields as $key => $field ) {
						if ( ! empty( $field['name'] ) ) {
							$all_existing_acf[] = '[' . $field['name'] . '] ' . $field['label'];
							// Include subfields.
							if ( isset( $field['sub_fields'] ) && ! in_array( $field['type'], [
									'repeater',
									'flexible_content',
								] ) && is_array( $field['sub_fields'] ) && ! empty( $field['sub_fields'] ) ) {
								foreach ( $field['sub_fields'] as $sub_field ) {
									if ( ! empty( $sub_field['name'] ) ) {
										$all_existing_acf[] = '[' . $field['name'] . '_' . $sub_field['name'] . '] ' . $sub_field['label'];
									}
								}
							}
						}
					}
				}
			}
		}
	} else {
		$acfs             = get_posts( [ 'posts_per_page' => - 1, 'post_type' => 'acf' ] );
		$all_existing_acf = [];
		if ( ! empty( $acfs ) ) {
			foreach ( $acfs as $key => $acf_entry ) {
				foreach ( get_post_meta( $acf_entry->ID, '' ) as $cur_meta_key => $cur_meta_val ) {
					if ( strpos( $cur_meta_key, 'field_' ) !== 0 ) {
						continue;
					}
					$field      = ( ! empty( $cur_meta_val[0] ) ) ? unserialize( $cur_meta_val[0] ) : [];
					$field_name = '[' . $field['name'] . '] ' . $field['label'];
					if ( ! in_array( $field_name, $all_existing_acf ) ) {
						$all_existing_acf[] = $field_name;
					}
					if ( ! empty( $field['sub_fields'] ) ) {
						foreach ( $field['sub_fields'] as $key => $sub_field ) {
							$sub_field_name = $field_name . '---[' . $sub_field['name'] . ']';
							if ( ! in_array( $sub_field_name, $all_existing_acf ) ) {
								$all_existing_acf[] = $sub_field_name;
							}
						}
					}
				}
			}
		}
	}
	?>
    <div class="input">
        <input type="hidden" name="mb_field_list" value="0"/>
        <input type="hidden" name="is_update_acf" value="0"/>
        <input type="checkbox" id="is_update_acf_<?= $entry; ?>" name="is_update_acf"
               value="1" <?= $post['is_update_acf'] ? 'checked="checked"' : '' ?> class="switcher"/>
        <label for="is_update_acf_<?= $entry; ?>"><?php _e( 'Advanced Custom Fields', 'mb-wpai' ) ?></label>
        <div class="switcher-target-is_update_acf_<?= $entry; ?>" style="padding-left:17px;">
            <div class="input">
                <input type="radio" id="update_mb_logic_full_update_<?= $entry; ?>" name="update_mb_logic"
                       value="full_update" <?= ( "full_update" == $post['update_mb_logic'] ) ? 'checked="checked"' : '' ?>
                       class="switcher"/>
                <label for="update_mb_logic_full_update_<?= $entry; ?>"><?php _e( 'Update all ACF fields', 'mb-wpai' ) ?></label>
            </div>
            <div class="input">
                <input type="radio" id="update_mb_logic_mapped_<?= $entry; ?>" name="update_mb_logic"
                       value="mapped" <?= ( "mapped" == $post['update_mb_logic'] ) ? 'checked="checked"' : '' ?>
                       class="switcher"/>
                <label for="update_mb_logic_mapped_<?= $entry; ?>"><?php _e( 'Update only mapped ACF groups', 'mb-wpai' ) ?></label>
            </div>
            <div class="input">
                <input type="radio" id="update_mb_logic_only_<?= $entry; ?>" name="update_mb_logic"
                       value="only" <?= ( "only" == $post['update_mb_logic'] ) ? 'checked="checked"' : '' ?>
                       class="switcher"/>
                <label for="update_mb_logic_only_<?= $entry; ?>"><?php _e( 'Update only these ACF fields, leave the rest alone', 'mb-wpai' ) ?></label>
                <div class="switcher-target-update_mb_logic_only_<?= $entry; ?> pmxi_choosen"
                     style="padding-left:17px;">

                    <span class="hidden choosen_values"><?php if ( ! empty( $all_existing_acf ) ) {
							echo implode( ',', $all_existing_acf );
						} ?></span>
                    <input class="choosen_input"
                           value="<?php if ( ! empty( $post['mb_field_list'] ) and "only" == $post['update_mb_logic'] ) {
						       echo implode( ',', $post['mb_field_list'] );
					       } ?>" type="hidden" name="mb_only_list"/>
                </div>
            </div>
            <div class="input">
                <input type="radio" id="update_mb_logic_all_except_<?= $entry; ?>" name="update_mb_logic"
                       value="all_except" <?= ( "all_except" == $post['update_mb_logic'] ) ? 'checked="checked"' : '' ?>
                       class="switcher"/>
                <label for="update_mb_logic_all_except_<?= $entry; ?>"><?php _e( 'Leave these ACF fields alone, update all other ACF fields', 'mb-wpai' ) ?></label>
                <div class="switcher-target-update_mb_logic_all_except_<?= $entry; ?> pmxi_choosen"
                     style="padding-left:17px;">

                    <span class="hidden choosen_values"><?php if ( ! empty( $all_existing_acf ) ) {
							echo implode( ',', $all_existing_acf );
						} ?></span>
                    <input class="choosen_input"
                           value="<?php if ( ! empty( $post['mb_field_list'] ) and "all_except" == $post['update_mb_logic'] ) {
						       echo implode( ',', $post['mb_field_list'] );
					       } ?>" type="hidden" name="mb_except_list"/>
                </div>
            </div>
        </div>
    </div>
	<?php
}
