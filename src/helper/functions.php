<?php
function mb_wpai_import($post_id, $data, $import_options) {

	global $mb_wpai;

	if ($mb_wpai->can_update_meta('_yoast_wpseo_title', $import_options)) {
		update_post_meta($post_id, '_yoast_wpseo_title', $data['yoast_wpseo_title']);
	}

	if ($mb_wpai->can_update_meta('_yoast_wpseo_metadesc', $import_options)) {
		update_post_meta($post_id, '_yoast_wpseo_metadesc', $data['yoast_wpseo_metadesc']);
	}

	if ($mb_wpai->can_update_meta('_yoast_wpseo_meta-robots-noindex', $import_options)) {
		update_post_meta($post_id, '_yoast_wpseo_meta-robots-noindex', $data['yoast_wpseo_meta-robots-noindex']);
	}


	if ($mb_wpai->can_update_image($import_options)) {
		$image_url = wp_get_attachment_url($data['yoast_wpseo_opengraph-image']['attachment_id']);
		update_post_meta($post_id, '_yoast_wpseo_opengraph-image', $image_url);
	}

}