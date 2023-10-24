<?php
/**
 * Add required and recommended plugins.
 *
 * @package MB WPAI
 */

add_action( 'tgmpa_register', 'mb_wpai_required_plugins' );

/**
 * Required Plugins
 */
function mb_wpai_required_plugins() {
	$plugins = [
		[
			'name'     => 'Meta Box',
			'slug'     => 'meta-box',
			'required' => true,
		],
	];

	tgmpa( $plugins );
}