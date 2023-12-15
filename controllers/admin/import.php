<?php

class PMAI_Admin_Import extends PMAI_Controller_Admin {

	public function index( string $post_type, array $post ): void {
		$this->data['post_type'] = $post_type;
		$this->data['post']      =& $post;
        
		$meta_box_registry = rwmb_get_registry( 'meta_box' );
		$meta_boxes        = $meta_box_registry->all();
		
		$this->data['meta_boxes'] = $meta_boxes;

		PMXI_Plugin::$session->set( 'meta_boxes', $meta_boxes );
		PMXI_Plugin::$session->save_data();

		$this->render();
	}
}
