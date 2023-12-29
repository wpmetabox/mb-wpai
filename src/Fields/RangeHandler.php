<?php

namespace MetaBox\WPAI\Fields;

class RangeHandler extends FieldHandler {
    public function get_value() {
        $value = parent::get_value();
        
        foreach ($value as $clone_index => $val) {
            if (is_numeric($val)) {
                $value[$clone_index] = floatval($val);
                continue;
            }

            if (is_array($val)) {
                foreach ($val as $key => $v) {
                    $value[$key] = floatval($v);
                }
            }
        }
        
        return $this->field['clone'] ? $value : $value[0];
    }
}
