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

		MetaboxService::set_meta( $this, $this->getPostID(), $this->getFieldName(), $this->getFieldValue() );
	}
}