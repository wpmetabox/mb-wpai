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
		$this->post = $post;
	}

    private function merge_fields_with_bindings($fields, $bindings) {
        $merged_fields = [];
        
        foreach ($fields as $index => $field) {
            if (isset($bindings[$field['id']])) {
                $merged_fields[$index] = array_merge($field, [
                    'binding' => $bindings[$field['id']],
                ]);
            }
    
            if (isset($field['fields'])) {
                $merged_fields[$index]['fields'] = $this->merge_fields_with_bindings($field['fields'], $bindings[$field['id']]);
            }
        }
    
        return $merged_fields;
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
        $bindings = $parsingData['import']['options']['fields'] ?? [];

        $this->meta_box->meta_box['fields'] = $this->merge_fields_with_bindings($this->meta_box->meta_box['fields'], $bindings);
        $this->parsingData = $parsingData;

        // file_put_contents(__DIR__ . '/parsingData.json', json_encode($parsingData, JSON_PRETTY_PRINT));
	}

	public function import( $import_data, $args = [] ) {
		foreach ( $this->meta_box->meta_box['fields'] as $mb_field ) {
            $field = FieldFactory::create( $mb_field, $this->get_post(), $this );
            $field->parsingData = $this->parsingData;
            $field->base_xpath = $this->parsingData['xpath_prefix'] . $this->parsingData['import']['xpath'];
            $field->xpath = $mb_field['binding'];
            $field->importData = $import_data;
            $field->import($import_data, $args);
        }
	}

	public function saved_post( $import_data ) {
        foreach ( $this->meta_box->meta_box['fields'] as $field ) {
            $field = FieldFactory::create( $field, $this->get_post(), $this );
            $field->parsingData = $this->parsingData;
            $field->saved_post($import_data);
        }
	}
}
