<?php

namespace MetaBox\WPAI\Fields;
use MetaBox\WPAI\MetaBoxService;

class TextListHandler extends FieldHandler {
	private function recursive_explode( $array ) {
		$result = [];
		foreach ( $array as $key => $value ) {
			if ( is_array( $value ) ) {
				$result[ $key ] = $this->recursive_explode( $value );
			} else {
				if ( strpos( $value, '||' ) !== false ) {
					$value = explode( '||', $value );
				}

				$result[ $key ] = $value;
			}
		}

		return $result;
	}

	private function get_recursive_value( $xpath ) {
		$value = [];

		foreach ( $xpath as $sub_field => $sub_xpath ) {
			if ( is_array( $sub_xpath ) ) {
				$sub_field_value   = $this->get_recursive_value( $sub_xpath );
				$value[ $sub_field ] = $sub_field_value ?? [];
			} else {
				$sub_field_value   = $this->get_value_by_xpath( $sub_xpath );
				$sub_field_value   = $this->recursive_explode( $sub_field_value );
				$value[ $sub_field ] = $sub_field_value[0] ?? [];
			}
		}

		return $value;
	}

    public function import($importData, $args = []) {
        $this->importData = $importData;

        $value = $this->get_value();
        
        if (!$this->field['clone']) {
            foreach ( $value as $k => $v) {
                MetaBoxService::set_meta( $this, $this->get_post_id(), $k, $v );
            }
            return true;
        } else {
            $output = [];
            foreach ($value as $k => $v) {
                $output[] = [$k, $v];
            }

            MetaBoxService::set_meta( $this, $this->get_post_id(), $this->field['id'], $output );
        }
    }

	public function get_value() {
		$xpath = $this->field['_wpai']['xpath'];

		if ( ! is_array( $xpath ) ) {
			$xpath = [ $xpath ];
		}

		$output = [];

		foreach ( $xpath as $clone_index => $xpath ) {
			if ( is_string( $xpath ) ) {
				$output[ $clone_index ] = $this->get_value_by_xpath( $xpath );
				continue;
			}

			foreach ( $xpath as $index => $value ) {
				$output[ $clone_index ][ $index ] = $this->get_value_by_xpath( $value );
			}
		}
        
        $output = $this->combine_kv($output);
        
		return $output;
	}

    private function combine_kv(array $array) {
        // Make $array[0] as keys and $array[1] as values
        $keys = $array[0];
        $values = $array[1];

        $output = [];

        foreach ($keys as $index => $key) {
            $output[$key] = $values[$index];
        }

        return $output;
    }
}
