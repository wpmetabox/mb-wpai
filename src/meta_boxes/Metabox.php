<?php
namespace wpai_meta_box_add_on\meta_boxes;

class Metabox extends BaseMetabox {

    public function initFields() {
	    $fields = $this->meta_box['fields'];

        $this->fieldsData = $fields;

        parent::initFields();
    }
}