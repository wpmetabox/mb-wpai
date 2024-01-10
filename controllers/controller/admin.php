<?php

/**
 * Introduce special type for controllers which render pages inside admin area
 *
 * @author Maksym Tsypliakov <maksym.tsypliakov@gmail.com>
 */
abstract class PMAI_Controller_Admin extends PMAI_Controller {
	/**
	 * Admin page base url (request url without all get parameters but `page`)
	 * @var string
	 */
	public $baseUrl;
	/**
	 * Parameters which is left when baseUrl is detected
	 * @var array
	 */
	public $baseUrlParamNames = [ 'page', 'pagenum', 'order', 'order_by', 'type', 's', 'f' ];
	/**
	 * Whether controller is rendered inside WordPress page
	 * @var bool
	 */
	public $isInline = false;

	/**
	 * Constructor
	 */
	public function __construct() {
		$remove = array_diff( array_keys( $_GET ), $this->baseUrlParamNames );
		if ( $remove ) {
			$this->baseUrl = remove_query_arg( $remove );
		} else {
			$this->baseUrl = $_SERVER['REQUEST_URI'];
		}
		parent::__construct();

		wp_enqueue_style( 'pmai-admin-style', PMAI_ROOT_URL . '/static/css/admin.css' );

		wp_enqueue_script( 'pmai-script', PMAI_ROOT_URL . '/static/js/pmai.js', [ 'jquery' ] );
		wp_enqueue_script( 'pmai-admin-script', PMAI_ROOT_URL . '/static/js/admin.js', [
			'jquery',
			'jquery-ui-core',
			'jquery-ui-resizable',
			'jquery-ui-dialog',
			'jquery-ui-datepicker',
			'jquery-ui-draggable',
			'jquery-ui-droppable',
			'jquery-nestable',
			'pmxi-admin-script',
		], time() );
		wp_enqueue_script( 'pmai-datetimepicker', PMAI_ROOT_URL . '/static/js/jquery/datetime.min.js', [ 'jquery' ] );

		// Add base meta box script and style
		wp_enqueue_style( 'rwmb', RWMB_CSS_URL . 'style.css', [], RWMB_VER );
		if ( is_rtl() ) {
			wp_enqueue_style( 'rwmb-rtl', RWMB_CSS_URL . 'style-rtl.css', [], RWMB_VER );
		}

		wp_enqueue_script( 'rwmb', RWMB_JS_URL . 'script.js', [ 'jquery' ], RWMB_VER, true );
		wp_enqueue_script( 'rwmb-clone', RWMB_JS_URL . 'clone.js', [ 'jquery-ui-sortable' ], time(), true );

		wp_enqueue_style( 'rwmb-group', plugin_dir_url( '' ) . 'meta-box-group/group.css', [], time() );
		wp_enqueue_script( 'rwmb-group', plugin_dir_url( '' ) . 'meta-box-group/group.js', [ 'jquery', 'underscore' ], time(), true );
		wp_localize_script( 'rwmb-group', 'RWMB_Group', [
			'confirmRemove' => __( 'Are you sure you want to remove this group?', 'meta-box-group' ),
			'on'            => __( 'On', 'meta-box-group' ),
			'off'           => __( 'Off', 'meta-box-group' ),
			'yes'           => __( 'Yes', 'meta-box-group' ),
			'no'            => __( 'No', 'meta-box-group' ),
		] );
	}

	/**
	 * @see Controller::render()
	 */
	protected function render( $viewPath = null ) {
		// assume template file name depending on calling function
		if ( is_null( $viewPath ) ) {
			$trace    = debug_backtrace();
			$viewPath = str_replace( '_', '/', preg_replace( '%^' . preg_quote( PMAI_Plugin::PREFIX, '%' ) . '%', '', strtolower( $trace[1]['class'] ) ) ) . '/' . $trace[1]['function'];
		}
		parent::render( $viewPath );
	}
}
