<?php

namespace MetaBox\WPAI\Fields;

interface FieldInterface {
	public function import( $importData, array $args = [] );
	public function saved_post( $importData );
}