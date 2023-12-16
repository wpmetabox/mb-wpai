<?php
$field_value = $field['clone'] ? [$field_value] : $field_value;

$fieldset_text_field = array_merge($field, [
    'id' => $field['_name'],
    'std' => $field_value,
    'field_name' => $field['_name'],
]);

$fieldset_text_fields = \RW_Meta_Box::normalize_fields( [$fieldset_text_field] );

RWMB_Field::call('show', $fieldset_text_fields[0] , false );
?>