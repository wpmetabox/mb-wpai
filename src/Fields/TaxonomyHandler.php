<?php

namespace MetaBox\WPAI\Fields;

use MetaBox\WPAI\Fields\FieldHandler;
use MetaBox\WPAI\MetaBoxService;

class TaxonomyHandler extends FieldHandler {
	public function get_value() {
        $value = parent::get_value();

        ddd($value);
    }

    
    public function saved_post($importData)
    {}
}