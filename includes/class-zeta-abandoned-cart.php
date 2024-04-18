<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://zetasolutionsonline.com
 * @since      1.0.0
 *
 * @package    ZetaAbandonedCart
 * @subpackage ZetaAbandonedCart/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    ZetaAbandonedCart
 * @subpackage ZetaAbandonedCart/includes
 * @author     ZETA Solutions <info@zetasolutionsonline.com>
 */
class Zeta_Abandoned_Cart {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      ZAC_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'ZAC_VERSION' ) ) {
			$this->version = ZAC_VERSION;
		} else {
			$this->version = '1.0.3';
		}
		$this->plugin_name = 'zeta-abandoned-cart';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - ZAC_Loader. Orchestrates the hooks of the plugin.
	 * - ZAC_i18n. Defines internationalization functionality.
	 * - ZAC_Admin. Defines all hooks for the admin area.
	 * - ZAC_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-zac-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-zac-i18n.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-zac-db.php';

		if ( ! class_exists( 'WP_List_Table' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
		}

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-zac-list-table.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-zac-admin.php';

		/**
		 * The class responsible for defining all the schedules & scheduled event hooks.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-zac-schedules.php';
		ZAC_Schedules::instance()->zac_schedculed_events();

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-zac-public.php';

		$this->loader = new ZAC_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the ZAC_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new ZAC_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new ZAC_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_init', $plugin_admin, 'requirements_check' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'notices' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'page_init' );

		$this->loader->add_action( 'wp_ajax_save_zac_data', $plugin_admin, 'save_zac_data' );
		$this->loader->add_action( 'wp_ajax_nopriv_save_zac_data', $plugin_admin, 'save_zac_data' );
		$this->loader->add_action( 'load-toplevel_page_zac-dashboard', $plugin_admin, 'screen_option' );

		// Schedules & Scheduled Events.
		$this->loader->add_action( 'zac_scheduled_status_update', $plugin_admin, 'update_order_status' );
		$this->loader->add_action( 'zac_scheduled_follow_up', $plugin_admin, 'zac_send_scheduled_follow_up_emails' );

		$this->loader->add_action( 'wp', $plugin_admin, 'zac_restore_abandoned_cart' );
		$this->loader->add_filter( 'woocommerce_after_checkout_billing_form', $plugin_admin, 'add_gdpr_message' );
		$this->loader->add_filter( 'set-screen-option', $plugin_admin, 'set_screen', 10, 3 );

		$this->loader->add_action( 'wp_ajax_zac_test_email', $plugin_admin, 'zac_test_email' );

		// Delete the stored cart abandonment data once order gets created.
		$this->loader->add_action( 'woocommerce_thankyou', $plugin_admin, 'zac_order_process' );

		// $this->loader->add_action( 'woocommerce_order_status_changed', array( $this, 'wcf_ca_update_order_status' ), 999, 3 );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new ZAC_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    ZAC_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
