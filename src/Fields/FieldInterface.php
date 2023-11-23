<?php

namespace MetaBox\WPAI\Fields;

interface FieldInterface {
	public function parse( $xpath, $parsingData, array $args = [] );
	public function import( $importData, array $args = [] );
	public function saved_post( $importData );
	public function isNotEmpty();
	public function getOriginalFieldValueAsString();
}