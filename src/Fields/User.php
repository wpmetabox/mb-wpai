<?php

namespace MetaBox\WPAI\Fields;

use MetaBox\WPAI\MetaboxService;
use MetaBox\WPAI\Fields\Field;

class User extends Field {

	public function parse( $xpath, $parsingData, $args = array() ) {
		parent::parse( $xpath, $parsingData, $args );
		$values = $this->getByXPath( $xpath );
		$this->setOption( 'values', $values );
	}

	public function import( $importData, $args = array() ) {
		$isUpdated = parent::import( $importData, $args );

		if ( ! $isUpdated ) {
			return false;
		}

		MetaboxService::update_post_meta( $this, $this->getPostID(), $this->getFieldName(), $this->getFieldValue() );
	}

	public function getFieldValue(): ?int {
		$by = [ 'login', 'slug', 'email', 'id' ];

		foreach ( $by as $column ) {
			$user = get_user_by( $column, parent::getFieldValue() );

			if ( ! empty( $user ) ) {
				return $user->ID;
			}
		}

		return null;
	}
}