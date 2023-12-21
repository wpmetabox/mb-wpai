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
		$this->base_xpath  = $parsingData['xpath_prefix'] . $parsingData['import']->xpath;
	}

    public function get_tree_value(array $xpath, $post_index) {
        $output = [];

        foreach ( $xpath['rows'] as $index => $row ) {
            if ( $index === 'ROWNUMBER' ) {
                continue;
            }

            foreach ($row as $column => $sub_xpath) {
                if (is_string($sub_xpath)) {
                    $values = $this->get_value_by_xpath($sub_xpath);

                    if (isset($values[$post_index])) {
                        // Variable repeater mode
                        $values[$post_index] = explode($xpath['separator'] ?? '|', $values[$post_index]);
                        $output[$index][$column] = $values[$post_index];
                    }
                }

                if (is_array($sub_xpath)) {
                    $output[$index][$column] = $this->get_tree_value($sub_xpath, $post_index);
                }
            }
        }

        return $output;
    }

	public function get_value() {
        if (!is_array($this->xpath)) {
            return;
        }

        $this->xpath = $this->bind_foreach($this->xpath);
        
        $post_index = $this->get_post_index();
        $values = $this->get_tree_value($this->xpath, $post_index);
                
        if ($this->returns_array()) {
            return $values;
        } 

        return $values[0];
	}

    private function bind_foreach(array $xpath)
    {
        foreach ($xpath['rows'] as $index => $row) {
            if ($index === 'ROWNUMBER') {
                continue;
            }

            foreach ($row as $column => $cxpath) {
                if (is_string($cxpath)) {
                    $cxpath = '{' . $this->get_string_between($xpath['foreach']) . $this->get_string_between($cxpath) . '}';
                }

                if (is_array($cxpath)) {
                    $cxpath = $this->bind_foreach($cxpath);
                }

                $xpath['rows'][$index][$column] = $cxpath;
            }
        }

        return $xpath;
    }

    private function get_string_between($string)
    {
        $start = '{';
        $end = '}';

        $string = ' ' . $string;
        $ini = strpos($string, $start);

        if ($ini == 0) {
            return '';
        }

        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        $str = substr($string, $ini, $len);

        if ($str === '.') {
            $str = '';
        }

        return $str;
    }
}