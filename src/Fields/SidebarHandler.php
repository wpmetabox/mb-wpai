<?php

namespace MetaBox\WPAI\Fields;

/**
 * This class get_values() method should returns array of strings so we need
 * to extends from FieldHandler class instead of SidebarHandler
 */
class SidebarHandler extends FieldHandler {
	public function get_value() {
		$value = parent::get_value();

		return $this->field['clone'] ? $value : $value[0];
	}
}
