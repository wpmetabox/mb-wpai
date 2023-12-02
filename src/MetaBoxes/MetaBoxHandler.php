<?php

namespace MetaBox\WPAI\MetaBoxes;

use MetaBox\WPAI\Fields\FieldFactory;

class MetaBoxHandler implements MetaboxInterface {
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

	public function init_field_handlers(array $fields = []): void {
		if ( empty( $fields ) ) {
			$fields = $this->meta_box->meta_box['fields'];
		}

		foreach ( $fields as $mb_field ) {
			$field          = FieldFactory::create( $mb_field, $this->get_post() );
			$this->field_handlers[] = $field;

			if ( ! empty( $mb_field['fields'] ) ) {
				$this->init_field_handlers( $mb_field['fields'] );
			}
		}
	}

	public function get_post(): array {
		return $this->post;
	}

	public function view(): void {
		$this->render_block( 'header' );
		
		foreach ( $this->field_handlers as $field ) {
			$field->view();
		}

		$this->render_block( 'footer' );
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
		foreach ( $this->field_handlers as $field ) {
			$xpath = $parsingData['import']->options['fields'][ $field->getFieldKey() ] ?? '';
			$field->parse( $xpath, $parsingData );
		}
	}

	public function import( $import_data, $args = [] ) {
		foreach ( $this->field_handlers as $field ) {
			$field->import( $import_data, $args );
		}
	}

	/**
	 * @todo: check when this method is called
	 */
	public function saved_post( $import_data ) {
		foreach ( $this->field_handlers as $field ) {
			$field->saved_post( $import_data );
		}
	}
}