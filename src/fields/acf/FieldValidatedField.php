<?php

namespace wpai_meta_box_add_on\fields\acf;

use wpai_meta_box_add_on\fields\Field;

/**
 * Class FieldValidateField
 * @package wpai_meta_box_add_on\fields\acf
 */
class FieldValidateField extends Field {

    /**
     *  Field type key
     */
    public $type = 'validated_field';

    /**
     *
     * Parse field data
     *
     * @param $xpath
     * @param $parsingData
     * @param array $args
     */
    public function parse($xpath, $parsingData, $args = array()) {
        parent::parse($xpath, $parsingData, $args);
        if ("" != $xpath) {
            $field = $this->getOption('field');
            if (!empty($field['sub_field'])) {
                $this->setOption('field', $field['sub_field']);
            }
            $values = $this->getByXPath($xpath);
            $this->setOption('values', $values);
        }
    }
}