<?php
/**
 * Define constant, tgmpa and load text domain.
 *
 * @package MB WPAI.
 */

namespace MBWPAI;

/**
 * Class Plugin
 */
class Plugin {
	/**
	 * Plugin base file.
	 *
	 * @var string
	 */
	protected $file;

	/**
	 * Query instance.
	 *
	 * @var string
	 */
	public $query = null;

	/**
	 * Plugin constructor.
	 *
	 * @param string $file Plugin base file.
	 */
	public function __construct( $file ) {
		$this->file = $file;
		$this->define_constants();
		$this->init_hooks();
    }
    
    /**
	 * Define plugin const variable.
	 */
	protected function define_constants() {
		define( 'MB_WPAI_FILE', $this->file );
		define( 'MB_WPAI_DIR', plugin_dir_path( $this->file ) );
		define( 'MB_WPAI_URL', plugin_dir_url( $this->file ) );
		define( 'MB_WPAI_BASENAME', plugin_basename( $this->file ) );
		define( 'MB_WPAI_VERSION', '1.0.0' );
	}

	/**
	 * Hook when init plugin.
	 */
	protected function init_hooks() {
		add_action( 'init', [ $this, 'init' ], 0 );
		add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );
		add_action( 'tgmpa_register', [ $this, 'register_plugins' ] );
	}

	public function init() {
		$this->load_plugin_textdomain();
	}

	/**
	 * Load plugin text domain.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'mb-wpai', false, basename( basename( __DIR__ ) ) . '/languages' );
	}

	/**
	 * Add plugin extra links on the plugin screen.
	 *
	 * @param array  $links List of plugin actions.
	 * @param string $file  Plugin file.
	 *
	 * @return array
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( MB_WPAI_BASENAME !== $file ) {
			return $links;
		}

		$links[] = '<a href="' . esc_url( '' ) . '" title="' . esc_attr__( 'View Documentation', 'mb-wpai' ) . '">' . esc_html__( 'Documentation', 'mb-wpai' ) . '</a>';

		return $links;
	}

	/**
	 * Install required plugin on activation.
	 */
	public function register_plugins() {
		$plugins = [
			[
				'name'     => 'Meta Box',
				'slug'     => 'meta-box',
				'required' => true,
			],
			[
				'name'     => 'WP All Import',
				'slug'     => 'wp-all-import',
				'required' => true,
			],
		];
		$config  = [
			'id'          => 'mb-wpai',
			'menu'        => 'tgmpa-install-plugins',
			'parent_slug' => 'plugins.php',
			'capability'  => 'install_plugins',
			'strings'     => [
				/* translators: 1: plugin name(s). */
				'notice_can_install_required' => _n_noop(
					'The WPAI Addon for MetaBox plugin requires the following plugin: %1$s.',
					'The WPAI Addon for MetaBox plugin requires the following plugins: %1$s.',
					'mb-wpai'
				),
			],
		];

		tgmpa( $plugins, $config );
	}
}
