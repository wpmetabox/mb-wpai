<?php

namespace MetaBox\WPAI\MetaBoxes;

interface MetaBoxInterface {
	public function view(): void;
	public function parse( array $parsing_data );
	public function saved_post( array $import_data );
}
