<?php

namespace MetaBox\WPAI\Fields;

use MetaBox\WPAI\Fields\FieldHandler;
use MetaBox\WPAI\MetaBoxService;

class FileHandler extends FieldHandler {
	public function get_value() {
        if (!$this->xpath) {
            return;
        }
        
        $value = parent::get_value();

        $parsingData = $this->parsingData;

        $attachment_id = MetaBoxService::import_file(
            $value, 
            $this->get_post_id(), 
            $parsingData['logger'], 
            $parsingData['import']['options']['is_fast_mode'], 
            true, 
            true, 
            $this->importData
        );

        return $attachment_id;
    }
}