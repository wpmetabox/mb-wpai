<?php

namespace MetaBox\WPAI\MetaBoxes;

interface MetaBoxInterface {
	public function init_field_handlers( array $fields ): void;
	public function view(): void;
	public function parse( array $parsing_data );
	public function saved_post( array $import_data );
}