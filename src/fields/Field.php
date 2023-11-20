<?php

namespace wpai_meta_box_add_on\fields;

use wpai_meta_box_add_on\MetaboxService;

abstract class Field implements FieldInterface {

	public string $type;

	public array $data;

	/**
	 * @var mixed
	 */
	public $supportedVersion = false;

	public array $parsingData;

	public array $importData;

	public array $options = [];

	public Field $parent;

	public array $subFields = [];

	public function __construct( Field $field, array $post, $field_name = "", $parent_field = false ) {
		$this->setParent( $parent_field );
		$this->data = array_merge( [
			'field'      => $field,
			'post'       => $post,
			'field_name' => $field_name,
		], $this->getFieldData() );
		$this->initSubFields();
	}

	/**
	 *  Create sub field instances
	 */
	public function initSubFields() {

		// Get sub fields configuration
		$subFieldsData = $this->isLocalFieldStorage() ? $this->getLocalSubFieldsData() : $this->getDBSubFieldsData();

		if ( $subFieldsData ) {
			foreach ( $subFieldsData as $subFieldData ) {
				$field             = $this->initDataAndCreateField( $subFieldData );
				$this->subFields[] = $field;
			}
		}

		// Init sub fields for Flexible Content
		if ( MetaboxService::isACFNewerThan( '5.0.0' ) && $this->getType() == 'flexible_content' && $this->isLocalFieldStorage() ) {
			// get flexible field
			$flexibleField = $this->getData( 'field' );
			// vars
			$flex_fields = acf_get_fields( $flexibleField );
			// loop through layouts, sub fields and swap out the field key with the real field
			foreach ( array_keys( $flexibleField['layouts'] ) as $fi ) {
				// extract layout
				$layout = acf_extract_var( $flexibleField['layouts'], $fi );
				// append sub fields
				if ( ! empty( $flex_fields ) ) {
					$layout['sub_fields'] = [];
					foreach ( array_keys( $flex_fields ) as $fk ) {
						// check if 'parent_layout' is empty
						if ( empty( $flex_fields[ $fk ]['parent_layout'] ) ) {
							// parent_layout did not save for this field, default it to first layout
							$flex_fields[ $fk ]['parent_layout'] = $layout['id'];
						}
						// append sub field to layout,
						if ( $flex_fields[ $fk ]['parent_layout'] == $layout['id'] ) {
							$layout['sub_fields'][] = acf_extract_var( $flex_fields, $fk );
						}
					}
				}
				// append back to layouts
				$this->data['field']['layouts'][ $fi ] = $layout;
			}
		}

	}

	/**
	 * @return array
	 */
	private function getFieldData() {

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
			$data['current_field'] = empty( $post['fields'][ $field['id'] ] ) ? false : $post['fields'][ $field['id'] ];
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

			$data['current_field'] = empty( $data['current_field'][ $field['id'] ] ) ? false : $data['current_field'][ $field['id'] ];

			foreach ( $options as $option ) {
				$data[ 'current_' . $option ] = isset( $data[ 'current_' . $option ][ $field['id'] ] ) ? $data[ 'current_' . $option ][ $field['id'] ] : false;
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
		$this->parsingData = $parsingData;

		$defaults = [
			'field_path'          => '',
			'xpath_suffix'        => '',
			'repeater_count_rows' => 0,
			'inside_repeater'     => false,
		];

		$args = array_merge( $defaults, $args );

		$field = $this->getData( 'field' );

		$isMultipleField = ( isset( $parsingData['import']->options['is_multiple_field_value'][ $field['id'] ] ) ) ? $parsingData['import']->options['is_multiple_field_value'][ $field['id'] ] : false;
		$multipleValue   = ( isset( $parsingData['import']->options['multiple_value'][ $field['id'] ] ) ) ? $parsingData['import']->options['multiple_value'][ $field['id'] ] : false;

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

			$xpath           = empty( $xpath[ $field['id'] ] ) ? false : $xpath[ $field['id'] ];
			$isMultipleField = isset( $isMultipleField[ $field['id'] ] ) ? $isMultipleField[ $field['id'] ] : false;
			$multipleValue   = isset( $multipleValue[ $field['id'] ] ) ? $multipleValue[ $field['id'] ] : false;
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
		$defaults = [
			'container_name'  => '',
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

		// Do not import empty fields.
		if ( in_array( $this->getType(), [ 'file', 'image', 'gallery' ] ) ) {
			$value = ( $this->isNotEmpty() ) ? true : '';
		} else {
			$value = $this->getFieldValue();
		}

		if ( $value === '' && ! in_array( $this->getType(), [
				'group',
				'repeater',
				'clone',
				'flexible_content',
				'button_group',
			] ) ) {
			$is_import_empty_acf_fields = apply_filters( "wp_all_import_is_import_empty_acf_fields", true, $this->parsingData['import']->id );
			if ( empty( $is_import_empty_acf_fields ) ) {
				return false;
			}
		}

		MetaboxService::update_post_meta( $this, $this->getPostID(), $this->getFieldName(), $this->getFieldValue() );

		return true;
	}

	/**
	 * @param $importData
	 */
	public function saved_post( $importData ) {
	}

	/**
	 * @return mixed
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return \wpai_meta_box_add_on\fields\Field|mixed
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * @param \wpai_meta_box_add_on\fields\Field|mixed $parent
	 */
	public function setParent( $parent ) {
		$this->parent = $parent;
	}

	/**
	 * @param $option
	 *
	 * @return mixed|mixed
	 */
	public function getData( $option ) {
		return isset( $this->data[ $option ] ) ? $this->data[ $option ] : false;
	}

	/**
	 * @param $option
	 * @param $value
	 */
	public function setData( $option, $value ) {
		$this->data[ $option ] = $value;
	}

	/**
	 * @param $option
	 *
	 * @return mixed|mixed
	 */
	public function getOption( $option ) {
		return isset( $this->options[ $option ] ) ? $this->options[ $option ] : false;
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
		if ( $xpath != "" ) {
			$file   = false;
			$values = \XmlImportParser::factory( $this->parsingData['xml'], $this->getOption( 'base_xpath' ) . $suffix, $xpath, $file )->parse();
			@unlink( $file );
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
		$fieldName = ( isset( $this->data['field']['name'] ) ? $this->data['field']['name'] : '' );
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
		return $this->data['field']['id'];
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

		if ( isset( $this->options['is_multiple_field'] ) && $this->options['is_multiple_field'] == 'yes' ) {
			$value = array_shift( $values );
		} else {
			$value   = isset( $values[ $this->getPostIndex() ] ) ? $values[ $this->getPostIndex() ] : '';
			$parents = $this->getParents();

			if ( ! empty( $parents ) ) {
				foreach ( $parents as $key => $parent ) {
					if ( $parent['delimiter'] !== false ) {
						$value = explode( $parent['delimiter'], $value );
						$value = isset( $value[ $parent['index'] ] ) ? $value[ $parent['index'] ] : '';
					}
				}
			}
		}

		return $this->trimValue( $value );
	}

	/**
	 * Trim
	 *
	 * @param $value
	 *
	 * @return array|string
	 */
	public function trimValue( $value ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $k => $v ) {
				if ( is_string( $v ) ) {
					$value[ $k ] = $this->trimValue( $v );
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

	/**
	 * @return array
	 */
	public function getSubFields() {
		return $this->subFields;
	}

	/**
	 * @return mixed
	 */
	public function isLocalFieldStorage() {
		return ! is_numeric( $this->getFieldOption( 'id' ) ) || $this->getFieldOption( 'id' ) == 0;
	}

	/**
	 * @return array
	 */
	public function getDBSubFieldsData() {
		$fieldID = $this->getFieldOption( 'id' );
		if ( empty( $fieldID ) ) {
			$fieldData = $this->getDBFieldDataByKey( $this->getFieldKey() );
			if ( ! empty( $fieldData['id'] ) ) {
				$fieldID = $fieldData['id'];
			}
		}
		$field = acf_get_field( $fieldID );
		if ( ! empty( $field['sub_fields'] ) ) {
			return $field['sub_fields'];
		}

		return [];
	}

	/**
	 * @return array
	 */
	public function getLocalSubFieldsData() {

		$subFieldsData = [];

		$subFields = $this->getFieldOption( 'sub_fields' );

		if ( empty( $subFields ) ) {

			if ( MetaboxService::isACFNewerThan( '5.0.0' ) ) {
				$fields = [];
				if ( function_exists( 'acf_local' ) ) {
					$fields = acf_local()->fields;
				}
				if ( empty( $fields ) && function_exists( 'acf_get_local_fields' ) ) {
					$fields = acf_get_local_fields();
				}
				if ( ! empty( $fields ) ) {
					foreach ( $fields as $field ) {
						if ( isset( $field['parent'] ) && $field['parent'] == $this->getFieldKey() ) {
							$subFieldData       = $field;
							$subFieldData['id'] = $subFieldData['id'] = uniqid();
							$subFieldsData[]    = $subFieldData;
						}
					}
				}
			} else {
				global $acf_register_field_group;
				if ( ! empty( $acf_register_field_group ) ) {
					foreach ( $acf_register_field_group as $key => $group ) {
						foreach ( $group['fields'] as $field ) {
							if ( isset( $field['parent'] ) && $field['parent'] == $this->getFieldKey() ) {
								$subFieldData       = $field;
								$subFieldData['id'] = $subFieldData['id'] = uniqid();
								$subFieldsData[]    = $subFieldData;
							}
						}
					}
				}
			}
		} else {
			foreach ( $subFields as $field ) {
				$subFieldData       = $field;
				$subFieldData['id'] = $subFieldData['id'] = uniqid();
				$subFieldsData[]    = $subFieldData;
			}
		}

		return $subFieldsData;
	}

	/**
	 * @param $fieldKey
	 *
	 * @return \wpai_meta_box_add_on\fields\Field|mixed
	 */
	public function getFieldByKey( $fieldKey ) {

		// Get field configuration
		$fieldData = $this->isLocalFieldStorage() ? $this->getLocalFieldDataByKey( $fieldKey ) : $this->getDBFieldDataByKey( $fieldKey );

		return $fieldData ? $this->initDataAndCreateField( $fieldData ) : false;
	}

	/**
	 * @param $fieldKey
	 *
	 * @return mixed
	 */
	protected function getLocalFieldDataByKey( $fieldKey ) {
		$fieldData = false;
		$fields    = [];
		if ( function_exists( 'acf_local' ) ) {
			$fields = acf_local()->fields;
		}
		if ( empty( $fields ) && function_exists( 'acf_get_local_fields' ) ) {
			$fields = acf_get_local_fields();
		}
		if ( ! empty( $fields ) ) {
			foreach ( $fields as $sub_field ) {
				if ( $sub_field['id'] == $fieldKey ) {
					$fieldData       = $sub_field;
					$fieldData['id'] = $fieldData['id'] = uniqid();
					break;
				}
			}
		}

		return $fieldData;
	}

	/**
	 * @param $fieldKey
	 *
	 * @return array|mixed|mixed
	 */
	protected function getDBFieldDataByKey( $fieldKey ) {
		return acf_get_field( $fieldKey );
	}

	/**
	 * @param $subFieldData
	 *
	 * @return Field|mixed
	 */
	public function initDataAndCreateField( $subFieldData ) {

		$fieldData = $subFieldData;

		if ( is_object( $subFieldData ) ) {
			$fieldData          = empty( $subFieldData->post_content ) ? [] : unserialize( $subFieldData->post_content );
			$fieldData['id']    = $fieldData['id'] = $subFieldData->ID;
			$fieldData['label'] = $subFieldData->post_title;
			$fieldData['id']    = $subFieldData->post_name;
			$fieldData['name']  = $subFieldData->post_excerpt;
		}

		// Do not include same field as child to avoid `Maximum function nesting level` exception.
		$parent = $this->getParent();
		if ( $parent ) {
			do {
				if ( $parent->getFieldKey() == $fieldData['id'] ) {
					return false;
				}
				$parent = $parent->getParent();
			} while ( $parent );
		}
		if ( $this->getFieldKey() == $fieldData['id'] ) {
			return false;
		}

		// Create sub field instance
		return FieldFactory::create( $fieldData, $this->getData( 'post' ), $this->getFieldName(), $this );
	}

	/**
	 * @return mixed
	 */
	public function isNotEmpty() {
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

		return $values[ $this->getPostIndex() ] ?? '';
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
								'index'     => $parent->getRowIndex(),
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
			'type'              => $field['type'],
			'post_type'         => isset( $field['post_type'] ) ? $field['post_type'] : false,
			'name'              => $field['name'],
			'multiple'          => isset( $field['multiple'] ) ? $field['multiple'] : false,
			'values'            => $this->getOption( 'values' ),
			'is_multiple'       => $this->getOption( 'is_multiple' ),
			'is_variable'       => $this->getOption( 'is_variable' ),
			'is_ignore_empties' => $this->getOption( 'is_ignore_empties' ),
			'xpath'             => $this->getOption( 'xpath' ),
			'id'                => empty( $field['id'] ) ? $field['id'] : $field['id'],
		];
	}
}
