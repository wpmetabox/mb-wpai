<?php

namespace wpai_meta_box_add_on\fields\acf;

use wpai_meta_box_add_on\MetaboxService;
use wpai_meta_box_add_on\fields\Field;

/**
 * Class FieldWpWysiwyg
 * @package wpai_meta_box_add_on\fields\acf
 */
class FieldWpWysiwyg extends Field {

    /**
     *  Field type key
     */
    public $type = 'wp_wysiwyg';

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
        $values = $this->getByXPath($xpath);
        $this->setOption('values', $values);
    }

    /**
     * @param $importData
     * @param array $args
     * @return mixed
     */
    public function import($importData, $args = array()) {
        $isUpdated = parent::import($importData, $args);
        if (!$isUpdated){
            return FALSE;
        }
        MetaboxService::update_post_meta($this, $this->getPostID(), $this->getFieldName(), $this->getFieldValue());
    }
}