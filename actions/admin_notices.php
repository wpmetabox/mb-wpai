<?php

/**
 * Render admin notices if ACF or WP All Import plugins are activated
 */
function pmai_admin_notices() {

	// notify user if history folder is not writable		
	if ( ! class_exists( 'PMXI_Plugin' ) ) {
		?>
        <div class="error"><p>
				<?php printf(
					__( '<b>%s Plugin</b>: WP All Import must be installed. Free edition of WP All Import at <a href="http://wordpress.org/plugins/wp-all-import/" target="_blank">http://wordpress.org/plugins/wp-all-import/</a> and the paid edition at <a href="http://www.wpallimport.com/">http://www.wpallimport.com/</a>', 'mbai' ),
					PMAI_Plugin::getInstance()->getName()
				) ?>
            </p></div>
		<?php

		deactivate_plugins( PMAI_ROOT_DIR . '/wpai-acf-add-on.php' );
	}

	if ( class_exists( 'PMXI_Plugin' ) and ( version_compare( PMXI_VERSION, '4.1.1' ) < 0 and PMXI_EDITION == 'paid' or version_compare( PMXI_VERSION, '3.2.3' ) <= 0 and PMXI_EDITION == 'free' ) ) {
		?>
        <div class="error"><p>
				<?php printf(
					__( '<b>%s Plugin</b>: Please update your WP All Import to the latest version', 'mbai' ),
					PMAI_Plugin::getInstance()->getName()
				) ?>
            </p></div>
		<?php

		deactivate_plugins( PMAI_ROOT_DIR . '/wpai-acf-add-on.php' );
	}

	if ( ! class_exists( 'acf' ) ) {
		?>
        <div class="error"><p>
				<?php printf(
					__( '<b>%s Plugin</b>: <a target="_blank" href="http://wordpress.org/plugins/advanced-custom-fields/">Advanced Custom Fields</a> must be installed', 'mbai' ),
					PMAI_Plugin::getInstance()->getName()
				) ?>
            </p></div>
		<?php

		deactivate_plugins( PMAI_ROOT_DIR . '/wpai-acf-add-on.php' );

	}
	$input    = new PMAI_Input();
	$messages = $input->get( 'pmai_nt', [] );
	if ( $messages ) {
		is_array( $messages ) or $messages = [ $messages ];
		foreach ( $messages as $type => $m ) {
			in_array( (string) $type, [ 'updated', 'error' ] ) or $type = 'updated';
			?>
            <div class="<?= $type ?>"><p><?= $m ?></p></div>
			<?php
		}
	}
}
