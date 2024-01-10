<?php
/**
 * This filter fires when the user clicks the Save Settings button on the Import Options screen.
 * This modifies the $post array before it is saved to the database, allowing you to add or remove options.
 * In this example, we are adding the Meta Box fields to the list of fields to update.
 *
 * @param array $post
 */
function pmai_pmxi_save_options( array $post ): array {
	if ( PMXI_Plugin::getInstance()->getAdminCurrentScreen()->action == 'options' ) {
		if ( $post['update_mb_logic'] == 'only' ) {
			$post['mb_field_list'] = explode( ',', $post['mb_only_list'] );
		} elseif ( $post['update_mb_logic'] == 'all_except' ) {
			$post['mb_field_list'] = explode( ',', $post['mb_except_list'] );
		}
	}

	return $post;
}
