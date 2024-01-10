<?php

use MetaBox\WPAI\MetaBoxes\MetaBoxHandler;
use MetaBox\WPAI\MetaBoxes\MetaBoxFactory;

/**
 * Class PMAI_Import_Record
 */
class PMAI_Import_Record extends PMAI_Model_Record {

	/**
	 * @var MetaBoxHandler[]
	 */
	public array $metaboxes = [];

	/**
	 * Initialize model instance
	 *
	 * @param array [optional] $data Array of record data to initialize object with
	 */
	public function __construct( $data = [] ) {
		parent::__construct( $data );

		$this->setTable( PMXI_Plugin::getInstance()
		->getTablePrefix() . 'imports' );
	}

	/**
	 * @param array $parsingData [import, count, xml, logger, chunk, xpath_prefix]
	 */
	public function parse( array $parsingData ) {

		add_filter( 'user_has_cap', [
			$this,
			'_filter_has_cap_unfiltered_html',
		] );
		kses_init(); // do not perform special filtering for imported content

		$parsingData['chunk'] == 1 and $parsingData['logger'] and call_user_func( $parsingData['logger'], __( 'Composing meta box...', 'mb-wpai' ) );

		if ( ! empty( $parsingData['import']->options['meta_box'] ) ) {
			$metaboxGroups = $parsingData['import']->options['meta_box'];
			if ( ! empty( $metaboxGroups ) ) {
				foreach ( $metaboxGroups as $groupId => $enabled ) {

					if ( ! $enabled ) {
						continue;
					}

					if ( ! is_numeric( $groupId ) ) {
						$group = pmai_get_meta_box_by_slug( $groupId );

						if ( ! empty( $group ) ) {
							$this->metaboxes[] = MetaBoxFactory::create( $group, $parsingData['import']->options );
						}
					} else {
						$this->metaboxes[] = MetaBoxFactory::create( $groupId, $parsingData['import']->options );
					}
				}
			}

			foreach ( $this->metaboxes as $group ) {
				$group->parse( $parsingData );
			}
		}

		remove_filter( 'user_has_cap', [
			$this,
			'_filter_has_cap_unfiltered_html',
		] );
		kses_init(); // return any filtering rules back if they has been disabled for import procedure
	}

	/**
	 * @param $importData [pid, i, import, articleData, xml, is_cron, xpath_prefix]
	 */
	public function import( $importData ) {
		$importData['logger'] and call_user_func( $importData['logger'], __( '<strong>Metabox ADD-ON:</strong>', 'mb-wpai' ) );

		foreach ( $this->metaboxes as $mb ) {
			$mb->import( $importData );
		}
	}

	/**
	 * @param $importData [pid, import, logger, is_update]
	 */
	public function saved_post( $importData ) {
		foreach ( $this->metaboxes as $mb ) {
			$mb->saved_post( $importData );
		}
	}

	/**
	 * @param $caps
	 *
	 * @return mixed
	 */
	public function _filter_has_cap_unfiltered_html( $caps ) {
		$caps['unfiltered_html'] = true;

		return $caps;
	}

	/**
	 * @param $var
	 *
	 * @return mixed
	 */
	public function filtering( $var ) {
		return ! empty( $var );
	}
}
