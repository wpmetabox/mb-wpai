<?php

namespace wpai_meta_box_add_on\fields\mb;

use wpai_meta_box_add_on\MetaboxService;
use wpai_meta_box_add_on\fields\Field;

/**
 * Class FieldFile
 * @package wpai_meta_box_add_on\fields\mb
 */
class File extends Field {

    /**
     *  Field type key
     */
    public $type = 'file';

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
        $xpath = is_array($xpath) ? $xpath['url'] : $xpath;
        $values = $this->getByXPath($xpath);
        $this->setOption('values', $values);
    }

    /**
     * @param $importData
     * @param array $args
     * @return void
     */
    public function import($importData, $args = array()) {
        $isUpdated = parent::import($importData, $args);
        if ($isUpdated){
            MetaboxService::update_post_meta($this, $this->getPostID(), $this->getFieldName(), $this->getFieldValue());
        }
    }

    /**
     * @return false|int|mixed|string
     */
    public function getFieldValue() {
        $attachmentID = false;
        if ("" != parent::getFieldValue() ) {
            $parsingData = $this->getParsingData();
            $xpath  = $this->getOption('xpath');
            $search_in_gallery = empty($xpath['search_in_media']) ? 0 : 1;
            $search_in_files = empty($xpath['search_in_files']) ? 0 : 1;
            $attachmentID = MetaboxService::import_file(parent::getFieldValue(), $this->getPostID(), $parsingData['logger'], $parsingData['import']->options['is_fast_mode'], $search_in_gallery, $search_in_files, $this->importData);
        }
        return $attachmentID;
    }
}