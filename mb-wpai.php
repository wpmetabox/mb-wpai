<?php
/**
 * Plugin Name: MB WP All Import
 * Description: MB WP All Import
 * Author:      Metabox.io
 * Author URI:  https://metabox.io
 * Plugin URI:  https://metabox.io/plugins/mb-wpai/
 * Version:     0.0.1
 * Text Domain: mb-wpai
 * Domain Path: languages
 *
 * @package MB WPAI
 */

defined( 'ABSPATH' ) || die;

final class MetaboxAddon {
	private $mb_wpai;
	private $fields = [];
	private $mb_objects = [];

	public function __construct() {
		$this->mb_wpai = new RapidAddon('Meta Box Add-on', 'mb_wpai');

		add_action( 'init', [ $this, 'mb_wpai_check_php_version' ] );
		add_action( 'init', [ $this, 'get_mb_fields' ], 30);
        add_action( 'init', [ $this, 'load' ] );
	}

	public function load() {
		require __DIR__ . '/bootstrap.php';
	}

	public function get_mb_fields( $custom_type ) {
		$custom_type = 'customer';
	
		$meta_box_registry = rwmb_get_registry( 'meta_box' );
	
		if ( ! $meta_box_registry ) {
			return;
		}
	
		$args = [
			'object_type' => 'post',
		];
	
		$metabox_fields = $meta_box_registry->get_by( $args );
	
		foreach( $metabox_fields as $mb ) {
			if ( $custom_type === $mb->meta_box['post_types'][0] ) {
				array_push( $this->mb_objects, $mb->meta_box );
				$this->fields = array_merge( $mb->meta_box['fields'], $this->fields );
			}
		}
	
		$this->execute( $this->mb_objects );
	}

	public function mb_wpai_check_php_version() {
		if ( version_compare( phpversion(), '5.4', '<' ) ) {
			die( esc_html__( 'WPAI Addon for MetaBox requires PHP version 5.4+. Please contact your host and ask them to upgrade.', 'mb-wpai' ) );
		}
    }
    
    public function generate_fields( $mbs, $obj ) {
		foreach( $this->fields as $field ) {
			$obj->add_field( $field['id'], $field['name'], $field['type'] );
		}
	}
	
	public function execute( $mbs ) {
		$this->generate_fields( $mbs, $this->mb_wpai );
	
		// $this->mb_wpai->set_import_function( [ $this, 'mb_wpai_import' ] );
	
		$this->mb_wpai->run();
	}
	
	public function mb_wpai_import( $post_id, $data, $import_options ) {
		foreach( $this->fields as $field ) {
			if ( 'text' === $field['type'] && $this->$mb_wpai->can_update_meta( $field['id'], $import_options) ) {
				update_post_meta($post_id, $field['id'], $data[ $field['id'] ]);
			}
		}
	}
}

require __DIR__ . '/vendor/autoload.php';

new MetaboxAddon();