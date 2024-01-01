<?php
namespace MetaBox\WPAI\Fields;

class BackgroundHandler extends FieldHandler {
	public function get_value() {
		$xpath  = $this->field['_wpai']['xpath'];
		$output = [];

		foreach ( $xpath as $clone_index => $background ) {
			if ( ! is_array( $background ) ) {
				$background  = $xpath;
				$clone_index = 0;
			}

			foreach ( $background as $key => $value ) {
				if ( str_contains( $value, '{' ) && str_contains( $value, '}' ) ) {
					$value = $this->get_value_by_xpath( $value );

					if ( is_array( $value ) ) {
						$value = $value[0];
					}
				}

				$output[ $clone_index ][ $key ] = $value;
			}
		}

		return $this->field['clone'] ? $output : $output[0];
	}
}
