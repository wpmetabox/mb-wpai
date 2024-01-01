<?php
namespace MetaBox\WPAI\Fields;

use MetaBox\WPAI\MetaBoxService;

class FileAdvancedHandler extends FieldHandler {
	public function get_value() {
		$value = parent::get_value();
		
		if ( ! $value ) {
			return;
		}

		$attachments = [];
		$parsingData = $this->parsingData;
		
		foreach ( $value as $clone_index => $files ) {
			if ( empty( $files ) ) {
				continue;
			}

			if ( is_string( $files ) ) {
				$files = [ $files ];
			}

			foreach ( $files as $file ) {
				if ( empty( $file ) ) {
					continue;
				}

				$attachment = MetaBoxService::import_file(
					$file,
					$this->get_post_id(),
					$parsingData['logger'],
					$parsingData['import']->options['is_fast_mode'],
					true,
					true,
					$this->importData
				);

				if ( is_array( $attachment ) ) {
					$attachments[ $clone_index ][] = $attachment['ID'];
				}
			}
		}

		return $this->field['clone'] ? $attachments : reset( $attachments );
	}
}
