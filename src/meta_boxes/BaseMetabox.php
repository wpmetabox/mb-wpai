<?php

namespace wpai_meta_box_add_on\meta_boxes;

use wpai_meta_box_add_on\fields\Field;
use wpai_meta_box_add_on\fields\FieldFactory;

abstract class BaseMetabox implements MetaboxInterface {

	/**
	 * @var $post Import options
	 */
	public array $post;

	public \RW_Meta_Box $meta_box;

	public $fields = [];

	public $fieldsData = [];

	/**
	 * meta_box constructor.
	 * @param $meta_box
	 */
	public function __construct( $meta_box, $post ) {
		$this->meta_box = $meta_box;

		$this->post = $post;
		$this->initFields();

		add_filter('rwmb_html', array($this, 'filterHtml'), 10, 2);
	}

	/**
	 * @param $html
	 * @param $field
	 * @return string
	 */
	public function filterHtml($html, $field) {
		// Replace name attribute
		$pattern = '/name="([^"]+)"/';
		$replacement = 'name="fields[' . $field['id'] . ']"';
		
		$html = preg_replace($pattern, $replacement, $html);

		// Replace type attribute, use type="text" only
		$pattern = '/type="([^"]+)"/';
		$replacement = 'type="text"';

		$html = preg_replace($pattern, $replacement, $html);
		
		return $html;
	}

	/**
	 *  Create field instances
	 */
	public function initFields() {
		foreach ( $this->getFieldsData() as $fieldData ) {
			$field = FieldFactory::create( $fieldData, $this->getPost() );
			$this->fields[] = $field;
		}
	}

	/**
	 * @return array
	 */
	public function getFieldsData() {
		return $this->fieldsData;
	}

	/**
	 * @return array
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * @return mixed
	 */
	public function getPost() {
		return $this->post;
	}

	/**
	 *  Render meta_box
	 */
	public function view() {
		$this->renderHeader();
		
		// Render whole meta box and fields
		$this->meta_box->show();

		$this->renderFooter();
	}

	protected function renderHeader() {
		$filePath = __DIR__ . '/templates/header.php';
		if ( is_file( $filePath ) ) {
			extract( $this->meta_box->meta_box );
			include $filePath;
		}
	}

	protected function renderFooter() {
		$filePath = __DIR__ . '/templates/footer.php';
		if ( is_file( $filePath ) ) {
			include $filePath;
		}
	}

	public function parse( $parsingData ) {
		foreach ( $this->getFields() as $field ) {
			$xpath = empty( $parsingData['import']->options['fields'][ $field->getFieldKey()] ) ? "" : $parsingData['import']->options['fields'][ $field->getFieldKey()];
			$field->parse( $xpath, $parsingData );
		}
	}

	public function import( $importData, $args = [] ) {
		foreach ( $this->getFields() as $field ) {
			$field->import( $importData, $args );
		}
	}

	/**
	 * @todo: check when this method is called
	 */
	public function saved_post( $importData ) {
		foreach ( $this->getFields() as $field ) {
			$field->saved_post( $importData );
		}
	}
}