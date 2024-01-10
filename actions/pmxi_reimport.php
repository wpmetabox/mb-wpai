<?php
/**
 * @param $entry
 * @param $post
 */
function pmai_pmxi_reimport( $entry, $post ) {
	$all_field_ids     = pmai_get_all_mb_field_ids();
	$all_field_ids_str = implode( ',', $all_field_ids );
	?>
	<div class="input">
		<input type="hidden" name="mb_field_list" value="0" />
		<input type="hidden" name="is_update_mb" value="0" />
		<input type="checkbox" id="is_update_mb_<?= esc_attr( $entry ); ?>" name="is_update_mb" value="1"
			<?= $post['is_update_mb'] ? 'checked="checked"' : '' ?> class="switcher" />
		<label for="is_update_mb_<?= esc_attr( $entry ); ?>">
			<?php _e( 'Meta Box', 'mb-wpai' ) ?>
		</label>
		<div class="switcher-target-is_update_mb_<?= esc_attr( $entry ); ?>" style="padding-left:17px;">
			<div class="input">
				<input type="radio" id="update_mb_logic_full_update_<?= esc_attr( $entry ); ?>" name="update_mb_logic"
					value="full_update" <?= ( 'full_update' == $post['update_mb_logic'] ) ? 'checked="checked"' : '' ?>
					class="switcher" />
				<label for="update_mb_logic_full_update_<?= esc_attr( $entry ); ?>">
					<?php _e( 'Update all Meta Box fields', 'mb-wpai' ) ?>
				</label>
			</div>
			<div class="input">
				<input type="radio" id="update_mb_logic_mapped_<?= esc_attr( $entry ); ?>" name="update_mb_logic"
					value="mapped" <?= ( 'mapped' == $post['update_mb_logic'] ) ? 'checked="checked"' : '' ?>
					class="switcher" />
				<label for="update_mb_logic_mapped_<?= esc_attr( $entry ); ?>">
					<?php _e( 'Update only mapped Meta Boxes', 'mb-wpai' ) ?>
				</label>
			</div>
			<div class="input">
				<input type="radio" id="update_mb_logic_only_<?= esc_attr( $entry ); ?>" name="update_mb_logic" value="only"
					<?= ( 'only' == $post['update_mb_logic'] ) ? 'checked="checked"' : '' ?> class="switcher" />
				<label for="update_mb_logic_only_<?= esc_attr( $entry ); ?>">
					<?php _e( 'Update only these Meta Box fields, leave the rest alone', 'mb-wpai' ) ?>
				</label>
				<div class="switcher-target-update_mb_logic_only_<?= esc_attr( $entry ); ?> pmxi_choosen"
					style="padding-left:17px;">

					<span class="hidden choosen_values">
						<?= esc_html( $all_field_ids_str ) ?>
					</span>
					<input class="choosen_input" value="
					<?php
					if ( ! empty( $post['mb_field_list'] ) and 'only' == $post['update_mb_logic'] ) {
						echo implode( ',', $post['mb_field_list'] );
					}
					?>
					" type="hidden" name="mb_only_list" />
				</div>
			</div>
			<div class="input">
				<input type="radio" id="update_mb_logic_all_except_<?= esc_attr( $entry ); ?>" name="update_mb_logic"
					value="all_except" <?= ( 'all_except' == $post['update_mb_logic'] ) ? 'checked="checked"' : '' ?>
					class="switcher" />
				<label for="update_mb_logic_all_except_<?= esc_attr( $entry ); ?>">
					<?php _e( 'Leave these Meta Box fields alone, update all other Meta Box fields', 'mb-wpai' ) ?>
				</label>
				<div class="switcher-target-update_mb_logic_all_except_<?= esc_attr( $entry ); ?> pmxi_choosen"
					style="padding-left:17px;">
					<span class="hidden choosen_values">
						<?= esc_html( $all_field_ids_str ) ?>
					</span>
					<input class="choosen_input" value="
					<?php
					if ( ! empty( $post['mb_field_list'] ) and 'all_except' == $post['update_mb_logic'] ) {
						echo implode( ',', $post['mb_field_list'] );
					}
					?>
					" type="hidden" name="mb_except_list" />
				</div>
			</div>
		</div>
	</div>
	<?php
}
