<?php

namespace MetaBox\WPAI\Fields;

class FieldsetTextHandler extends FieldHandler {

    /**
     * @return null|string[]|string[][]
     */
    public function get_value() {
        if ( ! is_array($this->xpath) ) {
            return;
        }

        $value = [];
        $post_index = $this->get_post_index();
        $value[$post_index] = [];

        foreach ( $this->xpath as $field => $xpath ) {
            $sub_field_value = $this->get_value_by_xpath( $xpath );
            $value[$post_index][$field] = $sub_field_value[$post_index] ?? '';
        }

        return $value[$post_index];
    }
}
