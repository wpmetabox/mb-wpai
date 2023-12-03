<?php
namespace MetaBox\WPAI\Fields;

use MetaBox\WPAI\MetaBoxService;

abstract class FieldHandler implements FieldInterface {

	public array $parsingData;

	public array $importData;

	public $parent;

	public $xpath = '';

	public $field;

	public $post;

	public $base_xpath = '';

	public $key = '';

	public function __construct(
		array $field,
		array $post,
		string $key,
	    $parent = null
	) {
		$this->parent = $parent;
		$this->field = $field;
		$this->key = $key;
		$this->post = $post;
	}

	/**
	 * @param $xpath
	 * @param $parsingData
	 * @param array $args
	 *
	 * @return void
	 */
	public function parse( $xpath, $parsingData, $args = [] ) {
		$this->xpath = $xpath;

		if (empty($xpath)) {
			return;
		}

		$this->parsingData = $parsingData;
		
		$this->base_xpath = $parsingData['xpath_prefix'] . $parsingData['import']->xpath . $args['xpath_suffix'];
	}

	/**
	 * @param $importData
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function import( $importData, array $args = [] ) {
		if (empty($this->xpath)) {
			return false;
		}

		$field = $this->field;

		$this->importData = array_merge( $importData, $args );

		$this->parsingData['logger'] and call_user_func( $this->parsingData['logger'], sprintf( __( '- Importing field `%s`', 'mbai' ),  $field['name'] ) );

		// @todo: Handle update permission
		// If update is not allowed
		// if ( ! empty( $this->importData['articleData']['id'] ) && ! \pmai_is_acf_update_allowed( $this->importData['container_name'] . $field['name'], $this->parsingData['import']->options, $this->parsingData['import']->id ) ) {
		// 	$this->parsingData['logger'] && call_user_func( $this->parsingData['logger'], sprintf( __( '- Field `%s` is skipped attempted to import options', 'mbai' ), $this->getFieldName() ) );

		// 	return false;
		// }

		MetaBoxService::set_meta( $this, $this->getPostID(), $this->get_id(), $this->get_value() );

		return true;
	}

	public function get_field_path($parent = null): string {
		$path = $parent ? $parent->field['id'] . '.' . $this->field['id'] : $this->field['id'];

		return $path;
	}

	/**
	 * @param $importData
	 */
	public function saved_post( $importData ) {
	}

	public function get_value_by_xpath( string $xpath, $suffix = '' ) {
		add_filter( 'wp_all_import_multi_glue', function ($glue) {
			return '||';
		} );

		$values = \XmlImportParser::factory( $this->parsingData['xml'], $this->base_xpath . $suffix, $xpath, $file )->parse();
		
		add_filter( 'wp_all_import_multi_glue', function ($glue) {
			return ',';
		} );

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
	public function getImportData() {
		return $this->importData;
	}

	/**
	 * @return mixed
	 */
	public function getPostIndex() {
		return $this->importData['i'];
	}

	/**
	 * @return mixed
	 */
	public function getPostID() {
		return $this->importData['pid'];
	}

	public function get_original_id(): string {
		return $this->field['id'];
	}

	public function get_id(): string {
		return $this->parent ? $this->get_root_key() : $this->field['id'];
	}

	/**
	 * @return string
	 */
	public function getFieldKey() {
		$prefix = $this->parent ? $this->parent->getFieldKey() . '.' : '';

		return $prefix . $this->field['id'];
	}

	/**
	 * @return mixed
	 */
	public function get_value() {
		$values = $this->get_value_by_xpath( $this->xpath );

		$values = $values[ $this->getPostIndex()] ?? '';

		$field = $this->field;

		if ( $field['clone'] || $this->parent ) {
			$values = explode( '||', $values );
		}

		$values = $this->recursive_trim( $values );

        return $this->parent ? $this->get_root_value( $values ) : $values;
	}

    public function get_root_value( $values ) {
        $root_value = MetaBoxService::get_meta( $this, $this->getPostID(), $this->get_root_key() );

		if ( ! is_array( $root_value ) ) {
			return;
		}

		foreach ( $values as $index => $value ) {
			// Add index before key
			$paths = explode( '.', $this->key );
			$paths[count($paths) - 1] = $index . '.' . $paths[count($paths) - 1];
			array_shift( $paths );

			$path       = implode( '.', $paths );
			
			\MetaBox\Support\Arr::set( $root_value, $path, $value );
		}

		return $root_value;
    }

	public function is_clonable(): bool {
		return $this->field['clone'] ?? false;
	}

	public function is_sub_field(): bool {
		return $this->parent ? true : false;
	}

	public function get_root_key(): string {
		$key = $this->getFieldKey();

		// Split key into parts separated by '.' and get the first part
		$key = explode( '.', $key );
		$key = $key[0];
		
		return $key;
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

	public function getImportOption( string $option ) {
		$importData = $this->getImportData();

		return $importData['import']->options[ $option ] ?? null;
	}

	/**
	 * @return mixed
	 */
	public function getImportType() {
		$importData = $this->getImportData();

		return $importData['import']->options['custom_type'];
	}

	/**
	 * @return mixed
	 */
	public function getTaxonomyType() {
		$importData = $this->getImportData();

		return $importData['import']->options['taxonomy_type'];
	}

	/**
	 * @return mixed
	 */
	public function getLogger() {
		return $this->parsingData['logger'];
	}
}
