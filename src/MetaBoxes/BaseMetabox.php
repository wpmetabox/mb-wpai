<?php

namespace MetaBox\WPAI\MetaBoxes;

use MetaBox\WPAI\Fields\FieldFactory;

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
	 *
	 * @param $meta_box
	 */
	public function __construct( \RW_Meta_Box $meta_box, array $post ) {
		$this->meta_box = $meta_box;
		$this->post = $post;
		$this->initFields();
	}

	// public function filterHtml( $html, $field ): string {
	// 	// Wrap matched name attribute inside fields array, use name="fields[...]", not name="..."
	// 	$pattern    = '/name="([^"]+)"/';
	// 	$replacement = 'name="fields[$1]"';

	// 	// Replace only if fields is not already in the name attribute
	// 	if ( false === strpos( $html, 'name="fields[' ) ) {
	// 		$html = preg_replace( $pattern, $replacement, $html );
	// 	}

	// 	// Replace type attribute, use type="text" only
	// 	$pattern     = '/type="([^"]+)"/';
	// 	$replacement = 'type="text"';

	// 	$html = preg_replace( $pattern, $replacement, $html );

	// 	return $html;
	// }

	public function initFields(): void {
		foreach ( $this->getFieldsData() as $fieldData ) {
			$field          = FieldFactory::create( $fieldData, $this->getPost() );
			$this->fields[] = $field;
		}
	}

	public function getFieldsData(): array {
		return $this->fieldsData;
	}

	public function getFields(): array {
		return $this->fields;
	}

	public function getPost(): array {
		return $this->post;
	}

	public function view(): void {
		$this->render_block( 'header' );
		
		foreach ( $this->getFields() as $field ) {
			$field->view();
		}

		$this->render_block( 'footer' );
	}

	protected function render_block( $block = 'header' ): void {
		$file_path = PMAI_ROOT_DIR . '/views/mb/' . $block . '.php';

		if ( ! file_exists( $file_path ) ) {
			return;
		}

		extract( $this->meta_box->meta_box );
		include $file_path;
	}

	public function parse( $parsingData ) {
		
		foreach ( $this->getFields() as $field ) {
			$xpath = $parsingData['import']->options['fields'][ $field->getFieldKey() ] ?? '';
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