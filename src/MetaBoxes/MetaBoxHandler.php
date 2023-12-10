<?php

namespace MetaBox\WPAI\MetaBoxes;

use MetaBox\WPAI\Fields\FieldFactory;

class MetaBoxHandler implements MetaBoxInterface {
	/**
	 * @var $post Import options
	 */
	public array $post;

	public \RW_Meta_Box $meta_box;

	public $field_handlers = [];

	/**
	 * meta_box constructor.
	 *
	 * @param $meta_box
	 */
	public function __construct( \RW_Meta_Box $meta_box, array $post ) {
		$this->meta_box = $meta_box;
		$this->post = $post;

		$this->init_field_handlers();
	}

	public function init_field_handlers(array $fields = [], $parent = null): void {
		if ( empty( $fields ) ) {
			$fields = $this->meta_box->meta_box['fields'];
		}

		foreach ( $fields as $mb_field ) {
			$field_key = $parent ? $parent->key . '.' . $mb_field['id'] : $mb_field['id'];
			$field          = FieldFactory::create( $mb_field, $this->get_post(), $field_key, $parent );
			$this->field_handlers[] = $field;

			if ( ! empty( $mb_field['fields'] ) ) {
				$this->init_field_handlers( $mb_field['fields'], $field );
			}
		}
	}

	public function get_post(): array {
		return $this->post;
	}

	public function view(): void {
		$this->render_block( 'header' );
		
		$this->render_fields($this->meta_box->meta_box['fields']);

		$this->render_block( 'footer' );
	}

	public function render_fields( $fields = [], $parent = null ): void {
		foreach ( $fields as $field ) {
            $field['_name'] = $parent ? $parent['_name'] . '[' . $field['id'] . ']' : 'fields[' . $field['id'] . ']';
            
			$this->render_field( $field, $parent );

			if ( ! empty( $field['fields'] ) ) {
				$this->render_fields( $field['fields'], $field );
			}
		}
	}

	public function render_field( $field, $parent = null ): void {
		$field_type = 'text';
        $field_name = $field['_name'];
        $field_value = $this->post['fields'][$field['id']] ?? '';
        $field_type = $field['type'] === 'taxonomy' ? 'taxonomy' : 'text';
		$file_path  = PMAI_ROOT_DIR . '/views/fields/' . $field_type . '.php';
        
		if ( ! file_exists( $file_path ) ) {
			return;
		}

		include $file_path;
	}

	protected function render_block( $block = 'header' ): void {
		$file_path = PMAI_ROOT_DIR . '/views/mb/' . $block . '.php';

		if ( ! file_exists( $file_path ) ) {
			return;
		}

		extract( $this->meta_box->meta_box );
		include $file_path;
	}

	public function parse( $parsingData ) {
        // Convert to associated array to make it easier to work with
        $parsingData = json_decode(json_encode($parsingData), true);
        
		foreach ( $this->field_handlers as $field ) {
			$xpath = $parsingData['import']['options']['fields'][ $field->key ] ?? '';
			$field->parse( $xpath, $parsingData );
		}
	}

	public function import( $import_data, $args = [] ) {
		foreach ( $this->field_handlers as $field ) {
			$field->import( $import_data, $args );
		}
	}

	public function saved_post( $import_data ) {
		foreach ( $this->field_handlers as $field ) {
			$field->saved_post( $import_data );
		}
	}
}
