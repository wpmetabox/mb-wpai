<?php
function pmai_pmxi_extend_options_custom_fields(string $post_type, array $post ) {
	$controller = new PMAI_Admin_Import();
	$controller->index( $post_type, $post );
}
