<?php

namespace MetaBox\WPAI\Fields;

class FileInputHandler extends FileHandler {
    public function get_value() {
        $attachment_id = parent::get_value();

        if ( ! $attachment_id ) {
            return;
        }

        $attachment = get_post( $attachment_id );

        if ( ! $attachment ) {
            return;
        }

        return $attachment->guid;
    }
}
