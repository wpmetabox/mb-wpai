<?php
namespace MetaBox\WPAI\Fields;

use MetaBox\WPAI\MetaBoxService;

class FileAdvancedHandler extends FieldHandler {
	public function get_value() {
		if ( ! $this->xpath ) {
			return;
		}

		$value = parent::get_value();
        
        if ( ! $value ) {
            return;
        }

        if ( ! is_array( $value ) ) {
            $value = [ $value ];
        }

        $attachments = [];
        $parsingData = $this->parsingData;

        foreach ( $value as $file ) {
            $attachment = MetaBoxService::import_file(
                $file,
                $this->get_post_id(),
                $parsingData['logger'],
                $parsingData['import']['options']['is_fast_mode'],
                true,
                true,
                $this->importData
            );

            if ( is_array($attachment )) {
                $attachments[] = $attachment['ID'];
            }
        }
	
		return $this->returns_array() ? $attachments : reset( $attachments );
	}
}
