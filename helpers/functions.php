<?php

function pmai_get_meta_box_by_slug(string $slug) : RW_Meta_Box
{	
	$meta_box_registry = rwmb_get_registry( 'meta_box' );

	return $meta_box_registry->get($slug);
}
