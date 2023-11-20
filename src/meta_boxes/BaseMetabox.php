<?php

namespace wpai_meta_box_add_on\meta_boxes;

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
	 *
	 * @param $meta_box
	 */
	public function __construct( $meta_box, $post ) {
		$this->meta_box = $meta_box;

		$this->post = $post;
		$this->initFields();

		add_filter( 'rwmb_html', [ $this, 'filterHtml' ], 10, 2 );
	}

	public function filterHtml( $html, $field ): string {
		// Replace name attribute
		$pattern     = '/name="([^"]+)"/';
		$replacement = 'name="fields[' . $field['id'] . ']"';

		$html = preg_replace( $pattern, $replacement, $html );

		// Replace type attribute, use type="text" only
		$pattern     = '/type="([^"]+)"/';
		$replacement = 'type="text"';

		$html = preg_replace( $pattern, $replacement, $html );

		return $html;
	}

	public function initFields() {
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
		$this->meta_box->show();
		$this->render_block( 'footer' );
	}

	protected function render_block( $block = 'header' ): void {
		$filePath = __DIR__ . '/templates/' . $block . '.php';

		if ( ! file_exists( $filePath ) ) {
			return;
		}

		extract( $this->meta_box->meta_box );
		include $filePath;
	}

	public function parse( $parsingData ) {
		foreach ( $this->getFields() as $field ) {
			$xpath = empty( $parsingData['import']->options['fields'][ $field->getFieldKey() ] ) ? "" : $parsingData['import']->options['fields'][ $field->getFieldKey() ];
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