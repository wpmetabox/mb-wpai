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

include "RapidAddon.php";
require_once( ABSPATH . "wp-config.php" );
require_once( ABSPATH . "wp-includes/wp-db.php" );

$mb_wpai = new RapidAddon('Meta Box Add-on', 'mb_wpai');

$fields = [];
$mb_objects = [];

$field_text = [ 'text', 'textarea', 'date', 'location', 'radio', 'checkbox' ];
$field_image = [ 'image', 'single_image', 'image_select', 'image_upload', 'image_advanced' ];
$field_group = [ 'group' ];

add_action( 'init', 'get_mb_fields', 99);

function get_mb_fields( $custom_type ) {
    global $mb_objects;
    global $fields;

    $custom_type = 'fish';

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
            array_push( $mb_objects, $mb->meta_box );
            $fields = array_merge( $mb->meta_box['fields'], $fields );
        }
    }

    execute( $mb_objects );
}

function generate_fields( $mbs, $obj ) {
    global $fields;
    global $field_image;
    global $field_group;
    // print("<pre>".print_r($fields,true)."</pre>");

    // $obj->add_options( 
    //     $obj->add_field( 'property_price', 'Property Price', 'text', null, 'Only digits, example: 435000' ),
    //     'Price Settings', 
    //     array(
    //             $obj->add_field( 'property_price_postfix', 'Price Postfix', 'text', null, 'Example: Per Month' ),
    //             $obj->add_field( 'property_price_currency', 'Currency Symbol', 'text', null, 'Example: $, or €' )
    //     )
	// );

    foreach( $fields as $field ) {
        if ( in_array( $field['type'], $field_group ) ) {
            // print("<pre>".print_r($field,true)."</pre>");
			$child_fields = [];

			foreach( $field['fields'] as $child_field ) {
				array_push( $child_fields, $obj->add_field( $child_field['id'], $child_field['name'], 'textarea', null, 'Enter each value in a new line' ) );
			}

            $obj->add_options( 
				$obj->add_field( $field['id'], $field['name'], 'text' ),
				'( Open )',
				$child_fields
			);
        } else {
            $obj->add_field( $field['id'], $field['name'], 'textarea', null, 'Enter each value in a new line' );
        }
        
    }
}

function execute( $mbs ) {
    global $mb_wpai;

    generate_fields( $mbs, $mb_wpai );

    $mb_wpai->set_import_function( 'mb_wpai_import' );

    $mb_wpai->run();
}

function mb_wpai_import( $post_id, $data, $import_options ) {
    global $mb_wpai;
    global $fields;
    global $field_image;
    global $field_group;
    global $wpdb;

    $table = $wpdb->prefix . 'postmeta';

    // $wpdb->insert( $table, [
    //     'post_id'    => '792',
    //     'meta_key'   => 'cus_img',
    //     'meta_value' => '783',
    // ] );

    foreach( $fields as $field ) {
        $data_lines = explode( "\r\n", $data[ $field['id'] ] );

        mb_import_text();

        mb_import_image();

        mb_import_group();
    }
}

// import image
function mb_import_image( $type, $post_id, $data, $field, $data_lines ) {
    global $field_image;
    global $fields;

    if ( ! in_array( $type, $field_image ) ) {
        return;
    }

    foreach ( $data_lines as $d ) {
        $wpdb->insert( $table, [
            'post_id'    => $post_id,
            'meta_key'   => $field['id'],
            'meta_value' => attachment_url_to_postid( $d ),
        ] );
    }
}

// import text
function mb_import_text( $type, $post_id, $data, $field, $data_lines ) {
    global $field_text;

    if ( ! in_array( $type, $field_text ) ) {
        return;
    }

    foreach ( $data_lines as $d ) {
        $wpdb->insert( $table, [
            'post_id'    => $post_id,
            'meta_key'   => $field['id'],
            'meta_value' => $d,
        ] );
    }
}

// import group
function mb_import_group( $type, $post_id, $data, $field, $data_lines ) {
    global $field_group;
    global $fields;

    if ( ! in_array( $type, $field_group ) ) {
        return;
    }

    $content_data = 'a:' . count( $field['fields'] ) . ':{';

    foreach ( $field['fields'] as $field_child ) {
        $content_data .= 's:' . strlen( $field_child['id'] ) . ':"' . $field_child['id'] . '"' . ';s:' . strlen( $data[ $field_child['id'] ] ) . ':"' . $data[ $field_child['id'] ] . '"' . ';';
    }

    $content_data .= '}';

    $wpdb->insert( $table, [
        'post_id'    => $post_id,
        'meta_key'   => $field['id'],
        'meta_value' => $content_data,
    ] );
}