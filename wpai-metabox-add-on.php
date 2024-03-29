<?php
/**
 * Plugin Name: WP All Import - Meta Box Add-On
 * Plugin URI: https://metabox.io
 * Description: Import to Meta Box. Requires WP All Import & Meta Box.
 * Version: 0.0.0
 * Author: MetaBox.io
 **/

/**
 * Plugin root dir with forward slashes as directory separator regardless of actual DIRECTORY_SEPARATOR value
 * @var string
 */
define( 'PMAI_ROOT_DIR', str_replace( '\\', '/', __DIR__ ) );
/**
 * Plugin root url for referencing static content
 * @var string
 */
define( 'PMAI_ROOT_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );

const PMAI_PREFIX = 'pmai_';

const PMAI_VERSION = '5.5.5';

const PMAI_EDITION = 'paid';

require PMAI_ROOT_DIR . '/vendor/autoload.php';

final class PMAI_Plugin {

	protected static $instance;

	public static $all_acf_fields = [];

	const ROOT_DIR = PMAI_ROOT_DIR;

	const ROOT_URL = PMAI_ROOT_URL;

	const PREFIX = PMAI_PREFIX;

	const FILE = __FILE__;

	public static function getInstance() {
		if ( self::$instance == null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function getEddName() {
		return 'Meta Box Add-On';
	}

	public function __call( $method, $args ) {
		if ( preg_match( '%^get(.+)%i', $method, $mtch ) ) {
			$info = get_plugin_data( self::FILE );
			if ( isset( $info[ $mtch[1] ] ) ) {
				return $info[ $mtch[1] ];
			}
		}
		throw new Exception( 'Requested method ' . get_class( $this ) . "::$method doesn't exist." );
	}

	/**
	 * Get path to plagin dir relative to WordPress root
	 *
	 * @param mixed[optional] $noForwardSlash Whether path should be returned withot forwarding slash
	 *
	 * @return string
	 */
	public function getRelativePath( $noForwardSlash = false ) {
		$wp_root = str_replace( '\\', '/', ABSPATH );

		return ( $noForwardSlash ? '' : '/' ) . str_replace( $wp_root, '', self::ROOT_DIR );
	}

	public function isNetwork(): bool {
		if ( ! is_multisite() ) {
			return false;
		}

		$plugins = get_site_option( 'active_sitewide_plugins' );

		return ( isset( $plugins[ plugin_basename( self::FILE ) ] ) );
	}

	public function isPermalinks(): bool {
		global $wp_rewrite;

		return $wp_rewrite->using_permalinks();
	}

	public function getTablePrefix(): string {
		global $wpdb;

		return ( $this->isNetwork() ? $wpdb->base_prefix : $wpdb->prefix ) . self::PREFIX;
	}

	public function getWPPrefix(): string {
		global $wpdb;

		return ( $this->isNetwork() ? $wpdb->base_prefix : $wpdb->prefix );
	}

	/**
	 * Class constructor containing dispatching logic
	 *
	 * @param string $rootDir Plugin root dir
	 * @param string $pluginFilePath Plugin main file
	 */
	protected function __construct() {
		register_activation_hook( self::FILE, [ $this, 'activation' ] );

		// register action handlers
		if ( is_dir( self::ROOT_DIR . '/actions' ) ) {
			if ( is_dir( self::ROOT_DIR . '/actions' ) ) {
				foreach ( PMAI_Helper::safe_glob( self::ROOT_DIR . '/actions/*.php', PMAI_Helper::GLOB_RECURSE | PMAI_Helper::GLOB_PATH ) as $filePath ) {
					require_once $filePath;
					$function = $actionName = basename( $filePath, '.php' );
					if ( preg_match( '%^(.+?)[_-](\d+)$%', $actionName, $m ) ) {
						$actionName = $m[1];
						$priority   = intval( $m[2] );
					} else {
						$priority = 10;
					}
					add_action( $actionName, self::PREFIX . str_replace( '-', '_', $function ), $priority, 99 ); // since we don't know at this point how many parameters each plugin expects, we make sure they will be provided with all of them (it's unlikely any developer will specify more than 99 parameters in a function)
				}
			}
		}

		// register filter handlers
		if ( is_dir( self::ROOT_DIR . '/filters' ) ) {
			foreach ( PMAI_Helper::safe_glob( self::ROOT_DIR . '/filters/*.php', PMAI_Helper::GLOB_RECURSE | PMAI_Helper::GLOB_PATH ) as $filePath ) {
				require_once $filePath;
				$function = $actionName = basename( $filePath, '.php' );
				if ( preg_match( '%^(.+?)[_-](\d+)$%', $actionName, $m ) ) {
					$actionName = $m[1];
					$priority   = intval( $m[2] );
				} else {
					$priority = 10;
				}
				add_filter( $actionName, self::PREFIX . str_replace( '-', '_', $function ), $priority, 99 ); // since we don't know at this point how many parameters each plugin expects, we make sure they will be provided with all of them (it's unlikely any developer will specify more than 99 parameters in a function)
			}
		}

		// register admin page pre-dispatcher
		add_action( 'admin_init', [ $this, 'adminInit' ], 1 );
		add_action( 'init', [ $this, 'init' ], 10 );
	}

	public function init() {
		$this->load_plugin_textdomain();
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present
	 *
	 * @access public
	 * @return void
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'mb-wpai' );
		load_plugin_textdomain( 'mb-wpai', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages' );
	}

	/**
	 * pre-dispatching logic for admin page controllers
	 */
	public function adminInit() {
		$input = new PMAI_Input();
		$page  = strtolower( $input->getpost( 'page', '' ) );

		if ( preg_match( '%^' . preg_quote( str_replace( '_', '-', self::PREFIX ), '%' ) . '([\w-]+)$%', $page ) ) {
			$this->adminDispatcher( $page, strtolower( $input->getpost( 'action', 'index' ) ) );
		}
	}

	public function shortcodeDispatcher( array $args, string $content, string $tag ): string {
		$controllerName = self::PREFIX . preg_replace_callback( '%(^|_).%', [
			$this,
			'replace_callback',
		], $tag ); // capitalize first letters of class name parts and add prefix
		$controller     = new $controllerName();

		if ( ! $controller instanceof PMAI_Controller ) {
			throw new \Exception( "Shortcode `$tag` matches to a wrong controller type." );
		}

		ob_start();
		$controller->index( $args, $content );

		return ob_get_clean();
	}

	public function adminDispatcher( $page = '', $action = 'index' ) {
		static $buffer          = null;
		static $buffer_callback = null;

		if ( '' === $page ) {
			if ( ! is_null( $buffer ) ) {
				echo '<div class="wrap">';
				echo $buffer;
				do_action( 'pmai_action_after' );
				echo '</div>';
			} elseif ( ! is_null( $buffer_callback ) ) {
				echo '<div class="wrap">';
				call_user_func( $buffer_callback );
				do_action( 'pmai_action_after' );
				echo '</div>';
			} else {
				throw new Exception( 'There is no previousely buffered content to display.' );
			}
		} else {
			$actionName = str_replace( '-', '_', $action );
			// capitalize prefix and first letters of class name parts
			$controllerName = preg_replace_callback( '%(^' . preg_quote( self::PREFIX, '%' ) . '|_).%', [
				$this,
				'replace_callback',
			], str_replace( '-', '_', $page ) );
			if ( method_exists( $controllerName, $actionName ) ) {

				if ( ! get_current_user_id() or ! current_user_can( PMXI_Plugin::$capabilities ) ) {
					// This nonce is not valid.
					die( 'Security check' );

				} else {

					$this->_admin_current_screen = (object) [
						'id'         => $controllerName,
						'base'       => $controllerName,
						'action'     => $actionName,
						'is_ajax'    => isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) and strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) === 'xmlhttprequest',
						'is_network' => is_network_admin(),
						'is_user'    => is_user_admin(),
					];
					add_filter( 'current_screen', [ $this, 'getAdminCurrentScreen' ] );

					$controller = new $controllerName();
					if ( ! $controller instanceof PMAI_Controller_Admin ) {
						throw new Exception( "Administration page `$page` matches to a wrong controller type." );
					}

					if ( $this->_admin_current_screen->is_ajax ) { // ajax request
						$controller->$action();
						do_action( 'pmai_action_after' );
						die(); // stop processing since we want to output only what controller is randered, nothing in addition
					} elseif ( ! $controller->isInline ) {
						ob_start();
						$controller->$action();
						$buffer = ob_get_clean();
					} else {
						$buffer_callback = [ $controller, $action ];
					}
				}
			} else { // redirect to dashboard if requested page and/or action don't exist
				wp_redirect( admin_url() );
				die();
			}
		}
	}

	protected $_admin_current_screen = null;

	public function getAdminCurrentScreen() {
		return $this->_admin_current_screen;
	}

	public function replace_callback( $matches ) {
		return strtoupper( $matches[0] );
	}

	/**
	 * Plugin activation logic
	 */
	public function activation() {
		// Uncaught exception doesn't prevent plugin from being activated, therefore replace it with fatal error so it does.
		set_exception_handler( function ( $e ) {
			trigger_error( $e->getMessage(), E_USER_ERROR );
		} );
	}

	/**
	 *  Init all available MB fields
	 */
	public static function get_available_mb_fields() {
		return pmai_get_all_mb_fields();
	}

	public static function get_default_import_options() {
		return [
			'meta_box'                => [],
			'fields'                  => [],
			'fields_settings'         => [],
			'is_multiple_field_value' => [],
			'multiple_value'          => [],
			'fields_delimiter'        => [],
			'is_update_mb'            => 1,
			'update_mb_logic'         => 'full_update',
			'mb_field_list'           => [],
			'mb_only_list'            => [],
			'mb_except_list'          => [],
		];
	}
}


PMAI_Plugin::getInstance();
