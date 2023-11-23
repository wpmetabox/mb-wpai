<?php

namespace MetaBox\WPAI\MetaBoxes;

use MetaBox\WPAI\MetaBoxes\Metabox;

final class MetaboxFactory {
	/**
	 * @param $meta_box
	 * @param $post array import options
	 *
	 * @return Metabox
	 */
	public static function create( \RW_Meta_Box $meta_box, array $post = [] ): Metabox {
		return new Metabox( $meta_box, $post );
	}
}