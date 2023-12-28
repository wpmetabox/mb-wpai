<?php
namespace MetaBox\WPAI\Fields;

class PostHandler extends FieldHandler {
	public function get_value() {
        $post_ids = parent::get_value();
        
        if ( ! $post_ids ) {
            return;
        }

        if ( ! is_array( $post_ids ) ) {
            $post_ids = [ $post_ids ];
        }
        
         // Search for posts by id or slug  
        $posts_by_ids = get_posts( [
            'post_type' => $this->field['post_type'] ?? 'post',
            'post__in'  => $post_ids,
        ] );

        $posts_by_slugs = get_posts( [
            'post_type'   => $this->field['post_type'] ?? 'post',
            'post_name__in' => $post_ids,
        ] );

        $posts = array_merge( $posts_by_ids, $posts_by_slugs );

        if ( ! $posts ) {
            return;
        }

        // return array of post ids
        $post_ids = wp_list_pluck( $posts, 'ID' );
        $post_ids = array_unique( $post_ids );

        return $post_ids;
    }
}
