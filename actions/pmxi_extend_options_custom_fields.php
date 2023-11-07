<?php
/**
 * @param $post_type
 * @param $post
 */
function mbai_pmxi_extend_options_custom_fields( $post_type, $post ) {
	$controller = new PMAI_Admin_Import();
	$controller->index( $post_type, $post );
}
