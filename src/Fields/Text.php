<?php

namespace MetaBox\WPAI\Fields;

use MetaBox\WPAI\MetaboxService;
use MetaBox\WPAI\Fields\Field;

class Text extends Field {

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

		MetaboxService::update_post_meta( $this, $this->getPostID(), $this->getFieldName(), $this->getFieldValue() );
	}
}