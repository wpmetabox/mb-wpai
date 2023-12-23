<?php
namespace MetaBox\WPAI\Fields;

use MetaBox\WPAI\MetaBoxService;
use MetaBox\WPAI\MetaBoxes\MetaBoxHandler;

abstract class FieldHandler {

	public array $parsingData;

	public array $importData;

	public $xpath = '';

	public $field;

	public $post;

	public $base_xpath = '';

	public $parent;

	public array $fields = [];

	public ?MetaBoxHandler $meta_box = null;

	public function __construct(
		array $field,
		array $post,
		MetaBoxHandler $meta_box = null
	) {
		$this->meta_box = $meta_box;
		$this->field    = $field;
		$this->post     = $post;

		$this->init_children_fields();
	}

	private function init_children_fields(): void {
		if ( ! isset( $this->field['fields'] ) ) {
			return;
		}

		foreach ( $this->field['fields'] as $sub_field ) {
			$sub_field['_name'] = $this->field['_name'] . '[' . $sub_field['id'] . ']';
			$field              = FieldFactory::create( $sub_field, $this->post, $this->meta_box );
			$field->parent      = $this;

			$this->fields[] = $field;
		}
	}

	public function view(): void {
		$field_type = 'text';
		$field_name = $this->field['_name'];

		$field_value  = $this->post['fields'][ $this->field['id'] ] ?? '';
		$field        = $this->field;
		$field['std'] = $field['std'] ?? $field_value;

		$handler = $this;

		$view_path = $this->get_view_path( $this->field['type'] );

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
		];

		$field_type = $matches[ $field_type ] ?? 'text';

		return PMAI_ROOT_DIR . '/views/fields/' . $field_type . '.php';
	}

	/**
	 * @param $importData
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function import( $importData, array $args = [] ) {
		$field = $this->field;

		$this->importData = array_merge( $importData, $args );

		$this->parsingData['logger'] and call_user_func( $this->parsingData['logger'], sprintf( __( '- Importing field `%s`', 'mb-wpai' ), $field['id'] ) );

		$field_name_dot = pmai_square_to_dot_notation( $field['_name'] );
		$field_name_dot = str_replace( 'fields.', '', $field_name_dot );

		if ( ! pmai_is_mb_update_allowed( $field_name_dot, $this->parsingData['import']['options'] ) ) {
			$this->parsingData['logger'] && call_user_func( $this->parsingData['logger'], sprintf( __( '- Field `%s` is skipped attempted to import options', 'mb-wpai' ), $this->field['name'] ) );

			return false;
		}

		$value = $this->get_value();

		MetaBoxService::set_meta( $this, $this->get_post_id(), $this->get_id(), $value );

		return true;
	}

	/**
	 * @param $importData
	 */
	public function saved_post( $importData ) {
	}

	public function get_value_by_xpath( $xpath, $suffix = '' ) {
		add_filter( 'wp_all_import_multi_glue', function ($glue) {
			return '||';
		} );

		$values = \XmlImportParser::factory( $this->parsingData['xml'], $this->base_xpath . $suffix, $xpath, $file )->parse();

		add_filter( 'wp_all_import_multi_glue', function ($glue) {
			return ',';
		} );

		if ( $this->returns_array() ) {
			$values = array_map( function ($value) {
				$v = explode( '||', $value );
				return $this->recursive_trim( $v );
			}, $values );
		}

		return $values;
	}

	/**
	 * @return mixed
	 */
	public function getParsingData() {
		return $this->parsingData;
	}

	/**
	 * @return mixed
	 */
	public function get_import_data() {
		return $this->importData;
	}

	/**
	 * Get the index of the post in the import file, starting from 0
	 * 
	 * @return int
	 */
	public function get_post_index(): int {
		return $this->importData['i'];
	}

	public function get_post_id(): int {
		return $this->importData['pid'];
	}

	public function get_original_id(): string {
		return $this->field['id'];
	}

	public function get_id(): string {
		return $this->field['id'];
	}

	public function get_values( $xpaths, $post_index ): array {
		$values = [];

		foreach ( $xpaths as $xpath ) {
			$xpath_values = $this->get_value_by_xpath( $xpath )[ $post_index ];

			if ( is_array( $xpath_values ) ) {
				$values = array_merge( $values, $xpath_values );
			} else {
				$values[] = $xpath_values;
			}
		}

		return $values;
	}

	public function get_value() {
		if ( ! $this->xpath ) {
			return;
		}

		if ( ! is_array( $this->xpath ) ) {
			$this->xpath = [ $this->xpath ];
		}

		$values = $this->get_values( $this->xpath, $this->get_post_index() );

		return $this->returns_array() ? $values : $values[0];
	}

	public function returns_array( $field = null ): bool {
		if ( ! $field ) {
			$field = $this->field;
		}

		if ( $field['clone'] ) {
			return true;
		}

		if ( $field['multiple'] ) {
			return true;
		}

		$multiple_type = [ 'checkbox_list', 'group' ];

		return in_array( $field['type'], $multiple_type ) ?? false;
	}

	/**
	 * Recursively trim value
	 *
	 * @param $value
	 *
	 * @return array|string
	 */
	public function recursive_trim( $value ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $k => $v ) {
				if ( is_string( $v ) ) {
					$value[ $k ] = $this->recursive_trim( $v );
				}
			}

			return $value;
		}

		return is_string( $value ) ? trim( $value ) : $value;
	}

	public function get_import_option( string $option ) {
		$importData = $this->get_import_data();

		return $importData['import']['options'][ $option ] ?? null;
	}

	/**
	 * @return mixed
	 */
	public function getImportType() {
		$importData = $this->get_import_data();

		return $importData['import']['options']['custom_type'];
	}

	/**
	 * @return mixed
	 */
	public function getTaxonomyType() {
		$importData = $this->get_import_data();

		return $importData['import']['options']['taxonomy_type'];
	}

	/**
	 * @return mixed
	 */
	public function getLogger() {
		return $this->parsingData['logger'];
	}
}
