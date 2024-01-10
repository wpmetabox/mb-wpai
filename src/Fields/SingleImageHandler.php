<?php

namespace MetaBox\WPAI\Fields;

class SingleImageHandler extends FileAdvancedHandler {
    public function get_value() {
        $value = parent::get_value();
        
        return $this->field['clone'] ? $value : reset( $value );
    }
}
