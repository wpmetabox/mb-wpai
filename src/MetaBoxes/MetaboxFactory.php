<?php

namespace MetaBox\WPAI\MetaBoxes;

use MetaBox\WPAI\MetaBoxes\MetaboxHandler;

final class MetaboxFactory {
	/**
	 * @param $meta_box
	 * @param $post array import options
	 *
	 * @return MetaboxHandler
	 */
	public static function create( \RW_Meta_Box $meta_box, array $post = [] ): MetaboxHandler {
		return new MetaboxHandler( $meta_box, $post );
	}
}