<?php
namespace wpai_meta_box_add_on\meta_boxes;

class Metabox extends BaseMetabox {

    public function initFields(): void {
	    $fields = $this->meta_box->meta_box['fields'];

        $this->fieldsData = $fields;

        parent::initFields();
    }
}