<?php

namespace wpai_meta_box_add_on\fields\mb;

use wpai_meta_box_add_on\MetaboxService;
use wpai_meta_box_add_on\fields\Field;

class Text extends Field {

	public function parse( $xpath, $parsingData, $args = [] ) {
		parent::parse( $xpath, $parsingData, $args );
		$values = $this->getByXPath( $xpath );
		
		$this->setOption( 'values', $values );
	}

	public function import( $importData, array $args = [] ) {
		$canImport = parent::import( $importData, $args );

		if ( ! $canImport ) {
			return false;
		}

		MetaboxService::update_post_meta( $this, $this->getPostID(), $this->getFieldName(), $this->getFieldValue() );
	}
}