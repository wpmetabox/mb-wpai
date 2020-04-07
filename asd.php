<?php
function helper_parse($parsingData, $options) {

extract($parsingData);

$data = []; // parsed data

if ( ! $import->options[$this->slug] ) {
    return;
}
// error_log( print_r( $import->options[$this->slug] ) );
// log( print_r( $import->options[$this->slug] ) );
$this->logger = $parsingData['logger'];

$cxpath = $xpath_prefix . $import->xpath;

$tmp_files = [];

foreach ( $options[$this->slug] as $option_name => $option_value ) {
    // error_log( print_r( $option_value ) );
    if ( $import->options[$this->slug][$option_name] ) {						
        $this->parse_xpath( $cxpath, $import->$options[$this->slug], $data, $option_name, $file );
        $this->parse_metabox( $cxpath, $import->$options[$this->slug], $data, $option_name, $file );
    } else {
        $data[$option_name] = array_fill(0, $count, "");
    }

    $tmp_files[] = $file;
}

foreach ($tmp_files as $file) { // remove all temporary files created
    unlink($file);
}

var_dump( $data );
return $data;
}

function parse_xpath( $cxpath, $import_slug, $data, $option_name, $file ) {
if ( $import_slug[$option_name] != "xpath" ) {
    return;
}
if ( "" == $import_slug['xpaths'][$option_name] ){
    $count and $data[$option_name] = array_fill( 0, $count, "" );
} else {
    $data[$option_name] = XmlImportParser::factory($xml, $cxpath, (string) $import_slug['xpaths'][$option_name], $file)->parse();
}
}

function parse_metabox( $cxpath, $import_slug, $data, $option_name, $file ) {
$field = rwmb_get_field_settings( $data[$option_name] );

$this->parse_metabox_clone( $field, $cxpath, $import_slug, $data, $option_name, $file );
$this->parse_metabox_not_clone( $field, $cxpath, $import_slug, $data, $option_name, $file );
}

function parse_metabox_clone( $field, $cxpath, $import_slug, $data, $option_name, $file ) {
if ( ! $field['clone'] ) {
    return;
}
$string_data = (string) $import_slug[$option_name];
$lines = explode( "\r\n", $string_data );

$lines_num = $lines[0];

$temp = [];

$temp_2 = [];

for ( $x = 1; $x <= $lines_num; $x++ ) {
    $l = str_replace( '_i_', $x, $lines[1]);
    $temp[] = XmlImportParser::factory($xml, $cxpath, $l, $file)->parse();
}

// [ a, a, a ]
// [ b, b, b ]
// => [ a, b ], [ a, b ], [ a, b ]
for ( $x = 0; $x < $lines_num; $x++ ) {
    foreach ( $temp as $k => $v ) {
        $temp_2[] = $v[ $x ];
    }
}

$data[$option_name] = array_chunk( $temp_2, ceil( count( $temp_2 ) / $lines_num ) );
}

function parse_metabox_not_clone( $field, $cxpath, $import_slug, $data, $option_name, $file ) {
if ( $field['clone'] ) {
    return;
}
$data[$option_name] = XmlImportParser::factory($xml, $cxpath, (string) $import_slug[$option_name], $file)->parse();
}