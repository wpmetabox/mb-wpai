<?php
$key_value_field = array_merge($field, [
    'id' => $field['_name'],
    'std' => $field_value,
]);

$key_value_fields = \RW_Meta_Box::normalize_fields( [$key_value_field] );

RWMB_Field::call('show', $key_value_fields[0] , false );
?>