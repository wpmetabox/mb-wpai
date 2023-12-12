<?php
namespace MetaBox\WPAI\Fields;

class GroupHandler extends FieldHandler {
	private function get_children_value( $fields, $args ): array {
		$value = [];

		foreach ( $fields as $mb_field ) {

			$field = FieldFactory::create( $mb_field, $this->post, $this->meta_box );

			$field->parsingData = $this->parsingData;
			$field->base_xpath  = $this->parsingData['xpath_prefix'] . $this->parsingData['import']['xpath'];
			$field->xpath       = $mb_field['binding'];
			$field->importData  = $this->importData;

			if ( isset($field->field['fields']) && is_array( $field->field['fields'] ) ) {
				$value[ $field->field['id'] ] = $this->get_children_value( $field->field['fields'], $args );
			} else {
                $value[ $field->field['id'] ] = $field->get_value();
            }
		}

		return $value;
	}

	public function get_value(): array {
		$value = [];

		if ( isset( $this->field['fields'] ) && is_array( $this->field['fields'] ) ) {
			$value[] = $this->get_children_value( $this->field['fields'], [] );
		}
        
        return $this->field['clone'] ? $value : $value[0];
	}
}