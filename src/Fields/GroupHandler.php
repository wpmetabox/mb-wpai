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
				$value = $field_handler->get_value();
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

	public function get_value() {
		$xpaths = $this->get_xpaths();
		
		// Normalize xpaths
		if ( isset( $xpaths['foreach'] ) ) {
			$xpaths = $this->build_xpaths_tree( $xpaths );
		}

		$output = $this->get_tree_value( $xpaths );
	
		return $this->field['clone'] ? $output : $output[0] ?? [];
	}

	private function build_xpaths_tree( $xpath, $parent = [] ) {
		$tree = [];

		if (empty($xpath['foreach'])) {
			return $tree;
		}

		$rows       = $this->get_value_by_xpath( $xpath['foreach'] );
		$rows_count = count( $rows );

		for ( $i = 0; $i < $rows_count; $i++ ) {
			foreach ( $xpath as $field_id => $value ) {
				if ( $field_id === 'foreach' ) {
					continue;
				}
			
				if ( is_string( $value ) ) {
					$value = [ $value ];
				}

				if ( isset( $value['foreach'] ) ) {
					$value['foreach'] = $this->expand_xpath( $value['foreach'], $xpath['foreach'], $i+1, true );
					$value                   = $this->build_xpaths_tree( $value, $xpath );

					$tree[ $i ][ $field_id ] = $value;
					continue;
				}

				foreach ( $value as $clone_index => $v_xpath ) {
					if (is_string($v_xpath)) {
						$value[ $clone_index ] = $this->expand_xpath( $v_xpath, $xpath['foreach'], $i+1 );
					}

					if (is_array($v_xpath)) {
						// key-value field
						foreach ( $v_xpath as $key => $v ) {
							$value[ $clone_index ][ $key ] = $this->expand_xpath( $v, $xpath['foreach'], $i+1 );
						}
					}
				}

				$tree[ $i ][ $field_id ] = $value;
			}
		}

		return $tree;
	}

	private function normalize_xpath( array $array, $parent = null ) {
		$output = [];

		foreach ( $array as $index => $group ) {
			if ( empty( $group['foreach'] ) ) {
				$group['foreach'] = $parent['foreach'] ?? '';
			}

			foreach ( $group as $field_id => $value ) {
				if ( $field_id === 'foreach' ) {
					continue;
				}

				if ( is_string( $value ) ) {
					$value = [ $value ];
				}

				// At this point, we still don't know if the field is a group or not
				// So we need to check the field type
				// If it's a group, we need to convert the value to array
				$field = $this->get_field_info( $field_id );
				if ( empty( $field ) ) {
					continue;
				}


				if ( $field['type'] === 'group' ) {
					$value = $this->normalize_xpath( $value, $group );
				} else {
					foreach ( $value as $clone_index => $xpath ) {
						$value[ $clone_index ] = $this->expand_xpath( $xpath, $group );
					}
				}

				$group[ $field_id ] = $value;
			}

			$output[ $index ] = $group;
		}

		return $output;
	}

	function expand_xpath( $string, $xpath = '', $append_index = '', $xpath_if_empty = false ): ?string {
		$xpath  = trim( $xpath );
		$string = trim( $string );

		$xpath = str_replace( '{', '', $xpath );
		$xpath = str_replace( '}', '', $xpath );

		$string = str_replace( '{', '', $string );
		$string = str_replace( '}', '', $string );
		$string = str_replace( '.', './', $string );

		if ( $append_index !== '' ) {
			$xpath .= "[{$append_index}]";
		}

		if ( empty( $string ) ) {
			if ( $xpath_if_empty ) {
				return '{' . $xpath . '}';
			}

			return null;
		}

		$output = '{';

		if ( ! empty( $xpath ) ) {
			$output .= $xpath . '/';
		}

		$output .= $string . '}';
		$output = str_replace('/}', '}', $output);

		return $output;
	}

	private function sanitize( $groups, $parent = [] ) {
		$output = [];

		foreach ( $groups as $index => $group ) {
			foreach ( $group as $field_id => $value ) {
				if ( ! is_numeric( $field_id ) ) {
					$field_info = $this->get_field_info( $field_id, $parent );

					if ( ! $field_info ) {
						continue;
					}

					if ( $field_info['type'] === 'group' ) {
						$value = $this->sanitize( $value, $field_info );
					}
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

	public function saved_post( $importData ) {
		foreach ( $this->refs as $field ) {
			$field->saved_post( $importData );
		}
	}
}