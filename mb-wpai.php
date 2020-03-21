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

$field_text = [ 'text', 'textarea', 'date', 'map', 'radio', 'checkbox', 'autocomplete' ];
$field_image = [ 'image', 'single_image', 'image_select', 'image_upload', 'image_advanced', 'file', 'file_advanced', 'file_upload' ];
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
    // print("<pre>".print_r($fields,true)."</pre>");

    foreach( $fields as $field ) {
        generate_normal_fields( $field, $obj );
        generate_group_fields( $field, $obj );
    }
}

function generate_group_fields( $field, $obj ) {
    global $field_group;

    if ( ! in_array( $field['type'], $field_group ) ) {
        return;
    }

    // print("<pre>".print_r($field,true)."</pre>");

    $child_fields = [];

    foreach( $field['fields'] as $child_field ) {
        if ( ! in_array( $child_field['type'], $field_group ) ) {
            array_push( $child_fields, $obj->add_field( $child_field['id'], $child_field['name'], 'textarea', null, 'Enter each value in a new line' ) );
        }
    }

    $obj->add_options( 
        $obj->add_field( $field['id'], $field['name'], 'text' ),
        '( Open )',
        $child_fields
    );

    foreach( $field['fields'] as $child_field ) {
        if ( in_array( $child_field['type'], $field_group ) ) {
            // print("<pre>".print_r($child_field,true)."</pre>");
            generate_group_fields( $child_field, $obj );
        }
    }
}

function generate_normal_fields( $field, $obj ) {
    global $field_text;
    global $field_image;

    if ( ! in_array( $field['type'], $field_text ) && ! in_array( $field['type'], $field_image ) ) {
        return;
    }

    $obj->add_field( $field['id'], $field['name'], 'textarea', null, 'Enter each value in a new line' );
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
    global $wpdb;

    $table = $wpdb->prefix . 'postmeta';

    foreach( $fields as $field ) {
        $data_lines = explode( "\r\n", $data[ $field['id'] ] );

        mb_import_text( $post_id, $data_lines, $field, $table );

        mb_import_image( $post_id, $data_lines, $field, $table );

        mb_import_group( $post_id, $data, $field, $table );
    }
}

// import image
function mb_import_image( $post_id, $data, $field, $table ) {
    global $wpdb;
    global $field_image;

    if ( ! in_array( $field['type'], $field_image ) ) {
        return;
    }

    foreach ( $data as $d ) {
        $wpdb->insert( $table, [
            'post_id'    => $post_id,
            'meta_key'   => $field['id'],
            'meta_value' => attachment_url_to_postid( $d ),
        ] );
    }
}

// import text
function mb_import_text( $post_id, $data, $field, $table ) {
    global $wpdb;
    global $field_text;

    if ( ! in_array( $field['type'], $field_text ) ) {
        return;
    }

    foreach ( $data as $d ) {
        $wpdb->insert( $table, [
            'post_id'    => $post_id,
            'meta_key'   => $field['id'],
            'meta_value' => $d,
        ] );
    }
}

// import group
function mb_import_group( $post_id, $data, $field, $table ) {
    global $wpdb;
    global $field_group;

    if ( ! in_array( $field['type'], $field_group ) ) {
        return;
    }

    $content_data .= 'a:' . count( $field['fields'] ) . ':{';

    $content_data .= mb_get_group_data( $field['fields'], $data );

    $content_data .= '}';

    $wpdb->insert( $table, [
        'post_id'    => $post_id,
        'meta_key'   => $field['id'],
        'meta_value' => $content_data,
    ] );
}

function mb_get_group_data( $field, $data ) {
    global $field_group;
    
    $content_data = '';

    foreach ( $field as $field_child ) {
        $content_data .= process_group( $field_child, $data );
        $content_data .= process_image( $field_child, $data );
        $content_data .= process_text( $field_child, $data );
    }

    return $content_data;
}

function process_text( $field, $data ) {
    global $field_text;

    if ( ! in_array( $field['type'], $field_text ) ) {
        return '';
    }

    $content_data .= 's:' . strlen( $field['id'] )  . ':"' . $field['id'] . '"' . ';s:' . strlen( $data[ $field['id'] ] ) . ':"' . $data[ $field['id'] ] . '"' . ';';

    return $content_data;
}

function process_image( $field, $data ) {
    global $field_image;

    if ( ! in_array( $field['type'], $field_image ) ) {
        return '';
    }

    $content_data .= 's:' . strlen( $field['id'] )  . ':"' . $field['id'] . '"' . ';s:' . strlen( $data[ $field['id'] ] ) . ':"' . attachment_url_to_postid( $data[ $field['id'] ] ) . '"' . ';';

    return $content_data;
}

function process_group( $field, $data ) {
    global $field_group;

    if ( ! in_array( $field['type'], $field_group ) ) {
        return '';
    }

    $temp_1 = mb_get_group_data( $field['fields'], $data  );
    $temp_2 = count( $field['fields'] );
    $content_data .= 's:' . strlen( $field['id'] )  . ':"' . $field['id'] . '"' . ';a:' . $temp_2 . ':{' . $temp_1 . '}';

    return $content_data;
}