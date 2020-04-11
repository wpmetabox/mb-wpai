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

require __DIR__ . '/vendor/autoload.php';

require 'RapidAddon.php';

require_once( ABSPATH . "wp-config.php" );
require_once( ABSPATH . "wp-includes/wp-db.php" );

$mb_wpai = new RapidAddon('Meta Box Add-on', 'mb_wpai');

$fields = [];
$mb_objects = [];

$field_text = [ 'text', 'textarea', 'date', 'map', 'radio', 'checkbox', 'autocomplete', 'number' ];
$field_image = [ 'image', 'single_image', 'image_select', 'image_upload', 'file', 'file_upload' ];
$field_multi_images = [ 'image_advanced', 'file_advanced' ];
$field_group = [ 'group' ];

add_action( 'init', 'get_mb_fields', 99);
add_action( 'init', 'enqueue_scripts', 99);

function enqueue_scripts() {
    wp_enqueue_script( 'mb-wpai', plugin_dir_url( __FILE__ ) . 'assets/scripts.js', ['jquery'], '1.0.0', true );
}

function get_mb_fields( $custom_type ) {
    global $mb_objects;
    global $fields;

    $custom_type = 'event';

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

    // var_dump( get_post_types(array('_builtin' => false, 'show_ui' => true), 'objects') );

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
        mb_import_text( $post_id, $data[ $field['id'] ], $field, $table );

        mb_import_image( $post_id, $data[ $field['id'] ], $field, $table );

        mb_import_group( $post_id, $data[ $field['id'] ], $field, $table );

    }
}

// import image
function mb_import_image( $post_id, $data, $field, $table ) {
    global $wpdb;
    global $field_image;
    global $field_multi_images;

    if ( ! in_array( $field['type'], $field_image ) && ! in_array( $field['type'], $field_multi_images ) ) {
        return;
    }

    if ( $field['clone'] ) {
        foreach ( $data as $d ) {
            $content_data[] = attachment_url_to_postid( $d );
        }

        $wpdb->insert( $table, [
            'post_id'    => $post_id,
            'meta_key'   => $field['id'],
            'meta_value' => serialize( $content_data ),
        ] );
    }
    else {
        $wpdb->insert( $table, [
            'post_id'    => $post_id,
            'meta_key'   => $field['id'],
            'meta_value' => attachment_url_to_postid( $data ),
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

    if ( $field['clone'] ) {
        $wpdb->insert( $table, [
            'post_id'    => $post_id,
            'meta_key'   => $field['id'],
            'meta_value' => serialize( $data ),
        ] );
        // a:1:{s:16:"text_9z0sebegmsd";a:2:{i:0;s:8:"Standard";i:1;s:8:"Standard";}}
        // a:2:{i:0;s:8:"Standard";i:1;s:5:"Media";}
    }
    else {
        $wpdb->insert( $table, [
            'post_id'    => $post_id,
            'meta_key'   => $field['id'],
            'meta_value' => $data,
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

    // $content_data[] = mb_get_group_data( $field['fields'], $data );

    $wpdb->insert( $table, [
        'post_id'    => $post_id,
        'meta_key'   => $field['id'],
        'meta_value' => serialize( $data )
    ] );
}

function mb_get_group_data( $field, $data ) {
    global $field_group;

    $content_data = [];

    foreach ( $field as $field_child ) {
        $content_data[$field_child['id']] = process_group( $field_child, $data );
        $content_data[$field_child['id']] = process_image( $field_child, $data );
        $content_data[$field_child['id']] = process_text( $field_child, $data );
    }

    return $content_data;
}

function process_text( $field, $data ) {
    global $field_text;

    if ( ! in_array( $field['type'], $field_text ) ) {
        return '';
    }

    $content_data = $data[ $field['id'] ];

    return $content_data;
}

function process_image( $field, $data ) {
    global $field_image;

    if ( ! in_array( $field['type'], $field_image ) ) {
        return '';
    }

    $content_data = attachment_url_to_postid( $data[ $field['id'] ] );

    return $content_data;
}

function process_group( $field, $data ) {
    global $field_group;

    if ( ! in_array( $field['type'], $field_group ) ) {
        return '';
    }

    $content_data[$field['id']] = mb_get_group_data( $field['fields'], $data  );

    return $content_data;
}


// a:2:{i:0;a:2:{s:16:"text_mfsud1jlsyn";s:8:"Standard";s:28:"text_mfsud1jlsyn_vpd6i9813dd";s:2:"10";}i:1;a:2:{s:16:"text_mfsud1jlsyn";s:5:"Media";s:28:"text_mfsud1jlsyn_vpd6i9813dd";s:2:"20";}}