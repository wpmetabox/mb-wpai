<?php

namespace wpai_meta_box_add_on\fields\mb;

/**
 * Class FieldLocationField
 * @package wpai_meta_box_add_on\fields\mb
 */
class LocationField extends FieldGoogleMap {

    /**
     *  Field type key
     */
    public $type = 'location-field';

    /**
     * @return false|int|mixed|string
     */
    public function getFieldValue() {
        $values = $this->getOption('values');
        return $values['address'][$this->getPostIndex()] . "|" . $values['lat'][$this->getPostIndex()] . "," . $values['lng'][$this->getPostIndex()];
    }
}