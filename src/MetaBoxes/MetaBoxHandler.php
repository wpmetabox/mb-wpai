<?php

namespace MetaBox\WPAI\MetaBoxes;

use MetaBox\WPAI\Fields\FieldFactory;

class MetaBoxHandler implements MetaBoxInterface {
	/**
	 * @var $post Import options
	 */
	public array $post;

	public \RW_Meta_Box $meta_box;

	public $refs = [];

	public $parsingData = [];

	/**
	 * meta_box constructor.
	 *
	 * @param $meta_box
	 */
	public function __construct( \RW_Meta_Box $meta_box, array $post ) {
		$meta_box->meta_box['fields'] = $this->add_refs( $meta_box->meta_box['fields'] );

		$this->meta_box = $meta_box;
		$this->post     = $post;
	}

	public function add_refs( $fields, $parent = [] ) {
		foreach ( $fields as $index => $field ) {
			$field['reference'] = isset( $parent['reference'] ) ? $parent['reference'] . '.' . $field['id'] : $field['id'];

			if ( isset( $field['fields'] ) ) {
				$field['fields'] = $this->add_refs( $field['fields'], $field );
			}

			$fields[ $index ] = $field;
		}

		return $fields;
	}

	public function get_post(): array {
		return $this->post;
	}

	public function view(): void {
		$this->render_block( 'header' );

		foreach ( $this->meta_box->meta_box['fields'] as $field ) {
			$this->render_field( $field );
		}

		$this->render_block( 'footer' );
	}

	public function render_field( array $field ): void {
		if ( ! isset( $field['_wpai'] ) ) {
			$wpai_attr = $this->post['fields'][ $field['id'] ] ?? [ 
				'xpath' => null,
				'options' => [],
				'reference' => $field['id'],
			];

			$field['_wpai'] = $wpai_attr;
		}

		$handler   = $this;
		$view_path = $this->get_view_path( $field['type'] );

		if ( ! file_exists( $view_path ) || ! $view_path ) {
			return;
		}

		include $view_path;
	}

	private function get_view_path( string $field_type ): ?string {
		$matches = [ 
			'taxonomy' => 'taxonomy',
			'group' => 'group',
			'fieldset_text' => 'fieldset_text',
			'key_value' => 'key_value',
			'text_list' => 'text_list',
			'background' => 'background',
		];

		$field_type = $matches[ $field_type ] ?? 'text';

		return PMAI_ROOT_DIR . '/views/fields/' . $field_type . '.php';
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
		$this->parsingData = $parsingData;
	}

	public function import( $import_data, $args = [] ) {
		$binding_fields = $this->parsingData['import']->options['fields'] ?? [];

		foreach ( $binding_fields as $field_id => $value ) {
			$xpaths    = $value['xpath'] ?? [ '' ];
			$reference = $value['reference'];
			$options   = $value['options'] ?? [];

			if ( ! empty( $xpaths ) && ! empty( $reference ) ) {
				// Find field by reference id (dot notation)
				$field = $this->find_field( $this->meta_box->meta_box['fields'], $reference );
				// Create field instance to handle the import
				if ( $field ) {
					$field['_wpai']     = $value;
					$field              = FieldFactory::create( $field, $this->post, $this );
					$field->parsingData = $this->parsingData;
					$field->base_xpath  = $this->parsingData['xpath_prefix'] . $this->parsingData['import']['xpath'];
					$field->import( $import_data, $args );
					$this->refs[ $reference ] = $field;
				}
			}
		}
	}

	private function find_field( $fields, string $reference ) {
		foreach ( $fields as $field ) {
			if ( $field['reference'] === $reference ) {
				return $field;
			}

			if ( ! empty( $field['fields'] ) ) {
				$field = $this->find_field( $field['fields'], $reference );
				if ( $field ) {
					return $field;
				}
			}
		}

		return null;
	}

	public function saved_post( $import_data ) {
		$binding_fields = $this->parsingData['import']->options['fields'] ?? [];

		foreach ( $binding_fields as $field_id => $value ) {
			$xpaths    = $value['xpath'] ?? [ '' ];
			$reference = $value['reference'];

			if ( ! empty( $xpaths ) && ! empty( $reference ) ) {
				$field = $this->refs[ $reference ] ?? null;

				if ( ! $field ) {
					continue;
				}

				$field->saved_post( $import_data );
			}
		}
	}
}
