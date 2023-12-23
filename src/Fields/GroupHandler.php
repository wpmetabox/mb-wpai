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

	public function get_tree_value( array $xpath, $post_index ) {
		$output = [];
       
		foreach ( $xpath as $field_name => $xpath ) {
			if ( empty( $xpath ) ) {
				continue;
			}
           
			if ( is_array( $xpath ) ) {
				$output[ $field_name ] = $this->get_tree_value( $xpath, $post_index );
			}

			if ( is_string( $xpath ) ) {
                if (is_numeric($field_name)) {
                    $output = $this->get_value_by_xpath( $xpath )[ $post_index ];
                } else {
                    $output[ $field_name ] = $this->get_value_by_xpath( $xpath )[ $post_index ];
				
                    $field                 = $this->get_field_info( $field_name, $this->field );
                    
                    if ( ! $this->returns_array( $field ) ) {
                        $output[ $field_name ] = $output[ $field_name ][0];
                    }
                }
			}
		}

		return $output;
	}

	public function get_value() {
        if ( ! is_array( $this->xpath ) ) {
			return;
		}
        
		$this->xpath = $this->build_xpath_tree( $this->xpath );
        
		$values = [];

		foreach ( $this->xpath['posts'] as $post_index => $xpaths ) {
			$values[ $post_index ] = $this->get_tree_value( $xpaths, $post_index );
		}
        
		$values = $values[ $this->get_post_index()] ?? $values;
        
		if ( $this->returns_array() ) {
			return $values;
		}
        
		return $values[0];
	}

	private function recursive_xpath( $xpath, $count, $post_index ) {
		$output = [];

		// Clone xpath based on the count
		$foreach = $this->get_string_between( $xpath['foreach'] );
        $prepend = $xpath['is_variable'] !== 'no';
        
		for ( $i = 1; $i <= $count; $i++ ) {
			foreach ( $xpath['rows'] as $clone_index => $group ) {
				if ( $clone_index === 'ROWNUMBER' ) {
					continue;
				}

				foreach ( $group as $field_name => $real_xpath ) {
					if ( empty( $real_xpath ) ) {
						continue;
					}

					if ( is_string( $real_xpath ) ) {
                        $binded_xpath = $real_xpath;

                        if ($prepend) {
                            $real_xpath           = $this->get_string_between( $real_xpath );
                            $suffix               = $real_xpath !== '' ? "[{$real_xpath}]" : '';
                            $binded_xpath         = "{{$foreach}[{$i}]" . $suffix . '}';
                        }

						$group[ $field_name ] = $binded_xpath;
					}

					if ( is_array( $real_xpath ) ) {
						if ( isset( $real_xpath['foreach'] ) ) {
							$real_xpath_foreach    = $this->get_string_between( $real_xpath['foreach'] );
							$suffix                = $real_xpath_foreach !== '' ? "[{$real_xpath_foreach}]" : '';
							$binded_xpath          = "{$foreach}[{$i}]" . $suffix;
							$real_xpath['foreach'] = '{' . $binded_xpath . '}';
							$posts_value           = $this->get_value_by_xpath( $real_xpath['foreach'] );
							$rows_count            = count( $posts_value[ $post_index ] );
							$group_value           = $this->recursive_xpath( $real_xpath, $rows_count, $post_index );
							$group[ $field_name ]  = $group_value;
						} else {
                            $binded_xpath = $real_xpath;

                            if ($prepend) {
                                $real_xpath           = $this->get_string_between( $real_xpath[0] );
                                $suffix               = $real_xpath !== '' ? "[{$real_xpath}]" : '';
                                $binded_xpath         = "{{$foreach}[{$i}]" . $suffix . '}';
                            }

							$group[ $field_name ] = $binded_xpath;
						}
					}
				}

				$output[ $i ] = $group;
			}
		}

		return $output;
	}

	private function build_xpath_tree( array $xpath ) {
		if (empty($xpath['foreach'])) {
            for ( $i = 0; $i < 3; $i++ ) {
                $xpath['posts'][ $i ] = $this->recursive_xpath( $xpath, 1, $i );
            }
        } else {
            $post_values = $this->get_value_by_xpath( $xpath['foreach'] );
            
            $xpath['posts'] = [];

            foreach ( $post_values as $post_index => $values ) {
                $xpath['posts'][ $post_index ] = $this->recursive_xpath( $xpath, count( $values ), $post_index );
            }
        }
        
		return $xpath;
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

	private function get_field_info( $field_id, $parent ) {
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