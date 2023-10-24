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

defined( 'ABSPATH' ) || die;

require __DIR__ . '/vendor/autoload.php';

new MBWPAI\Main;