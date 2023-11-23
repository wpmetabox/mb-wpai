<?php

namespace MetaBox\WPAI;

use PMXI_API;
use MetaBox\WPAI\Fields\Field;

/**
 * Class MetaboxService
 * @package MetaBox\WPAI\
 */
final class MetaboxService {

	/**
	 * @param $version
	 *
	 * @return mixed
	 */
	public static function isACFNewerThan( $version ) {
		global $acf;

		return version_compare( $acf->settings['version'], $version ) >= 0;
	}

	/**
	 *
	 * Set ACF field value
	 *
	 * @param \MetaBox\WPAI\Fields\Field $field
	 * @param $pid
	 * @param $name
	 * @param $value
	 */
	public static function update_post_meta( Field $field, $pid, $name, $value ) {
		switch ( $field->getImportType() ) {
			case 'import_users':
			case 'shop_customer':
				update_user_meta( $pid, $name, $value );
				break;
			case 'taxonomies':
				update_term_meta( $pid, $name, $value );
				break;
			default:
				rwmb_set_meta( $pid, $name, $value );
				break;
		}
	}

	/**
	 *
	 * Get ACF field value
	 *
	 * @param Field $field
	 * @param $pid
	 * @param $name
	 *
	 * @return mixed
	 */
	public static function get_post_meta( Field $field, $pid, $name ) {
		switch ( $field->getImportType() ) {
			case 'import_users':
			case 'shop_customer':
				$value = get_user_meta( $pid, $name, true );
				break;
			case 'taxonomies':
				$value = get_term_meta( $pid, $name, true );
				break;
			default:
				$value = get_post_meta( $pid, $name, true );
				break;
		}

		return $value;
	}

	/**
	 *
	 * Assign taxonomy terms with particular post
	 *
	 * @param $pid
	 * @param $assign_taxes
	 * @param $tx_name
	 * @param mixed $logger
	 */
	public static function associate_terms( $pid, $assign_taxes, $tx_name, $logger = false ) {

		$use_wp_set_object_terms = apply_filters( 'wp_all_import_use_wp_set_object_terms', false, $tx_name );
		if ( $use_wp_set_object_terms ) {
			$term_ids = [];
			if ( ! empty( $assign_taxes ) ) {
				foreach ( $assign_taxes as $ttid ) {
					$term = get_term_by( 'term_taxonomy_id', $ttid, $tx_name );
					if ( $term ) {
						$term_ids[] = $term->term_id;
					}
				}
			}

			return wp_set_object_terms( $pid, $term_ids, $tx_name );
		}

		global $wpdb;

		$term_ids = wp_get_object_terms( $pid, $tx_name, [ 'fields' => 'ids' ] );

		$assign_taxes = ( is_array( $assign_taxes ) ) ? array_filter( $assign_taxes ) : false;

		if ( ! empty( $term_ids ) && ! is_wp_error( $term_ids ) ) {
			$in_tt_ids = "'" . implode( "', '", $term_ids ) . "'";
			$wpdb->query( "UPDATE {$wpdb->term_taxonomy} SET count = count - 1 WHERE term_taxonomy_id IN ($in_tt_ids) AND count > 0" );
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->term_relationships} WHERE object_id = %d AND term_taxonomy_id IN ($in_tt_ids)", $pid ) );
		}

		if ( empty( $assign_taxes ) ) {
			return;
		}

		$values     = [];
		$term_order = 0;

		$term_ids = [];
		foreach ( $assign_taxes as $tt ) {
			do_action( 'wp_all_import_associate_term', $pid, $tt, $tx_name );
			$values[]   = $wpdb->prepare( "(%d, %d, %d)", $pid, $tt, ++ $term_order );
			$term_ids[] = $tt;
		}

		$in_tt_ids = "'" . implode( "', '", $term_ids ) . "'";
		$wpdb->query( "UPDATE {$wpdb->term_taxonomy} SET count = count + 1 WHERE term_taxonomy_id IN ($in_tt_ids)" );

		if ( $values ) {
			if ( false === $wpdb->query( "INSERT INTO {$wpdb->term_relationships} (object_id, term_taxonomy_id, term_order) VALUES " . join( ',', $values ) . " ON DUPLICATE KEY UPDATE term_order = VALUES(term_order)" ) ) {
				$logger and call_user_func( $logger, __( '<b>ERROR</b> Could not insert term relationship into the database', 'wp_all_import_plugin' ) . ': ' . $wpdb->last_error );
			}
		}

		wp_cache_delete( $pid, $tx_name . '_relationships' );
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

		return PMXI_API::upload_image( $pid, $atch_url, $downloadFiles, $logger, true, $fileName, "files", $search_in_gallery, $importData['articleData'], $importData );
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
