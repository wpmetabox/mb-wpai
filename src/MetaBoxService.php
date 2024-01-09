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

					return [
						'ID'    => $attch->ID,
						'name'  => basename( $atch_url ),
						'path'  => $atch_url,
						'url'   => get_attached_file( $attch->ID ),
						'title' => basename( $atch_url ),
					];
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
}
