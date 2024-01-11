<?php
namespace MetaBox\WPAI\Fields;

class GroupHandler extends FieldHandler {
	/**
	 * @var {string: FieldHandler}[]
	 */
	protected $refs = [];

	private function get_tree_value( $xpath, $parent = [] ) {
		$output = [];

		foreach ( $xpath as $clone_index => $group ) {
			foreach ( $group as $field_id => $value ) {

				$field = $this->get_field_info( $field_id, $parent );

				if ( ! $field ) {
					continue;
				}

				$field_handler = $this->init_sub_field( $field, $parent, $value );
				$value         = $field_handler->get_value();

				$output[ $clone_index ][ $field_id ] = $value;
			}
		}

		return $output;
	}

	private function init_sub_field( $field, $parent, $bindings ) {
		$field['_wpai']['xpath'] = $bindings;

		// Create field instance to handle the import
		$field              = FieldFactory::create( $field, $this->post, $this->meta_box );
		$field->parsingData = $this->parsingData;
		$field->base_xpath  = $this->parsingData['xpath_prefix'] . $this->parsingData['import']['xpath'];
		$field->importData  = $this->importData;

		$this->refs[ $field->field['reference'] ] = $field;

		return $field;
	}

	private function is_normalized( $xpath ) {
		return is_array( $xpath ) && isset( $xpath[0] ) && ! array_key_exists( 'foreach', $xpath[0] );
	}

	public function get_value() {
		$xpaths = $this->get_xpaths();

		if ( empty( $xpaths ) ) {
			return null;
		}

		if ( ! $this->is_normalized( $xpaths ) ) {
			$output = [];

			foreach ( $xpaths as $xpath ) {
				$xpath_tree = $this->build_xpaths_tree( $xpath );
				$output     = array_merge( $output, $xpath_tree );
			}

			$output = $this->get_tree_value( $output );
		} else {
			$output = $this->get_tree_value( $xpaths );
		}

		return $this->field['clone'] ? $output : $output[0] ?? [];
	}

	private function build_xpaths_tree( $xpath, $parent = [] ) {
		$tree = [];

		$rows_count = 1;

		if ( ! empty( $xpath['foreach'] ) ) {
			$rows       = $this->get_value_by_xpath( $xpath['foreach'] );
			$rows_count = count( $rows );
		}

		for ( $i = 0; $i < $rows_count; $i++ ) {
			foreach ( $xpath as $field_id => $value ) {
				if ( $field_id === 'foreach' || empty( $value ) ) {
					continue;
				}

				if ( is_string( $value ) ) {
					$value = [ $value ];
				}

				if ( isset( $value['foreach'] ) ) {
					$value['foreach'] = $this->expand_xpath( $value['foreach'], $xpath['foreach'], $i + 1, true );
					$value            = $this->build_xpaths_tree( $value, $xpath );

					$tree[ $i ][ $field_id ] = $value;
					continue;
				}
				$output = [];

				foreach ( $value as $clone_index => $v_xpath ) {
					if ( is_string( $v_xpath ) ) {
						$output[ $clone_index ] = $this->expand_xpath( $v_xpath, $xpath['foreach'] ?? '', $i + 1 );
					}

					if ( is_array( $v_xpath ) ) {
						// nested group
						if ( isset( $v_xpath['foreach'] ) ) {
							$v_xpath['foreach'] = $this->expand_xpath( $v_xpath['foreach'], $xpath['foreach'], $i + 1, true );
							$output             = array_merge( $output, $this->build_xpaths_tree( $v_xpath ) );
							continue;
						}

						// key-value field
						foreach ( $v_xpath as $key => $v ) {
							$output = $this->expand_xpath( $v, $xpath['foreach'] ?? '', $i + 1 );
						}
					}
				}

				$tree[ $i ][ $field_id ] = $output;
			}
		}

		return $tree;
	}

	private function expand_xpath( $string, $xpath = '', $append_index = '', $xpath_if_empty = false ): ?string {
		$xpath_value = $this->get_string_between( $xpath );

		if ( $append_index !== '' ) {
			$xpath_value .= "[{$append_index}]";
		}

		if ( empty( $string ) ) {
			if ( $xpath_if_empty ) {
				return '{' . $xpath_value . '}';
			}

			return null;
		}

		$output = str_replace( '{.', '{' . $xpath_value . '/', $string );
		$output = str_replace( '/}', '}', $output );

		return $output;
	}

	private function get_string_between( $string ) {
		$start = '{';
		$end   = '}';

		$string = ' ' . $string;
		$ini    = strpos( $string, $start );

		if ( $ini == 0 ) {
			return '';
		}

		$ini += strlen( $start );
		$len  = strpos( $string, $end, $ini ) - $ini;
		$str  = substr( $string, $ini, $len );

		if ( $str === '.' ) {
			return '';
		}

		return $str;
	}

	private function get_field_info( $field_id, $parent = false ) {
		if ( ! $parent ) {
			$parent = $this->field;
		}

		foreach ( $parent['fields'] as $field ) {
			if ( $field['id'] === $field_id ) {
				return $field;
			}

			if ( isset( $field['fields'] ) ) {
				$field_info = $this->get_field_info( $field_id, $field );

				if ( $field_info ) {
					return $field_info;
				}
			}
		}

		return false;
	}

	public function saved_post( $importData ) {
		foreach ( $this->refs as $field ) {
			$field->saved_post( $importData );
		}
	}
}
