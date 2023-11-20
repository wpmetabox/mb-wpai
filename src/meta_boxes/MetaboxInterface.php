<?php

namespace wpai_meta_box_add_on\meta_boxes;

/**
 * Interface FieldInterface
 * @package wpai_meta_box_add_on\meta_boxes
 */
interface MetaboxInterface {

	public function initFields(): void;

	public function view(): void;

	/**
	 * @param $parsingData
	 *
	 * @return mixed
	 */
	public function parse( $parsingData );

	/**
	 * @param $importData
	 *
	 * @return mixed
	 */
	public function saved_post( $importData );

}