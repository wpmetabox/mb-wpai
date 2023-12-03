<?php
namespace MetaBox\WPAI\Fields;

final class FieldFactory {

	public static function create( $fieldData, $post, $key, $parent ): FieldHandler {
		$field_name = str_replace( " ", "", ucwords( str_replace( "_", " ", $fieldData['type'] ) ) );
		$field_class = __NAMESPACE__ . '\\' . $field_name . 'Handler';

		if ( ! class_exists( $field_class ) ) {
			$field_class = __NAMESPACE__ . '\\TextHandler';
		}

		return new $field_class( $fieldData, $post, $key, $parent );
	}
}
