<?php

if (!function_exists('mbai_get_join_attr')):

    /**
     * @param mixed $attributes
     * @return string
     */
    function mbai_get_join_attr($attributes = false ) {
        // validate
        if ( empty($attributes) ) {
            return '';
        }
        // vars
        $e = [];
        // loop through and render
        foreach ( $attributes as $k => $v ) {
            $e[] = $k . '="' . esc_attr( $v ) . '"';
        }
        // echo
        return implode(' ', $e);
    }

endif;

if (!function_exists('mbai_join_attr')):

    /**
     * @param mixed $attributes
     */
    function mbai_join_attr($attributes = false ){
        echo mbai_get_join_attr( $attributes );
    }

endif;