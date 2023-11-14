<?php

namespace wpai_meta_box_add_on\fields;

/**
 * Interface FieldInterface
 * @package wpai_meta_box_add_on\fields
 */
interface FieldInterface{

    /**
     * @param $xpath
     * @param $parsingData
     * @param array $args
     * @return mixed
     */
    public function parse($xpath, $parsingData, array $args = []);

    /**
     * @param $importData
     * @param array $args
     * @return mixed
     */
    public function import($importData, array $args = []);

    /**
     * @param $importData
     * @return mixed
     */
    public function saved_post($importData);

    /**
     *
     * Determines is field value empty or not
     *
     * @return mixed
     */
    public function isNotEmpty();

    /**
     * @return mixed
     */
    public function getOriginalFieldValueAsString();
}