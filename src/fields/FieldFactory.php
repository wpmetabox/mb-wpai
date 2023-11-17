<?php
namespace wpai_meta_box_add_on\fields;

final class FieldFactory {

    public static $hiddenFields = ['accordion', 'tab', 'html', 'divider'];

    public static function create($fieldData, $post, $fieldName = "", $fieldParent = false): Field {
        $field_name = str_replace(" ", "", ucwords(str_replace("_", " ", $fieldData['type'])));
        $field_class = __NAMESPACE__ .  '\\mb\\' . $field_name;
     
        if (!class_exists($field_class)) {
            throw new \Exception("Field class $field_class doesn't exist");
        }
	   
        return new $field_class($fieldData, $post, $fieldName, $fieldParent);
    }
}
