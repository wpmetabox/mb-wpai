<?php
/**
 * Plugin Name: MB WP All Import
 * Description: MB WP All Import
 * Author:      Metabox.io
 * Author URI:  https://metabox.io
 * Plugin URI:  https://metabox.io/plugins/mb-wpai/
 * Version:     0.0.1
 * Text Domain: mb-wpai
 * Domain Path: languages
 *
 * @package MB WPAI
 */

defined('ABSPATH') || wp_die();

require __DIR__ . '/vendor/autoload.php';

define('MBAI_ROOT_DIR', str_replace('\\', '/', dirname(__FILE__)));
define('MBAI_ROOT_URL', rtrim(plugin_dir_url(__FILE__), '/'));
define('MBAI_PREFIX', 'MBAI_');
define('MBAI_VERSION', '3.3.8');

final class MBAI_Plugin
{
    protected static $instance;
    public static $all_metabox_fields = [];
    const ROOT_DIR = MBAI_ROOT_DIR;
    const ROOT_URL = MBAI_ROOT_URL;
    const PREFIX = MBAI_PREFIX;
    const FILE = __FILE__;

    static public function getInstance()
    {
        if (self::$instance == NULL) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    static public function getEddName()
    {
        return 'Meta Box Add-On';
    }

    /**
     * Common logic for requestin plugin info fields
     */
    public function __call($method, $args)
    {
        if (preg_match('%^get(.+)%i', $method, $mtch)) {
            $info = get_plugin_data(self::FILE);
            if (isset($info[$mtch[1]])) {
                return $info[$mtch[1]];
            }
        }
        throw new Exception("Requested method " . get_class($this) . "::$method doesn't exist.");
    }

    /**
     * Get path to plagin dir relative to wordpress root
     * @param bool[optional] $noForwardSlash Whether path should be returned withot forwarding slash
     * @return string
     */
    public function getRelativePath($noForwardSlash = false)
    {
        $wp_root = str_replace('\\', '/', ABSPATH);
        return ($noForwardSlash ? '' : '/') . str_replace($wp_root, '', self::ROOT_DIR);
    }

    /**
     * Check whether plugin is activated as network one
     * @return bool
     */
    public function isNetwork()
    {
        if (!is_multisite())
            return false;

        $plugins = get_site_option('active_sitewide_plugins');
        if (isset($plugins[plugin_basename(self::FILE)]))
            return true;

        return false;
    }

    /**
     * Check whether permalinks is enabled
     * @return bool
     */
    public function isPermalinks()
    {
        global $wp_rewrite;

        return $wp_rewrite->using_permalinks();
    }

    /**
     * Return prefix for plugin database tables
     * @return string
     */
    public function getTablePrefix()
    {
        global $wpdb;
        return ($this->isNetwork() ? $wpdb->base_prefix : $wpdb->prefix) . self::PREFIX;
    }

    /**
     * Return prefix for wordpress database tables
     * @return string
     */
    public function getWPPrefix()
    {
        global $wpdb;
        return ($this->isNetwork() ? $wpdb->base_prefix : $wpdb->prefix);
    }

    /**
     * Class constructor containing dispatching logic
     * @param string $rootDir Plugin root dir
     * @param string $pluginFilePath Plugin main file
     */
    protected function __construct()
    {

        // create/update required database tables

        // register autoloading method
        spl_autoload_register(array($this, 'autoload'));

        // register helpers
        if (is_dir(self::ROOT_DIR . '/helpers'))
            foreach (MBAI_Helper::safe_glob(self::ROOT_DIR . '/helpers/*.php', MBAI_Helper::GLOB_RECURSE | MBAI_Helper::GLOB_PATH) as $filePath) {
                require_once $filePath;
            }

        register_activation_hook(self::FILE, array($this, 'activation'));

        // register action handlers
        if (is_dir(self::ROOT_DIR . '/actions')) if (is_dir(self::ROOT_DIR . '/actions'))
            foreach (MBAI_Helper::safe_glob(self::ROOT_DIR . '/actions/*.php', MBAI_Helper::GLOB_RECURSE | MBAI_Helper::GLOB_PATH) as $filePath) {
                require_once $filePath;
                $function = $actionName = basename($filePath, '.php');
                if (preg_match('%^(.+?)[_-](\d+)$%', $actionName, $m)) {
                    $actionName = $m[1];
                    $priority = intval($m[2]);
                } else {
                    $priority = 10;
                }
                add_action($actionName, self::PREFIX . str_replace('-', '_', $function), $priority, 99); // since we don't know at this point how many parameters each plugin expects, we make sure they will be provided with all of them (it's unlikely any developer will specify more than 99 parameters in a function)
            }

        // register filter handlers
        if (is_dir(self::ROOT_DIR . '/filters'))
            foreach (MBAI_Helper::safe_glob(self::ROOT_DIR . '/filters/*.php', MBAI_Helper::GLOB_RECURSE | MBAI_Helper::GLOB_PATH) as $filePath) {
                require_once $filePath;
                $function = $actionName = basename($filePath, '.php');
                if (preg_match('%^(.+?)[_-](\d+)$%', $actionName, $m)) {
                    $actionName = $m[1];
                    $priority = intval($m[2]);
                } else {
                    $priority = 10;
                }
                add_filter($actionName, self::PREFIX . str_replace('-', '_', $function), $priority, 99); // since we don't know at this point how many parameters each plugin expects, we make sure they will be provided with all of them (it's unlikely any developer will specify more than 99 parameters in a function)
            }

        // register shortcodes handlers
        if (is_dir(self::ROOT_DIR . '/shortcodes'))
            foreach (MBAI_Helper::safe_glob(self::ROOT_DIR . '/shortcodes/*.php', MBAI_Helper::GLOB_RECURSE | MBAI_Helper::GLOB_PATH) as $filePath) {
                $tag = strtolower(str_replace('/', '_', preg_replace('%^' . preg_quote(self::ROOT_DIR . '/shortcodes/', '%') . '|\.php$%', '', $filePath)));
                add_shortcode($tag, array($this, 'shortcodeDispatcher'));
            }

        // register admin page pre-dispatcher
        add_action('admin_init', array($this, 'adminInit'), 1);
        add_action('init', array($this, 'init'), 10);
    }

    public function init()
    {
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
    public function load_plugin_textdomain()
    {
        $locale = apply_filters('plugin_locale', get_locale(), 'mbai');
        load_plugin_textdomain('mbai', false, dirname(plugin_basename(__FILE__)) . '/i18n/languages');
    }

    /**
     * pre-dispatching logic for admin page controllers
     */
    public function adminInit()
    {
        $input = new MBAI_Input();
        $page = strtolower($input->getpost('page', ''));
        if (preg_match('%^' . preg_quote(str_replace('_', '-', self::PREFIX), '%') . '([\w-]+)$%', $page)) {
            $this->adminDispatcher($page, strtolower($input->getpost('action', 'index')));
        }
    }

    /**
     * Dispatch shorttag: create corresponding controller instance and call its index method
     * @param array $args Shortcode tag attributes
     * @param string $content Shortcode tag content
     * @param string $tag Shortcode tag name which is being dispatched
     * @return string
     */
    public function shortcodeDispatcher($args, $content, $tag)
    {

        $controllerName = self::PREFIX . preg_replace_callback('%(^|_).%', array($this, "replace_callback"), $tag); // capitalize first letters of class name parts and add prefix
        $controller = new $controllerName();
        if (!$controller instanceof PMAI_Controller) {
            throw new Exception("Shortcode `$tag` matches to a wrong controller type.");
        }
        ob_start();
        $controller->index($args, $content);
        return ob_get_clean();
    }

    /**
     * Dispatch admin page: call corresponding controller based on get parameter `page`
     * The method is called twice: 1st time as handler `parse_header` action and then as admin menu item handler
     * @param string[optional] $page When $page set to empty string ealier buffered content is outputted, otherwise controller is called based on $page value
     */
    public function adminDispatcher($page = '', $action = 'index')
    {
        static $buffer = NULL;
        static $buffer_callback = NULL;
        if ('' === $page) {
            if (!is_null($buffer)) {
                echo '<div class="wrap">';
                echo $buffer;
                do_action('pmai_action_after');
                echo '</div>';
            } elseif (!is_null($buffer_callback)) {
                echo '<div class="wrap">';
                call_user_func($buffer_callback);
                do_action('pmai_action_after');
                echo '</div>';
            } else {
                throw new Exception('There is no previousely buffered content to display.');
            }
        } else {
            $actionName = str_replace('-', '_', $action);
            // capitalize prefix and first letters of class name parts
            $controllerName = preg_replace_callback('%(^' . preg_quote(self::PREFIX, '%') . '|_).%', array($this, "replace_callback"), str_replace('-', '_', $page));
            if (method_exists($controllerName, $actionName)) {

                if (!get_current_user_id() or !current_user_can(PMXI_Plugin::$capabilities)) {
                    // This nonce is not valid.
                    die('Security check');

                } else {

                    $this->_admin_current_screen = (object) array(
                        'id' => $controllerName,
                        'base' => $controllerName,
                        'action' => $actionName,
                        'is_ajax' => isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest',
                        'is_network' => is_network_admin(),
                        'is_user' => is_user_admin(),
                    );
                    add_filter('current_screen', array($this, 'getAdminCurrentScreen'));

                    $controller = new $controllerName();
                    if (!$controller instanceof PMAI_Controller_Admin) {
                        throw new Exception("Administration page `$page` matches to a wrong controller type.");
                    }

                    if ($this->_admin_current_screen->is_ajax) { // ajax request
                        $controller->$action();
                        do_action('pmai_action_after');
                        die(); // stop processing since we want to output only what controller is randered, nothing in addition
                    } elseif (!$controller->isInline) {
                        ob_start();
                        $controller->$action();
                        $buffer = ob_get_clean();
                    } else {
                        $buffer_callback = array($controller, $action);
                    }
                }

            } else { // redirect to dashboard if requested page and/or action don't exist
                wp_redirect(admin_url());
                die();
            }
        }
    }

    protected $_admin_current_screen = NULL;
    public function getAdminCurrentScreen()
    {
        return $this->_admin_current_screen;
    }

    public function replace_callback($matches)
    {
        return strtoupper($matches[0]);
    }

    /**
     * Autoloader
     * It's assumed class name consists of prefix folloed by its name which in turn corresponds to location of source file
     * if `_` symbols replaced by directory path separator. File name consists of prefix folloed by last part in class name (i.e.
     * symbols after last `_` in class name)
     * When class has prefix it's source is looked in `models`, `controllers`, `shortcodes` folders, otherwise it looked in `core` or `library` folder
     *
     * @param string $className
     * @return bool
     */
    public function autoload($className)
    {

        if (!preg_match('/PMAI/m', $className)) {
            return false;
        }

        $is_prefix = false;
        $filePath = str_replace('_', '/', preg_replace('%^' . preg_quote(self::PREFIX, '%') . '%', '', strtolower($className), 1, $is_prefix)) . '.php';
        if (!$is_prefix) { // also check file with original letter case
            $filePathAlt = $className . '.php';
        }
        foreach ($is_prefix ? array('models', 'controllers', 'shortcodes', 'classes') : array() as $subdir) {
            $path = self::ROOT_DIR . '/' . $subdir . '/' . $filePath;
            if (is_file($path)) {
                require $path;
                return TRUE;
            }
            if (!$is_prefix) {
                $pathAlt = self::ROOT_DIR . '/' . $subdir . '/' . $filePathAlt;
                if (is_file($pathAlt)) {
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
    public function activation()
    {
        // Uncaught exception doesn't prevent plugin from being activated, therefore replace it with fatal error so it does.
        set_exception_handler(function ($e) {
            trigger_error($e->getMessage(), E_USER_ERROR); });
    }

    /**
     *  Init all available Meta Box fields.
     */
    public static function get_available_metabox_fields()
    {
        if (empty(self::$all_metabox_fields)) {
            // Implement logic to get all available Meta Box fields.
        }

        return self::$all_metabox_fields;
    }

    /**
     * Method returns default import options, main utility of the method is to avoid warnings when new
     * option is introduced but already registered imports don't have it
     */
    public static function get_default_import_options()
    {
        return array(
            'metabox' => array(),
            'fields' => array(),
            'is_multiple_field_value' => array(),
            'multiple_value' => array(),
            'fields_delimiter' => array(),

            'is_update_metabox' => 1,
            'update_metabox_logic' => 'full_update',
            'metabox_list' => array(),
            'metabox_only_list' => array(),
            'metabox_except_list' => array()
        );
    }
}

PMAI_Plugin::getInstance();