<?php
class MBAI_Admin_Import extends MBAI_Controller_Admin {

	/**
	 * @param string $post_type
	 * @param $post
	 */
	public function index( $post_type, $post ) {
		$this->data['post_type'] = $post_type;
		$this->data['post'] =& $post;

		$meta_box_registry = rwmb_get_registry( 'meta_box' );
		$meta_boxes = $meta_box_registry->all();
		
		$this->data['meta_boxes'] = $meta_boxes;

		PMXI_Plugin::$session->set('meta_boxes', $this->data['meta_boxes']);
		PMXI_Plugin::$session->save_data();

		$this->render();
	}
}
