<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://zetasolutionsonline.com
 * @since      1.0.0
 *
 * @package    ZetaAbandonedCart
 * @subpackage ZetaAbandonedCart/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    ZetaAbandonedCart
 * @subpackage ZetaAbandonedCart/public
 * @author     ZETA Solutions <info@zetasolutionsonline.com>
 */
class ZAC_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/zetaabandonedcart-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		$capture = get_option( 'zac_capture_enable', ZAC_DEFAULT_CAPTURE_ENABLE );
		if ( is_checkout() && 'on' === $capture ) {
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/zetaabandonedcart.js', array( 'jquery' ), $this->version, false );
			$zac_vars = array(
				'admin_ajax'  => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'zetasolutions_save_abandoned_cart' ),
				'checkout_id' => get_the_ID(),
			);
			wp_localize_script( $this->plugin_name, 'zac_vars', $zac_vars );
		}

	}

}
