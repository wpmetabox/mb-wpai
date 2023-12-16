<?php

namespace MetaBox\WPAI\Fields;

class FieldsetTextHandler extends FieldHandler {
    
    private function get_recursive_value( $xpath ) {
        $value = [];
        $post_index = $this->get_post_index();
        $value[$post_index] = [];

        foreach ( $xpath as $sub_field => $sub_xpath ) {
            if (is_array($sub_xpath)) {
                $sub_field_value = $this->get_recursive_value( $sub_xpath );
                $value[$post_index][$sub_field] = $sub_field_value ?? [];
            } else {
                $sub_field_value = $this->get_value_by_xpath( $sub_xpath );
                $value[$post_index][$sub_field] = $sub_field_value[$post_index] ?? [];
            }
        }

        return $value[$post_index];
    }

    /**
     * @return null|string[]|string[][]
     */
    public function get_value() {
        if ( ! is_array($this->xpath) ) {
            return;
        }

        $value = $this->get_recursive_value( $this->xpath );

        return $value;
    }
}
