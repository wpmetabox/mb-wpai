<?php
/**
 * Copy meta from a post to it's latest revision.
 *
 * @param $pid
 */
function pmai_pmxi_saved_post( $pid ) {
	$post_type = get_post_type( $pid );

	if ( $post_type && post_type_supports( $post_type, 'revisions' ) ) {
		// Use the MB Revision plugin if it's installed.
		if ( function_exists( 'mb_revision_init' ) ) {
			$revision = mb_revision_init();
			$revision->copy_fields_to_revision( $pid );
		}
	}
}