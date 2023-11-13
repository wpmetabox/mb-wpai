<?php

namespace wpai_meta_box_add_on\fields;

use wpai_meta_box_add_on\MetaboxService;
use wpai_meta_box_add_on\fields\acf\FieldEmpty;
use wpai_meta_box_add_on\fields\acf\FieldNotSupported;

/**
 * Class FieldFactory
 * @package wpai_meta_box_add_on\fields
 */
final class FieldFactory {

    /**
     *
     * An array of fields which are doesn't have any functionality
     *
     * @var array
     */
    public static $hiddenFields = ['accordion', 'tab', 'html', 'divider'];

    /**
     * @param $fieldData
     * @param $post
     * @param $fieldName
     * @param $fieldParent
     * @return mixed|FieldEmpty
     */
    public static function create($fieldData, $post, $fieldName = "", $fieldParent = false) {
        $field = FALSE;
        $class_suffix = str_replace(" ", "", ucwords(str_replace("_", " ", $fieldData['type'])));
        $class = '\\wpai_meta_box_add_on\\fields\\acf\\Field' . $class_suffix;
     
        if (!class_exists($class)) {
            $class = '\\wpai_meta_box_add_on\\fields\\acf\\' . $fieldData['type'] . '\\Field' . $class_suffix;
        }

	    $class = apply_filters( 'wp_all_import_acf_field_class', $class , $fieldData, $post, $fieldName, $fieldParent );
	    $field = apply_filters( 'wp_all_import_acf_field_field', $field,  $class, $fieldData, $post, $fieldName, $fieldParent );
	    
        if (!empty($field)){
		    return $field;
        }

        if (empty($fieldData['type']) || in_array($fieldData['type'], self::$hiddenFields)) {
            $field = new FieldEmpty($fieldData, $post, $fieldName);
        }  elseif (class_exists($class)) {
            $field = new $class($fieldData, $post, $fieldName, $fieldParent);
        }

        if (empty($field)){
            $field = new FieldNotSupported(false, $post);
        }
        
        return $field;
    }
}