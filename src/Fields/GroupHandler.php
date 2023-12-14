<?php
namespace MetaBox\WPAI\Fields;

class GroupHandler extends FieldHandler {
    public $mode = 'fixed';
    public $delimeter = ',';
    public $is_ignore_empties = false;
    public $foreach = '';

	public function parse( $xpath, $parsingData, $args = [] ) {
		$xpath = json_decode( $xpath, true );

		$this->xpath       = $xpath;
		$this->parsingData = $parsingData;
		$this->base_xpath  = $parsingData['xpath_prefix'] . $parsingData['import']['xpath'];
	}


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

    public function get_tree_value($rows, $post_index) {
        $value = [];

        foreach ( $rows as $index => $row ) {
            if ( $index === 'ROWNUMBER' ) {
                continue;
            }

            foreach ($row as $column => $xpath) {
                if (is_string($xpath)) {
                    $values = $this->get_value_by_xpath($xpath);

                    if (isset($values[$post_index])) {
                        $value[$index][$column] = $values[$post_index];
                    }
                }

                if (is_array($xpath)) {
                    $value[$index][$column] = $this->get_tree_value($xpath['rows'], $post_index);
                }
            }
        }

        return $value;
    }

	public function get_value() {
        if (!is_array($this->xpath)) {
            return;
        }

        $post_index = $this->get_post_index();
        $value = $this->get_tree_value($this->xpath['rows'], $post_index);

        if ($this->returns_array()) {
            return $value;
        } 

        return $value[0];
	}
}