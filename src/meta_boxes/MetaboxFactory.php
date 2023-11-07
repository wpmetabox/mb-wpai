<?php
namespace wpai_meta_box_add_on\meta_boxes;

use wpai_meta_box_add_on\meta_boxes\Metabox;

final class MetaboxFactory {
    /**
     * @param $meta_box
     * @param $post array import options
     * @return Metabox
     */
    public static function create($meta_box, $post = []): Metabox {
        
        return new Metabox($meta_box, $post);
    }
}