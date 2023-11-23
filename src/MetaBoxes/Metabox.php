<?php
namespace MetaBox\WPAI\MetaBoxes;

class Metabox extends BaseMetabox {

    public function initFields(): void {
	    $fields = $this->meta_box->meta_box['fields'];

        $this->fieldsData = $fields;

        parent::initFields();
    }
}