<?php

namespace MetaBox\WPAI\Fields;

class FileInputHandler extends FileHandler {
    public function get_value() {
        $attachment = parent::get_value();

        if ( ! $attachment ) {
            return;
        }

        $attachment = get_post( $attachment['ID'] );

        if ( ! $attachment ) {
            return;
        }

        return $attachment->guid;
    }
}
