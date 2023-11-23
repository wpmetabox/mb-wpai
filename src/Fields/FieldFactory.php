<?php
namespace MetaBox\WPAI\Fields;

final class FieldFactory {

	public static function create( $fieldData, $post, $fieldName = "", $fieldParent = false ): Field {
		$field_name = str_replace( " ", "", ucwords( str_replace( "_", " ", $fieldData['type'] ) ) );
		$field_class = __NAMESPACE__ . '\\' . $field_name;

		if ( ! class_exists( $field_class ) ) {
			$field_class = __NAMESPACE__ . '\\Text';
		}

		return new $field_class( $fieldData, $post, $fieldName, $fieldParent );
	}
}
