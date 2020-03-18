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

$fields_1 = [ 'text', 'textarea' ];
$fields_2 = [ 'image' ];
$fields_3 = [ 'radio' ];

add_action( 'init', 'get_mb_fields', 99);

function get_mb_fields( $custom_type ) {
    global $mb_objects;
    global $fields;

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
            array_push( $mb_objects, $mb->meta_box );
            $fields = array_merge( $mb->meta_box['fields'], $fields );
        }
    }

    execute( $mb_objects );
}

function generate_fields( $mbs, $obj ) {
    global $fields;
    // global $fields_1;
    // global $fields_2;
    // global $fields_3;   
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
        // if ( in_array( $field['type'], $fields_2 ) ) {
        //     $obj->import_images( $field['id'], $field['name'] );
        // } elseif ( in_array( $field['type'], $fields_1 ) ) {
        //     $obj->add_field( $field['id'], $field['name'], $field['type'] );
        // }
        $obj->add_field( $field['id'], $field['name'], 'textarea', null, 'Enter each value in a new line' );
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
    global $fields_1;
    global $fields_2;
    global $fields_3;
    global $wpdb;

    $table = $wpdb->prefix . 'postmeta';

    // $wpdb->insert( $table, [
    //     'post_id'    => '792',
    //     'meta_key'   => 'cus_img',
    //     'meta_value' => '783',
    // ] );

    foreach( $fields as $field ) {
        // if ( in_array( $field['type'], $fields_1 ) || in_array( $field['type'], $fields_3 ) ) {
        //     update_post_meta( $post_id, $field['id'], $data[ $field['id'] ] );
        // }
        // elseif ( in_array( $field['type'], $fields_2 ) ) {
        //     foreach ( $images as $image ) {
        //         $wpdb->insert( $table, [
        //             'post_id'    => $post_id,
        //             'meta_key'   => $field['id'],
        //             'meta_value' => $data[ $field['id'] ],
        //         ] );
        //     }
        // }
        
        $data_lines = explode( "\r\n", $data[ $field['id'] ] );
        foreach ( $data_lines as $d ) {
            $wpdb->insert( $table, [
                'post_id'    => $post_id,
                'meta_key'   => $field['id'],
                'meta_value' => $d,
            ] );
        }
    }
}