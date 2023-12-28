<?php

namespace MetaBox\WPAI\Fields;

class CheckboxHandler extends FieldHandler {

	/**
	 * @return null|bool|bool[]
	 */
	public function get_value() {
		$value = parent::get_value();
		$value = $this->to_bool( $value );

		return $value;
	}

	private function to_bool( $value ) {
		if ( ! is_array( $value ) ) {
			return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
		}

		return array_map( function ($item) {
			return $this->to_bool( $item );
		}, $value );
	}
}
