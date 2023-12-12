<?php
namespace MetaBox\WPAI\Fields;

use MetaBox\WPAI\MetaBoxService;
use MetaBox\WPAI\MetaBoxes\MetaBoxHandler;

abstract class FieldHandler implements FieldInterface {

	public array $parsingData;

	public array $importData;

	public $parent;

	public $xpath = '';

	public $field;

	public $post;

	public $base_xpath = '';

    public ?MetaBoxHandler $meta_box = null;

	public function __construct(
		array $field,
		array $post,
	    MetaBoxHandler $meta_box = null
	) {
		$this->meta_box = $meta_box;
		$this->field  = $field;
		$this->post   = $post;
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

		$this->parsingData['logger'] and call_user_func( $this->parsingData['logger'], sprintf( __( '- Importing field `%s`', 'mbai' ),  $field['name'] ) );

		// @todo: Handle update permission
		// If update is not allowed
		// if ( ! empty( $this->importData['articleData']['id'] ) && ! \pmai_is_acf_update_allowed( $this->importData['container_name'] . $field['name'], $this->parsingData['import']['options'], $this->parsingData['import']->id ) ) {
		// 	$this->parsingData['logger'] && call_user_func( $this->parsingData['logger'], sprintf( __( '- Field `%s` is skipped attempted to import options', 'mbai' ), $this->getFieldName() ) );

		// 	return false;
		// }

        $value = $this->get_value();

        if ($value) {
		    MetaBoxService::set_meta( $this, $this->get_post_id(), $this->get_id(), $value );
        }

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

	public function get_value_by_xpath( $xpath, $suffix = '' ) {
		// add_filter( 'wp_all_import_multi_glue', function ($glue) {
		// 	return '||';
		// } );

		$values = \XmlImportParser::factory( $this->parsingData['xml'], $this->base_xpath . $suffix, $xpath, $file )->parse();
		
		// add_filter( 'wp_all_import_multi_glue', function ($glue) {
		// 	return ',';
		// } );

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

	/**
	 * @return string
	 */
	public function get_field_key() {
		$prefix = $this->parent ? $this->parent->get_field_key() . '.' : '';

		return $prefix . $this->field['id'];
	}

	public function get_value() {
        if ( ! $this->xpath ) {
            return;
        }

		$values = $this->get_value_by_xpath( $this->xpath );
		$values = $values[ $this->get_post_index()] ?? '';
        
        if ($this->is_clonable() || $this->is_sub_field()) {
            $values = explode( ',', $values );
        }
        
        $values = $this->recursive_trim( $values );
        
        return $values;
	}

    public function get_root_value( $values ) {
        $root_value = MetaBoxService::get_meta( $this, $this->get_post_id(), $this->get_root_key() );

		if ( ! is_array( $root_value ) ) {
			return;
		}

		foreach ( $values as $index => $value ) {
			// Add index before key
			$paths = explode( '.', $this->key );
			$paths[count($paths) - 1] = $index . '.' . $paths[count($paths) - 1];
			array_shift( $paths );

			$path       = implode( '.', $paths );
            if ($this->key === 'actors.subgroup.character') {
                ddd($path);
            }
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
		$key = $this->get_field_key();

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
