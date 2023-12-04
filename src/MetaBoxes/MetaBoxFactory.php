<?php

namespace MetaBox\WPAI\MetaBoxes;

final class MetaBoxFactory {
	/**
	 * @param \RW_Meta_Box $meta_box
	 * @param $post array import
	 *
	 * @return MetaBoxHandler
	 */
	public static function create( \RW_Meta_Box $meta_box, array $post = [] ): MetaBoxHandler {
		return new MetaBoxHandler( $meta_box, $post );
	}
}
