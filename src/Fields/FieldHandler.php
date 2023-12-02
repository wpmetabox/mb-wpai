<?php
namespace MetaBox\WPAI\Fields;

use MetaBox\WPAI\MetaboxService;

abstract class FieldHandler implements FieldInterface {
	public array $data;

	public array $parsingData;

	public array $importData;

	public array $options = [];

	public $parent;

	public $xpath = '';

	public function __construct(
		array $field,
		array $post,
	    $parent = null
	) {
		$this->setParent( $parent );

		$this->data = array_merge( [ 
			'field' => $field,
			'post' => $post,
		], $this->getFieldData() );
	}

	/**
	 * @return array|null
	 */
	private function getFieldData() {
		if (empty($this->xpath)) {
			return [];
		}

		$data = [];

		$field = $this->getData( 'field' );
		$post  = $this->getData( 'post' );

		// set field default values
		$reset = [ 'multiple', 'class', 'id' ];
		foreach ( $reset as $key ) {
			if ( empty( $field[ $key ] ) ) {
				$field[ $key ] = false;
			}
		}

		if ( array_key_exists( 'id', $field ) ) {
			$data['current_field'] = $post['fields'][ $field['id'] ] ?? false;
		} else {
			$data['current_field'] = false;
		}

		$options = [ 'is_multiple_field_value', 'multiple_value' ];
		foreach ( $options as $option ) {
			$data[ 'current_' . $option ] = isset( $field['id'] ) && isset( $post[ $option ][ $field['id'] ] ) ? $post[ $option ][ $field['id'] ] : false;
		}

		// If parent field exists, parse field name
		if ( "" != $this->getData( 'field_name' ) ) {

			$field_keys = str_replace( [ '[', ']' ], [ '' ], str_replace( '][', ':', $this->getData( 'field_name' ) ) );

			$data['current_field'] = false;
			foreach ( explode( ":", $field_keys ) as $n => $key ) {
				if ( ! empty( $post['fields'][ $key ] ) ) {
					$data['current_field'] = $post['fields'][ $key ];
				} elseif ( isset( $data['current_field'][ $key ] ) ) {
					$data['current_field'] = $data['current_field'][ $key ];
				}

				foreach ( $options as $option ) {
					if ( ! empty( $post[ $option ][ $key ] ) ) {
						$data[ 'current_' . $option ] = $post[ $option ][ $key ];
					} elseif ( ! empty( $data[ 'current_' . $option ][ $key ] ) ) {
						$data[ 'current_' . $option ] = $data[ 'current_' . $option ][ $key ];
					}
				}
			}

			$data['current_field'] = $data['current_field'][ $field['id'] ] ?? false;

			foreach ( $options as $option ) {
				$data[ 'current_' . $option ] = $data[ 'current_' . $option ][ $field['id'] ] ?? false;
			}
		}

		return $data;
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

		$defaults = [ 
			'field_path' => '',
			'xpath_suffix' => '',
			'repeater_count_rows' => 0,
			'inside_repeater' => false,
		];

		$args = array_merge( $defaults, $args );

		$field = $this->getData( 'field' );

		$isMultipleField = $parsingData['import']->options['is_multiple_field_value'][ $field['id'] ] ?? false;
		$multipleValue   = $parsingData['import']->options['multiple_value'][ $field['id'] ] ?? false;

		if ( "" != $args['field_path'] ) {

			$fieldKeys               = preg_replace( '%[\[\]]%', '', str_replace( '][', ':', $args['field_path'] ) );
			$is_multiple_field_value = $parsingData['import']->options['is_multiple_field_value'];
			$is_multiple_value       = $parsingData['import']->options['multiple_value'];

			foreach ( explode( ":", $fieldKeys ) as $n => $key ) {
				$xpath = ( ! $n ) ? $parsingData['import']->options['fields'][ $key ] : $xpath[ $key ];

				if ( ! $n && isset( $is_multiple_field_value[ $key ] ) ) {
					$isMultipleField = $is_multiple_field_value[ $key ];
				}
				if ( isset( $isMultipleField[ $key ] ) ) {
					$isMultipleField = $isMultipleField[ $key ];
				}

				if ( ! $n && isset( $is_multiple_value[ $key ] ) ) {
					$multipleValue = $is_multiple_value[ $key ];
				}
				if ( isset( $multipleValue[ $key ] ) ) {
					$multipleValue = $multipleValue[ $key ];
				}
			}

			$xpath           = $xpath[ $field['id'] ] ?? false;
			$isMultipleField = $isMultipleField[ $field['id'] ] ?? false;
			$multipleValue   = $multipleValue[ $field['id'] ] ?? false;
		}

		$this->setOption( 'base_xpath', $parsingData['xpath_prefix'] . $parsingData['import']->xpath . $args['xpath_suffix'] );
		$this->setOption( 'xpath', $xpath );
		$this->setOption( 'is_multiple_field', $isMultipleField );
		$this->setOption( 'multiple_value', $multipleValue );
		$this->setOption( 'count', ( $args['repeater_count_rows'] ) ? $args['repeater_count_rows'] : $parsingData['count'] );
		$this->setOption( 'values', array_fill( 0, $this->getOption( 'count' ), "" ) );
		$this->setOption( 'field_path', $args['field_path'] );
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

		$defaults = [ 
			'container_name' => '',
			'parent_repeater' => '',
		];

		$field = $this->getData( 'field' );

		$args = array_merge( $defaults, $args );

		$this->importData = array_merge( $importData, $args );

		$this->parsingData['logger'] and call_user_func( $this->parsingData['logger'], sprintf( __( '- Importing field `%s`', 'mbai' ), $this->importData['container_name'] . $field['name'] ) );

		$parsedData = $this->getParsedData();

		// If update is not allowed
		if ( ! empty( $this->importData['articleData']['id'] ) && ! \pmai_is_acf_update_allowed( $this->importData['container_name'] . $field['name'], $this->parsingData['import']->options, $this->parsingData['import']->id ) ) {
			$this->parsingData['logger'] && call_user_func( $this->parsingData['logger'], sprintf( __( '- Field `%s` is skipped attempted to import options', 'mbai' ), $this->getFieldName() ) );

			return false;
		}

		// MetaboxService::set_meta( $this, $this->getPostID(), $this->getFieldName(), $this->getFieldValue() );

		return true;
	}

	/**
	 * @param $importData
	 */
	public function saved_post( $importData ) {
	}

	public function getType(): string {
		$slug = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2', get_class( $this ) ) );
		$type = str_replace( 'MetaBox\WPAI\\Fields\\', '', $slug );

		return $type;
	}

	/**
	 * @return \MetaBox\WPAI\Fields\Field|mixed
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * @param \MetaBox\WPAI\Fields\FieldHandler $parent
	 */
	public function setParent( $parent ) {
		$this->parent = $parent;
	}

	public function getData( $option ) {
		return $this->data[ $option ] ?? false;
	}

	public function setData( $option, $value ) {
		$this->data[ $option ] = $value;
	}

	public function getOption( $option ) {
		return $this->options[ $option ] ?? false;
	}

	/**
	 * @param $option
	 * @param $value
	 */
	public function setOption( $option, $value ) {
		$this->options[ $option ] = $value;
	}

	/**
	 * @param $xpath
	 * @param string $suffix
	 *
	 * @return array
	 * @throws \XmlImportException
	 */
	public function getByXPath( $xpath, $suffix = '' ) {
		$values = array_fill( 0, $this->getOption( 'count' ), "" );

		add_filter( 'wp_all_import_multi_glue', function ($glue) {
			return '||';
		} );

		if ( $xpath != "" ) {
			$file   = false;
			$values = \XmlImportParser::factory( $this->parsingData['xml'], $this->getOption( 'base_xpath' ) . $suffix, $xpath, $file )->parse();

			@unlink( $file );
		}

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

	/**
	 * @return string
	 */
	public function getFieldName() {
		$fieldName = $this->data['field']['name'] ?? '';
		// if ( empty( $fieldName ) ) {
		// 	if ( function_exists( 'acf_get_field' ) ) {
		// 		$field = acf_get_field( $this->data['field']['id'] );
		// 	} else {
		// 		$label = sanitize_title( $this->data['field']['label'] );
		// 		$fieldName = str_replace( '-', '_', $label );
		// 	}

		// 	if ( ! empty( $field ) ) {
		// 		$fieldName = $this->data['field']['name'] = $field['name'];
		// 	}
		// }
		return ! isset( $this->importData['container_name'] ) ? $fieldName : $this->importData['container_name'] . $fieldName;
	}

	/**
	 * @param $fieldName
	 */
	public function setFieldInputName( $fieldName ) {
		$this->data['field_name'] = $fieldName;
		$this->data               = array_merge( $this->data, $this->getFieldData() );
	}

	/**
	 * @return string
	 */
	public function getFieldKey() {
		$prefix = $this->parent ? $this->parent->getFieldKey() . '.' : '';

		return $prefix . $this->data['field']['id'];
	}

	/**
	 * @return string
	 */
	public function getFieldLabel() {
		return $this->data['field']['label'];
	}

	/**
	 * @return mixed
	 */
	public function getFieldValue() {
		$values = $this->options['values'];

		$value = $values[ $this->getPostIndex()] ?? '';

		$field = $this->getData( 'field' );

		if ( $field['clone'] || $this->parent ) {
			$value = explode( '||', $value );
		}

		return $this->recursiveTrim( $value );
	}

	public function is_clonable(): bool {
		return $this->data['field']['clone'] ?? false;
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
	public function recursiveTrim( $value ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $k => $v ) {
				if ( is_string( $v ) ) {
					$value[ $k ] = $this->recursiveTrim( $v );
				}
			}

			return $value;
		}

		return is_string( $value ) ? trim( $value ) : $value;
	}


	public function getFieldOption( string $option ) {
		return $this->data['field'][ $option ] ?? null;
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

	public function isNotEmpty(): bool {
		return (bool) $this->getCountValues();
	}

	/**
	 * @return int
	 */
	public function getCountValues( $parentIndex = false ) {
		$parents = $this->getParents();
		if ( $parentIndex !== false && isset( $parents[ $parentIndex ] ) ) {
			$parents = [ $parents[ $parentIndex ] ];
		}
		$value = $this->getOriginalFieldValueAsString();
		if ( ! empty( $parents ) && ! empty( $value ) && ! is_array( $value ) ) {
			$parentIndex = false;
			foreach ( $parents as $key => $parent ) {
				if ( $parentIndex !== false ) {
					$value = $value[ $parentIndex ];
				}
				if ( $parent['delimiter'] !== false ) {
					$value = explode( $parent['delimiter'], $value );
					if ( is_array( $value ) ) {
						$value = array_filter( $value );
					}
					$parentIndex = $parent['index'];
				}
			}
		}

		return is_array( $value ) ? count( $value ) : ! empty( $value );
	}

	/**
	 * @return mixed
	 */
	public function getOriginalFieldValueAsString() {
		$values = $this->options['values'];

		return $values[ $this->getPostIndex()] ?? '';
	}

	/**
	 * @return array
	 */
	protected function getParents() {
		$field   = $this;
		$parents = [];
		do {
			$parent = $field->getParent();
			if ( $parent ) {
				switch ( $parent->type ) {
					case 'repeater':
						if ( $parent->getMode() == 'fixed' || $parent->getMode() == 'csv' && $parent->getDelimiter() ) {
							$parents[] = [ 
								'delimiter' => $parent->getDelimiter(),
								'index' => $parent->getRowIndex(),
							];
						}
						break;
					default:
						break;
				}
				$field = $parent;
			}
		} while ( $parent );

		return array_reverse( $parents );
	}

	/**
	 * @return array
	 */
	public function getParsedData() {
		$field = $this->getData( 'field' );

		return [ 
			'type' => $field['type'],
			'post_type' => $field['post_type'] ?? false,
			'name' => $field['name'],
			'multiple' => $field['multiple'] ?? false,
			'values' => $this->getOption( 'values' ),
			'is_multiple' => $this->getOption( 'is_multiple' ),
			'is_variable' => $this->getOption( 'is_variable' ),
			'is_ignore_empties' => $this->getOption( 'is_ignore_empties' ),
			'xpath' => $this->getOption( 'xpath' ),
			'id' => $field['id'],
		];
	}
}
