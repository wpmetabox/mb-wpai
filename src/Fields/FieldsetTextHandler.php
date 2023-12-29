<?php

namespace MetaBox\WPAI\Fields;

class FieldsetTextHandler extends FieldHandler {
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
            if (is_array($sub_xpath)) {
                $sub_field_value = $this->get_recursive_value( $sub_xpath );
                $value[$sub_field] = $sub_field_value ?? [];
            } else {
                $sub_field_value = $this->get_value_by_xpath( $sub_xpath );
                $sub_field_value = $this->recursive_explode( $sub_field_value );
                $value[$sub_field] = $sub_field_value[0] ?? [];
            }
        }

        return $value;
    }

    public function get_value() {
		$xpath = $this->field['_wpai']['xpath'];

		if ( ! is_array( $xpath ) ) {
			$xpath = [ $xpath ];
		}

        $value = $this->get_recursive_value( $xpath );
        
		return $this->field['clone'] ? $value : $value[0];
	}
}
