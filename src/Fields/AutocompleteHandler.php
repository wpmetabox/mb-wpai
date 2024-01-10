<?php

namespace MetaBox\WPAI\Fields;

class AutocompleteHandler extends FieldHandler {
	public function get_value() {
		$value = parent::get_value();

		return $this->field['clone'] ? $value : $value[0];
	}
}
