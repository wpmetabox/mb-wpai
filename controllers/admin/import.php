<?php

class PMAI_Admin_Import extends PMAI_Controller_Admin {

	public function index( string $post_type, array $post ): void {
		$this->data['post_type'] = $post_type;
		$this->data['post']      =& $post;

		$meta_box_registry = rwmb_get_registry( 'meta_box' );
		$meta_boxes        = $meta_box_registry->all();

		// Remove all closure functions for serialization
		$meta_boxes = $this->remove_closure( $meta_boxes );

		$this->data['meta_boxes'] = $meta_boxes;

		PMXI_Plugin::$session->set( 'meta_boxes', $meta_boxes );
		PMXI_Plugin::$session->save_data();

		$this->render();
	}

	private function remove_closure( array $meta_boxes ): array {
		foreach ( $meta_boxes as $index => $meta_box ) {
			foreach ( $meta_box->meta_box['fields'] as $field_index => $field ) {
				foreach ( $field as $key => $value ) {
					if ( is_callable( $value ) && ! is_string( $value ) ) {
						unset( $meta_box->meta_box['fields'][ $field_index ][ $key ] );
					}
				}
			}

			$meta_boxes[ $index ] = $meta_box;
		}

		return $meta_boxes;
	}
}
