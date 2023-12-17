<?php

namespace MetaBox\WPAI;

use MetaBox\WPAI\Fields\FieldHandler;
use PMXI_API;

final class MetaBoxService {

	private static function get_object_type( FieldHandler $field ): string {
		$import_types = [ 
			'import_users'  => 'user',
			'shop_customer' => 'user',
			'taxonomies'    => 'term',
		];

		return $import_types[ $field->getImportType()] ?? 'post';
	}

	public static function set_meta( FieldHandler $field, int $pid, string $name, $value ) {
		$object_type = self::get_object_type( $field );

		rwmb_set_meta( $pid, $name, $value, [ 
			'object_type' => $object_type,
		] );
	}

	public static function get_meta( FieldHandler $field, $pid, $name ) {
		$object_type = self::get_object_type( $field );

		return rwmb_meta( $name, [ 
			'object_type' => $object_type,
			'post_id'     => $pid,
			'single'      => true,
		], $pid );
	}

	/**
	 * @param $img_url
	 * @param $pid
	 * @param $logger
	 * @param mixed $search_in_gallery
	 * @param mixed $search_in_files
	 *
	 * @param array $importData
	 *
	 * @return mixed|int|\WP_Error
	 */
	public static function import_image( $img_url, $pid, $logger, $search_in_gallery = false, $search_in_files = false, $importData = [] ) {

		// Search image attachment by ID.
		if ( $search_in_gallery and is_numeric( $img_url ) ) {
			if ( wp_get_attachment_url( $img_url ) ) {
				return $img_url;
			}
		}

		$downloadFiles = "yes";
		$fileName      = "";
		// Search for existing image in /files folder.
		if ( $search_in_files ) {
			// Before start searching check for existing image in pmxi_images table.
			if ( $search_in_gallery ) {
				$logger and call_user_func( $logger, sprintf( __( '- Searching for existing image `%s` by Filename...', 'wp_all_import_plugin' ), rawurldecode( $img_url ) ) );
				$imageList = new \PMXI_Image_List();
				$attch     = $imageList->getExistingImageByFilename( basename( $img_url ) );
				if ( $attch ) {
					$logger and call_user_func( $logger, sprintf( __( 'Existing image was found by Filename `%s`...', 'wp_all_import_plugin' ), basename( $img_url ) ) );

					return $attch->ID;
				}
			}

			$downloadFiles = "no";
			$fileName      = wp_all_import_basename( parse_url( trim( $img_url ), PHP_URL_PATH ) );
		}

		return PMXI_API::upload_image( $pid, $img_url, $downloadFiles, $logger, true, $fileName, 'images', $search_in_gallery, $importData['articleData'], $importData );
	}

	/**
	 * @param $atch_url
	 * @param $pid
	 * @param $logger
	 * @param mixed $fast
	 * @param mixed $search_in_gallery
	 * @param mixed $search_in_files
	 *
	 * @param array $importData
	 *
	 * @return mixed|int|\WP_Error
	 */
	public static function import_file( $atch_url, $pid, $logger, $fast = false, $search_in_gallery = false, $search_in_files = false, $importData = [] ) {
        // Clean up the URL, remove spaces and convert them to %20 (URL encoded space)
        $atch_url = str_replace( ' ', '%20', $atch_url );
        // Remove the query string from the URL
        $atch_url = strtok( $atch_url, '?' );

		// search file attachment by ID
		if ( $search_in_gallery and is_numeric( $atch_url ) ) {
			if ( wp_get_attachment_url( $atch_url ) ) {
				return $atch_url;
			}
		}

		$downloadFiles = "yes";
		$fileName      = "";
		// Search for existing image in /files folder.
		if ( $search_in_files ) {
			// Before start searching check for existing file in pmxi_images table.
			if ( $search_in_gallery ) {
				$logger and call_user_func( $logger, sprintf( __( '- Searching for existing file `%s` by Filename...', 'wp_all_import_plugin' ), rawurldecode( $atch_url ) ) );
				$imageList = new \PMXI_Image_List();
				$attch     = $imageList->getExistingImageByFilename( basename( $atch_url ) );
				if ( $attch ) {
					$logger and call_user_func( $logger, sprintf( __( 'Existing file was found by Filename `%s`...', 'wp_all_import_plugin' ), basename( $atch_url ) ) );

					return $attch->ID;
				}
			}

			$downloadFiles = "no";
			$fileName      = basename( $atch_url );
		}

		$attachment_id = PMXI_API::upload_image( $pid, $atch_url, $downloadFiles, $logger, true, $fileName, "files", $search_in_gallery, $importData['articleData'], $importData );

        return [
            'ID'    => $attachment_id,
            'name'  => basename( $atch_url ),
            'path'  => $atch_url,
            'url'   => get_attached_file( $attachment_id ),
            'title' => basename( $atch_url ),
        ];
	}

	/**
	 * @param $values
	 * @param array $post_types
	 *
	 * @return array
	 */
	public static function get_posts_by_relationship( $values, $post_types ) {
		$post_ids = [];
		$values   = array_filter( $values );
		if ( ! empty( $values ) ) {
			if ( ! empty( $post_types ) && ! is_array( $post_types ) ) {
				$post_types = [ $post_types ];
			}
			$values = array_map( 'trim', $values );
			global $wpdb;
			foreach ( $values as $ev ) {
				$relation = false;
				if ( ctype_digit( $ev ) ) {
					if ( empty( $post_types ) ) {
						$relation = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID = %d", $ev ) );
					} else {
						$relation = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID = %d AND post_type IN ('" . implode( "','", $post_types ) . "')", $ev ) );
					}
				}
				if ( empty( $relation ) ) {
					if ( empty( $post_types ) ) {
						$sql      = "SELECT * FROM {$wpdb->posts} WHERE post_type != %s AND ( post_title = %s OR post_name = %s )";
						$relation = $wpdb->get_row( $wpdb->prepare( $sql, 'revision', $ev, sanitize_title_for_query( $ev ) ) );
					} else {
						$sql      = "SELECT * FROM {$wpdb->posts} WHERE post_type IN ('" . implode( "','", $post_types ) . "') AND ( post_title = %s OR post_name = %s )";
						$relation = $wpdb->get_row( $wpdb->prepare( $sql, $ev, sanitize_title_for_query( $ev ) ) );
					}
				}
				if ( $relation ) {
					$post_ids[] = (string) $relation->ID;
				}
			}
		}

		return apply_filters( 'pmxi_acf_post_relationship_ids', $post_ids );
	}
}
