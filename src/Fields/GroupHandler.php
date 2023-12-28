<?php
namespace MetaBox\WPAI\Fields;

class GroupHandler extends FieldHandler {
	public $mode = 'fixed';
	public $delimeter = ',';
	public $is_ignore_empties = false;
	public $foreach = '';

	public function parse( $xpath, $parsingData, $args = [] ) {
		$xpath = json_decode( $xpath, true );

		$this->xpath       = $xpath;
		$this->parsingData = $parsingData;
		$this->base_xpath  = $parsingData['xpath_prefix'] . $parsingData['import']->xpath;
	}

	private function get_tree_value( $xpath, $parent = [] ) {
		if ( ! is_array( $xpath ) ) {
			$xpath = [ $xpath ];
		}

		$output = [];

		foreach ( $xpath as $group ) {
			$foreach = $this->get_value_by_xpath( $group['foreach'] );

			for ( $i = 0; $i < count( $foreach ); $i++ ) {
				foreach ( $group as $field_id => $bindings ) {
					if ( $field_id === 'foreach' ) {
						continue;
					}

					$field = $this->get_field_info( $field_id );
					$field_handler = $this->init_sub_field( $field, $this->field, $bindings );
					
					if ( ! $field ) {
						continue;
					}

					if ( $field['type'] === 'group' ) {
						$value = $this->get_tree_value( $bindings );
					} else {
						$value = $field_handler->get_value();
						$value = $value[0];
					}

					$output[ $i ][ $field_id ] = $value;
					// If contains *, then we assign all values to the field, otherwise we assign each value to the field
					// if ( str_contains( $xpath, '*' ) ) {
					// 	$output[ $i ][ $field_id ] = $value;
					// } else {
					// 	for ( $i = 0; $i < count( $value ); $i++ ) {
					// 		$output[ $i ][ $field_id ][] = $value[ $i ];
					// 	}
					// }
				}
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

		return $field;
	}

	public function get_value() {	
		$xpath  = $this->field['_wpai']['xpath'];
		$xpath  = $this->normalize_xpath( $xpath );

		$output = $this->get_tree_value( $xpath );

		$output = $this->sanitize( $output, $this->field );

		return $output;
	}

	private function normalize_xpath( array $xpath, $parent = null ) {
		foreach ( $xpath as $clone_index => $group ) {
			foreach ( $group as $field_id => $field_xpaths ) {
				if ( $field_id === 'foreach' ) {
					continue;
				}

				foreach ( $field_xpaths as $field_clone_index => $value ) {
					if ( is_array( $value ) ) {
						$value['foreach'] = $this->expand_xpath( $value['foreach'], $xpath[ $clone_index ] );
						$value = $this->normalize_xpath( [$value], $xpath[ $clone_index ] );
						$value = $value[0];
					} else if ( is_string( $value ) ) {
						$value = $this->expand_xpath( $value, $xpath[ $clone_index ] );
					}
					$xpath[ $clone_index ][ $field_id ][ $field_clone_index ] = $value;
				}
			}
		}

		return $xpath;
	}

	private function expand_xpath( $xpath, $parent = [] ) {
		$value         = $this->get_string_between( $xpath );
		$foreach_value = $this->get_string_between( $parent['foreach'] );

		if ( $value === '' ) {
			$value = $foreach_value;
		} else if ( $foreach_value !== '' ) {
			$value = "{$foreach_value}{$value}";
		}

		$value = str_replace( '.', '/', $value );
		$value = trim( $value,'/');
		$value = "{{$value}}";

		return $value;
	}

	private function sanitize( $groups, $parent = [] ) {
		$output = [];

		foreach ( $groups as $index => $group ) {
			foreach ( $group as $field_id => $value ) {
				$field_info = $this->get_field_info( $field_id, $parent );

				if ( ! $field_info ) {
					continue;
				}

				if ( $field_info['type'] === 'group' ) {
					$value = $this->sanitize( $value, $field_info );
				}

				if ( ! $field_info['clone'] ) {
					$value = $value[0];
				}

				$output[ $index ][ $field_id ] = $value;
			}

			// If the group is not cloneable, then we only need the first value
			if ( ! $parent['clone'] ) {
				break;
			}
		}

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
		$len = strpos( $string, $end, $ini ) - $ini;
		$str = substr( $string, $ini, $len );

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
}