<?php
/**
 * Plugin Name: WPAI Addon for MetaBox
 * Description: A complete add-on for importing Meta Box data.
 * Author:      Metabox.io
 * Author URI:  
 * Plugin URI:  
 * Version:     1.0.0
 * Text Domain: mb-wpai
 * Domain Path: languages
 *
 * @package MB WPAI
 */

defined( 'ABSPATH' ) || die;

final class MetaboxAddon {
	public function __construct() {
		add_action( 'init', [ $this, 'mb_wpai_check_php_version' ] );
		add_action( 'plugins_loaded', [ $this, 'load' ] );
	}

	public function load() {
		require __DIR__ . '/bootstrap.php';
	}

	public function mb_wpai_check_php_version() {
		if ( version_compare( phpversion(), '5.4', '<' ) ) {
			die( esc_html__( 'WPAI Addon for MetaBox requires PHP version 5.4+. Please contact your host and ask them to upgrade.', 'mb-wpai' ) );
		}
	}
}

require __DIR__ . '/vendor/autoload.php';

new MetaboxAddon();