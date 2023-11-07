<?php

namespace wpai_meta_box_add_on\meta_boxes;

/**
 * Interface FieldInterface
 * @package wpai_meta_box_add_on\meta_boxes
 */
interface MetaboxInterface {

	/**
	 * @return mixed
	 */
	public function initFields();

	/**
	 * @return mixed
	 */
	public function view();

	/**
	 * @param $parsingData
	 * @return mixed
	 */
	public function parse( $parsingData );

	/**
	 * @param $importData
	 * @return mixed
	 */
	public function saved_post( $importData );

}