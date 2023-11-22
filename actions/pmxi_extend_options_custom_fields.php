<?php
function pmai_pmxi_extend_options_custom_fields( string $post_type, array $post ) {
	(new PMAI_Admin_Import())->index( $post_type, $post );
}
