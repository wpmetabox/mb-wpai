<?php
namespace MetaBox\WPAI\Fields;

use MetaBox\WPAI\MetaBoxes\MetaBoxHandler;

final class FieldFactory {

	public static function create( array $fieldData, array $post, MetaBoxHandler $meta_box ): FieldHandler {
		$field_name = str_replace( ' ', '', ucwords( str_replace( '_', ' ', $fieldData['type'] ) ) );

		$field_class = __NAMESPACE__ . '\\' . $field_name . 'Handler';

		if ( ! class_exists( $field_class ) ) {
			$field_class = __NAMESPACE__ . '\\TextHandler';
		}

		return new $field_class( $fieldData, $post, $meta_box );
	}
}
