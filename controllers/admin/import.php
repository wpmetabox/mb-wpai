<?php
class PMAI_Admin_Import extends PMAI_Controller_Admin {

	public function index(string $post_type, array $post ): void {
		$this->data['post_type'] = $post_type;
		$this->data['post'] =& $post;

		$meta_box_registry = rwmb_get_registry( 'meta_box' );
		$meta_boxes = $meta_box_registry->all();
		$meta_boxes = $this->formatMetaBoxes($meta_boxes);

		$this->data['meta_boxes'] = $meta_boxes;

		PMXI_Plugin::$session->set('meta_boxes', $this->data['meta_boxes']);
		PMXI_Plugin::$session->save_data();

		$this->render();
	}

	/**
	 * Because the import UI only needs to display either Text or Textarea.
	 * We will format the fields to only display those two types.
	 */
	private function formatMetaBoxes(array $meta_boxes): array
	{
		return array_map(function ($meta_box) {
			$meta_box->meta_box['fields'] = array_map(function ($field) {
				$multiline = in_array($field['type'], ['wysiwyg', 'textarea']);
				$field['type'] = $multiline ? 'textarea' : 'text';
				$field['autocomplete'] = false;
				$field['datalist'] = false;
				$field['readonly'] = false;

				return $field;
			}, $meta_box->meta_box['fields']);

			return $meta_box;
		}, $meta_boxes);
	}
}
