<?php

namespace MetaBox\WPAI\MetaBoxes;

use MetaBox\WPAI\Fields\FieldFactory;

class MetaBoxHandler implements MetaBoxInterface {
	/**
	 * @var $post Import options
	 */
	public array $post;

	public \RW_Meta_Box $meta_box;

	public $fields = [];

	public $parsingData = [];

	/**
	 * meta_box constructor.
	 *
	 * @param $meta_box
	 */
	public function __construct( \RW_Meta_Box $meta_box, array $post ) {
		$this->meta_box = $meta_box;
		$this->post     = $post;
        $this->init_fields();
	}

    private function init_fields(): void {
        foreach ( $this->meta_box->meta_box['fields'] as $field ) {
            // we create _name field to be able to use it in view
            $field['_name'] = 'fields[' . $field['id'] . ']';
            $this->fields[ $field['id'] ] = FieldFactory::create( $field, $this->get_post(), $this );
        }
    }

	private function add_binding_to_fields( array $fields, array $bindings ): array {
		$merged_fields = [];

		foreach ( $fields as $index => $field ) {
			if ( isset( $bindings[ $index ] ) ) {
                $field->field['binding'] = $bindings[ $index ];
			}

			if ( isset( $field->field['fields'] ) ) {
				$field->field['fields'] = $this->add_binding_to_fields( $field->field['fields'], $bindings );
			}

            $merged_fields[ $index ] = $field;
		}

		return $merged_fields;
	}

	public function get_post(): array {
		return $this->post;
	}

	public function view(): void {
		$this->render_block( 'header' );

		foreach ( $this->fields as $field ) {
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
		// Convert to associated array to make it easier to work with
		$parsingData = json_decode( json_encode( $parsingData ), true );
		$bindings    = $parsingData['import']['options']['fields'] ?? [];

        $this->fields = $this->add_binding_to_fields( $this->fields, $bindings ); 
		$this->parsingData                  = $parsingData;
		// file_put_contents(__DIR__ . '/parsingData.json', json_encode($parsingData, JSON_PRETTY_PRINT));
	}

	public function import( $import_data, $args = [] ) {
		foreach ( $this->fields as $field ) {
			$field->parsingData = $this->parsingData;
			$field->base_xpath  = $this->parsingData['xpath_prefix'] . $this->parsingData['import']['xpath'];
			$field->xpath       = $field->field['binding'];
			$field->importData  = $import_data;

			$field->import( $import_data, $args );
		}
	}

	public function saved_post( $import_data ) {
		foreach ($this->fields as $field ) {
			$field->parsingData = $this->parsingData;
			$field->saved_post( $import_data );
		}
	}
}
