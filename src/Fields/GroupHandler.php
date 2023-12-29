<?php
namespace MetaBox\WPAI\Fields;

class GroupHandler extends FieldHandler {
	/**
	 * @var {string: FieldHandler}[]
	 */
	protected $refs = [];

	private function get_tree_value( $xpath, $parent = [] ) {
		$output = [];

		foreach ( $xpath as $group ) {
			if ( empty( $group['foreach'] ) ) {
				$foreach = [ 0 ];
			} else {
				$foreach = $this->get_value_by_xpath( $group['foreach'] );
			}

			for ( $i = 0; $i < count( $foreach ); $i++ ) {
				foreach ( $group as $field_id => $bindings ) {

					if ( $field_id === 'foreach' ) {
						continue;
					}

					$field         = $this->get_field_info( $field_id );
					$field_handler = $this->init_sub_field( $field, $this->field, $bindings );

					if ( ! $field ) {
						continue;
					}

					if ( $field['type'] === 'group' ) {
						$value = $this->get_tree_value( $bindings );
					} else {
						$value = $field_handler->get_value();
					}

					if ( empty( $value ) ) {
						continue;
					}

					if ( is_string( $bindings ) ) {
						$bindings = [ $bindings ];
					}

					// move the value to the right segment
					foreach ( $bindings as $cindex => $cxpath ) {
						if ( is_string($cxpath) && ! str_contains( $cxpath, '[.' ) ) {
							$output[0][ $field_id ] = $value;
						} else {
							if ( is_array( $value ) ) {
								for ( $i = 0; $i < count( $value ); $i++ ) {
									$output[ $i ][ $field_id ] = $value[ $i ];
								}
							}
						}
					}
				}
			}
		}

		return $output;
	}

	private function init_sub_field( $field, $parent, $bindings ) {
		$field['_wpai']['xpath'] = $bindings;

		// Create field instance to handle the import
		$field                                    = FieldFactory::create( $field, $this->post, $this->meta_box );
		$field->parsingData                       = $this->parsingData;
		$field->base_xpath                        = $this->parsingData['xpath_prefix'] . $this->parsingData['import']['xpath'];
		$field->importData                        = $this->importData;
		
		$this->refs[ $field->field['reference'] ] = $field;
		return $field;
	}

	public function get_value() {
		$xpath = $this->field['_wpai']['xpath'];

		$xpath  = $this->normalize_xpath( $xpath, $xpath );
		$output = $this->get_tree_value( $xpath );
		
		return $this->field['clone'] ? $output : $output[0];
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

	private function expand_xpath( $xpath, $parent = [] ) {
		$value         = $this->get_string_between( $xpath );
		$foreach_value = $this->get_string_between( $parent['foreach'] ?? '' );

		if ( $value === '' ) {
			$value = $foreach_value;
		} else if ( $foreach_value !== '' ) {
			$value = "{$foreach_value}{$value}";
		}

		$value = str_replace( '.', '/', $value );
		$value = trim( $value, '/' );
		$value = str_replace( '[/]', '[.]', $value );

		$value = "{{$value}}";

		return $value;
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