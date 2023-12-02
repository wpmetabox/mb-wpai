<?php

namespace MetaBox\WPAI\Fields;

use MetaBox\WPAI\MetaboxService;
use MetaBox\WPAI\Fields\FieldHandler;

class TextHandler extends FieldHandler {

	public function parse( $xpath, $parsingData, $args = [] ) {
		parent::parse( $xpath, $parsingData, $args );
		
		$values = $this->getByXPath( $xpath );
		
		$this->setOption( 'values', $values );
	}

	public function import( $import_data, array $args = [] ) {
		$canImport = parent::import( $import_data, $args );

		if ( ! $canImport ) {
			return;
		}

		if ($this->is_sub_field()) {
			$key = $this->getFieldKey();
			$root_key = $this->get_root_key();

			$root_value = MetaboxService::get_meta( $this, $this->getPostID(), $root_key );

			if ( ! is_array( $root_value ) ) {
				return;
			}

			foreach ( $this->getFieldValue() as $index => $value ) {
				// Add index before key
				$paths = explode( '.', $key );
				$paths[count($paths) - 1] = $index . '.' . $paths[count($paths) - 1];
				array_shift( $paths );

				$path       = implode( '.', $paths );
				
				\MetaBox\Support\Arr::set( $root_value, $path, $value );
			}

			MetaboxService::set_meta( $this, $this->getPostID(), $root_key, $root_value );

			return;
		}

		MetaboxService::set_meta( $this, $this->getPostID(), $this->getFieldName(), $this->getFieldValue() );
	}
}