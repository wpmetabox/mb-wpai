<?php
/**
 * Copy meta from a post to it's latest revision.
 *
 * @param $pid
 */
function pmai_pmxi_saved_post( $pid ) {
	// Use the MB Revision plugin if it's installed.
	if ( function_exists( 'mb_revision_init' ) ) {
		$revision = mb_revision_init();

		if ( $revision ) {
			$revision->copy_fields_to_revision( $pid );
		}
	}
}
