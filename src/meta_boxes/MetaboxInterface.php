<?php

namespace wpai_meta_box_add_on\meta_boxes;

interface MetaboxInterface {
	public function initFields(): void;
	public function view(): void;
	public function parse( array $parsingData );
	public function saved_post( array $importData );
}