<?php

namespace MetaBox\WPAI\Fields;

class FieldsetTextHandler extends KeyValueHandler {


	public function get_value() {
		$xpaths = $this->get_xpaths();
		$xpaths = $this->build_xpaths_tree( $xpaths );

        $values = [];

		foreach ( $xpaths as $clone_index => $row ) {
            foreach ($row as $column => $xpath) {
                $values[$clone_index][$column] = $this->get_value_by_xpath($xpath);
            }
		}

        $output = [];
        foreach ($values as $index => $row) {
            $output = array_merge($output, $this->convert_array($row));
        }

		return $this->field['clone'] ? $output : $output[0] ?? null;
	}

    private function convert_array($array) {
        $result = [];
        $keys = array_keys($array);
    
        foreach ($array as $key => $values) {
            foreach ($values as $index => $value) {
                foreach ($keys as $k) {
                    $result[$index][$k] = $array[$k][$index] ?? null;
                }
            }
        }
    
        return $result;
    }

	private function build_xpaths_tree( $xpath ) {
		$tree = [];

		foreach ( $xpath as $clone_index => $sub_xpath ) {
			if ( is_string( $sub_xpath ) ) {
				$clone_index = 0;
				$sub_xpath   = $xpath;
			}

			$tree[ $clone_index ] = $sub_xpath;
		}

		return $tree;
	}
}
