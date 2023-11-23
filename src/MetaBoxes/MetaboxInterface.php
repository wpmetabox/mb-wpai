<?php

namespace MetaBox\WPAI\MetaBoxes;

interface MetaboxInterface {
	public function initFields(): void;
	public function view(): void;
	public function parse( array $parsingData );
	public function saved_post( array $importData );
}