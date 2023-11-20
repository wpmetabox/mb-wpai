<?php

namespace wpai_meta_box_add_on\fields\mb;

use wpai_meta_box_add_on\MetaboxService;
use wpai_meta_box_add_on\fields\Field;

class Text extends Field {
	/**
	 *
	 * Parse field data
	 *
	 * @param $xpath
	 * @param $parsingData
	 * @param array $args
	 */
	public function parse( $xpath, $parsingData, $args = array() ) {
		parent::parse( $xpath, $parsingData, $args );
		$values = $this->getByXPath( $xpath );
		$this->setOption( 'values', $values );
	}

	/**
	 * @param $importData
	 * @param array $args
	 * @return mixed
	 */
	public function import( $importData, array $args = [] ) {
		$isUpdated = parent::import( $importData, $args );

		if ( ! $isUpdated ) {
			return false;
		}

		MetaboxService::update_post_meta( $this, $this->getPostID(), $this->getFieldName(), $this->getFieldValue() );
	}
}