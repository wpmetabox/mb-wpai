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
define( 'MBAI_ROOT_DIR', str_replace( '\\', '/', dirname( __FILE__ ) ) );
/**
 * Plugin root url for referencing static content
 * @var string
 */
define( 'MBAI_ROOT_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );

define( 'MBAI_PREFIX', 'mbai_' );

define( 'MBAI_VERSION', '0.0.0' );

define( 'MBAI_EDITION', 'paid' );

require MBAI_ROOT_DIR . '/vendor/autoload.php';

final class MBAI_Plugin {

	protected static $instance;

	public static $all_acf_fields = array();

	const ROOT_DIR = MBAI_ROOT_DIR;

	const ROOT_URL = MBAI_ROOT_URL;

	const PREFIX = MBAI_PREFIX;

	const FILE = __FILE__;

	static public function getInstance() {
		if ( self::$instance == NULL ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	static public function getEddName() {
		return 'Meta Box Add-On';
	}

	public function __call( $method, $args ) {
		if ( preg_match( '%^get(.+)%i', $method, $mtch ) ) {
			$info = get_plugin_data( self::FILE );
			if ( isset( $info[ $mtch[1] ] ) ) {
				return $info[ $mtch[1] ];
			}
		}
		throw new Exception( "Requested method " . get_class( $this ) . "::$method doesn't exist." );
	}

	/**
	 * Get path to plagin dir relative to wordpress root
	 * @param mixed[optional] $noForwardSlash Whether path should be returned withot forwarding slash
	 * @return string
	 */
	public function getRelativePath( $noForwardSlash = false ) {
		$wp_root = str_replace( '\\', '/', ABSPATH );
		return ( $noForwardSlash ? '' : '/' ) . str_replace( $wp_root, '', self::ROOT_DIR );
	}

	public function isNetwork(): bool {
		if ( ! is_multisite() )
			return false;

		$plugins = get_site_option( 'active_sitewide_plugins' );
		if ( isset( $plugins[ plugin_basename( self::FILE ) ] ) )
			return true;

		return false;
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
	 * @param string $rootDir Plugin root dir
	 * @param string $pluginFilePath Plugin main file
	 */
	protected function __construct() {

		// create/update required database tables

		// register autoloading method
		spl_autoload_register( array( $this, 'autoload' ) );

		// register helpers
		if ( is_dir( self::ROOT_DIR . '/helpers' ) )
			foreach ( MBAI_Helper::safe_glob( self::ROOT_DIR . '/helpers/*.php', MBAI_Helper::GLOB_RECURSE | MBAI_Helper::GLOB_PATH ) as $filePath ) {
				require_once $filePath;
			}

		register_activation_hook( self::FILE, array( $this, 'activation' ) );

		// register action handlers
		if ( is_dir( self::ROOT_DIR . '/actions' ) ) if ( is_dir( self::ROOT_DIR . '/actions' ) )
			foreach ( MBAI_Helper::safe_glob( self::ROOT_DIR . '/actions/*.php', MBAI_Helper::GLOB_RECURSE | MBAI_Helper::GLOB_PATH ) as $filePath ) {
				require_once $filePath;
				$function = $actionName = basename( $filePath, '.php' );
				if ( preg_match( '%^(.+?)[_-](\d+)$%', $actionName, $m ) ) {
					$actionName = $m[1];
					$priority = intval( $m[2] );
				} else {
					$priority = 10;
				}
				add_action( $actionName, self::PREFIX . str_replace( '-', '_', $function ), $priority, 99 ); // since we don't know at this point how many parameters each plugin expects, we make sure they will be provided with all of them (it's unlikely any developer will specify more than 99 parameters in a function)
			}

		// register filter handlers
		if ( is_dir( self::ROOT_DIR . '/filters' ) )
			foreach ( MBAI_Helper::safe_glob( self::ROOT_DIR . '/filters/*.php', MBAI_Helper::GLOB_RECURSE | MBAI_Helper::GLOB_PATH ) as $filePath ) {
				require_once $filePath;
				$function = $actionName = basename( $filePath, '.php' );
				if ( preg_match( '%^(.+?)[_-](\d+)$%', $actionName, $m ) ) {
					$actionName = $m[1];
					$priority = intval( $m[2] );
				} else {
					$priority = 10;
				}
				add_filter( $actionName, self::PREFIX . str_replace( '-', '_', $function ), $priority, 99 ); // since we don't know at this point how many parameters each plugin expects, we make sure they will be provided with all of them (it's unlikely any developer will specify more than 99 parameters in a function)
			}

		// register shortcodes handlers
		if ( is_dir( self::ROOT_DIR . '/shortcodes' ) )
			foreach ( MBAI_Helper::safe_glob( self::ROOT_DIR . '/shortcodes/*.php', MBAI_Helper::GLOB_RECURSE | MBAI_Helper::GLOB_PATH ) as $filePath ) {
				$tag = strtolower( str_replace( '/', '_', preg_replace( '%^' . preg_quote( self::ROOT_DIR . '/shortcodes/', '%' ) . '|\.php$%', '', $filePath ) ) );
				add_shortcode( $tag, array( $this, 'shortcodeDispatcher' ) );
			}

		// register admin page pre-dispatcher
		add_action( 'admin_init', array( $this, 'adminInit' ), 1 );
		add_action( 'init', array( $this, 'init' ), 10 );
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
		$locale = apply_filters( 'plugin_locale', get_locale(), 'mbai' );
		load_plugin_textdomain( 'mbai', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages' );
	}

	/**
	 * pre-dispatching logic for admin page controllers
	 */
	public function adminInit() {
		$input = new MBAI_Input();
		$page = strtolower( $input->getpost( 'page', '' ) );

		if ( preg_match( '%^' . preg_quote( str_replace( '_', '-', self::PREFIX ), '%' ) . '([\w-]+)$%', $page ) ) {
			$this->adminDispatcher( $page, strtolower( $input->getpost( 'action', 'index' ) ) );
		}
	}


	public function shortcodeDispatcher( array $args, string $content, string $tag ): string {

		$controllerName = self::PREFIX . preg_replace_callback( '%(^|_).%', array( $this, "replace_callback" ), $tag ); // capitalize first letters of class name parts and add prefix
		$controller = new $controllerName();
		
		if ( ! $controller instanceof MBAI_Controller ) {
			throw new Exception( "Shortcode `$tag` matches to a wrong controller type." );
		}
		ob_start();
		$controller->index( $args, $content );
		return ob_get_clean();
	}

	public function adminDispatcher( $page = '', $action = 'index' ) {
		static $buffer = NULL;
		static $buffer_callback = NULL;

		if ( '' === $page ) {
			if ( ! is_null( $buffer ) ) {
				echo '<div class="wrap">';
				echo $buffer;
				do_action( 'mbai_action_after' );
				echo '</div>';
			} elseif ( ! is_null( $buffer_callback ) ) {
				echo '<div class="wrap">';
				call_user_func( $buffer_callback );
				do_action( 'mbai_action_after' );
				echo '</div>';
			} else {
				throw new Exception( 'There is no previousely buffered content to display.' );
			}
		} else {
			$actionName = str_replace( '-', '_', $action );
			// capitalize prefix and first letters of class name parts
			$controllerName = preg_replace_callback( '%(^' . preg_quote( self::PREFIX, '%' ) . '|_).%', array( $this, "replace_callback" ), str_replace( '-', '_', $page ) );
			if ( method_exists( $controllerName, $actionName ) ) {

				if ( ! get_current_user_id() or ! current_user_can( PMXI_Plugin::$capabilities ) ) {
					// This nonce is not valid.
					die( 'Security check' );

				} else {

					$this->_admin_current_screen = (object) array(
						'id' => $controllerName,
						'base' => $controllerName,
						'action' => $actionName,
						'is_ajax' => isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) and strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) === 'xmlhttprequest',
						'is_network' => is_network_admin(),
						'is_user' => is_user_admin(),
					);
					add_filter( 'current_screen', array( $this, 'getAdminCurrentScreen' ) );

					$controller = new $controllerName();
					if ( ! $controller instanceof MBAI_Controller_Admin ) {
						throw new Exception( "Administration page `$page` matches to a wrong controller type." );
					}
					
					if ( $this->_admin_current_screen->is_ajax ) { // ajax request
						$controller->$action();
						do_action( 'mbai_action_after' );
						die(); // stop processing since we want to output only what controller is randered, nothing in addition
					} elseif ( ! $controller->isInline ) {
						ob_start();
						$controller->$action();
						$buffer = ob_get_clean();
					} else {
						$buffer_callback = array( $controller, $action );
					}
				}

			} else { // redirect to dashboard if requested page and/or action don't exist
				wp_redirect( admin_url() );
				die();
			}
		}
	}

	protected $_admin_current_screen = NULL;
	public function getAdminCurrentScreen() {
		return $this->_admin_current_screen;
	}

	public function replace_callback( $matches ) {
		return strtoupper( $matches[0] );
	}

	/**
	 * Autoloader
	 * It's assumed class name consists of prefix folloed by its name which in turn corresponds to location of source file
	 * if `_` symbols replaced by directory path separator. File name consists of prefix folloed by last part in class name (i.e.
	 * symbols after last `_` in class name)
	 * When class has prefix it's source is looked in `models`, `controllers`, `shortcodes` folders, otherwise it looked in `core` or `library` folder
	 *
	 * @param string $className
	 * @return mixed
	 */
	public function autoload( $className ) {

		if ( ! preg_match( '/MBAI/m', $className ) ) {
			return false;
		}

		$is_prefix = false;
		$filePath = str_replace( '_', '/', preg_replace( '%^' . preg_quote( self::PREFIX, '%' ) . '%', '', strtolower( $className ), 1, $is_prefix ) ) . '.php';
		if ( ! $is_prefix ) { // also check file with original letter case
			$filePathAlt = $className . '.php';
		}
		foreach ( $is_prefix ? array( 'models', 'controllers', 'shortcodes', 'classes' ) : array() as $subdir ) {
			$path = self::ROOT_DIR . '/' . $subdir . '/' . $filePath;
			if ( is_file( $path ) ) {
				require $path;
				return TRUE;
			}
			if ( ! $is_prefix ) {
				$pathAlt = self::ROOT_DIR . '/' . $subdir . '/' . $filePathAlt;
				if ( is_file( $pathAlt ) ) {
					require $pathAlt;
					return TRUE;
				}
			}
		}

		return FALSE;
	}

	/**
	 * Plugin activation logic
	 */
	public function activation() {
		// Uncaught exception doesn't prevent plugin from being activated, therefore replace it with fatal error so it does.
		set_exception_handler( function ($e) {
			trigger_error( $e->getMessage(), E_USER_ERROR );
		} );
	}

	/**
	 *  Init all available ACF fields.
	 */
	public static function get_available_acf_fields() {
		return ['name', 'email', 'phone'];
	}

	public static function get_default_import_options() {
		return [ 
			'acf' => [],
			'fields' => [],
			'is_multiple_field_value' => [],
			'multiple_value' => [],
			'fields_delimiter' => [],

			'is_update_acf' => 1,
			'update_acf_logic' => 'full_update',
			'acf_list' => [],
			'acf_only_list' => [],
			'acf_except_list' => [],
		];
	}
}


MBAI_Plugin::getInstance();
