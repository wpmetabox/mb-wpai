<?php

namespace MetaBox\WPAI\MetaBoxes;

interface MetaboxInterface {
	public function init_fields( array $fields ): void;
	public function view(): void;
	public function parse( array $parsing_data );
	public function saved_post( array $import_data );
}