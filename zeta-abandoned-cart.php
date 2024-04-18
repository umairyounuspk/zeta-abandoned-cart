<?php
/**
 *
 * @link              https://zetasolutionsonline.com
 * @since             1.0.0
 * @package           ZetaAbandonedCart
 *
 * @wordpress-plugin
 * Plugin Name:       ZETA Abandoned Cart
 * Plugin URI:        https://zetasolutionsonline.com/wordpress-plugins/zeta-abandoned-cart
 * Description:       Zeta Abandoned Cart Plug-in has been equipped with all the features that you need for having an abandoned cart strategy to improve conversion rate.
 * Version:           1.0.4
 * Author:            ZETA Solutions
 * Author URI:        https://zetasolutionsonline.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       zeta-abandoned-cart
 * Domain Path:       /languages
 * Requires at least: 4.7
 * Tested up to: 6.1
 * WC requires at least: 4.0.0
 * WC tested up to: 7.0
 * Requires PHP: 7.2
 * Tags: abandoned cart, cart abandonment, follow up emails, cart recovery, order recovery
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Defination of all the Constants to be used in plugin codebase.
 */
define( 'ZAC_VERSION', '1.0.4' );
define( 'ZAC_CARTS_TABLE', 'zac_abandoned_carts' );
define( 'ZAC_EMAIL_TEMPLATES_TABLE', 'zac_email_templates' );
define( 'ZAC_DEFAULT_CAPTURE_ENABLE', 'on' );
define( 'ZAC_DEFAULT_FROM_NAME', get_bloginfo( 'name' ) );
define( 'ZAC_DEFAULT_EMAIL_SUBJECT', 'Need help?' );
define( 'ZAC_DEFAULT_EMAIL_BODY', '<p>Hi {customer_name}!</p><p>I am {email_from_name}, and I help handle customer issues at {site_title}.</p><p>I just noticed that you tried to make a purchase, but unfortunately, there was some trouble. Is there anything I can do to help?</p><p>You should be able to complete your checkout in less than a minute:</p><p><a href="http://{checkout_link}" target="_blank" rel="noopener">Click here to continue your purchase</a></p><p>Thanks!<br />{email_from_name}<br />{site_title}</p>' );
define( 'ZAC_DEFAULT_FROM_EMAIL', get_bloginfo( 'admin_email' ) );
define( 'ZAC_DEFAULT_STATUS_UPDATE_INTERVAL', 15 );
define( 'ZAC_DEFAULT_FOLLOW_UP_INTERVAL', 10 );
define( 'ZAC_DEFAULT_COUPON_ENABLE', 'on' );
define( 'ZAC_DEFAULT_COUPON_DISCOUNT', 10 );
define( 'ZAC_DEFAULT_DISCOUNT_TYPE', 'fixed_cart' );
define( 'ZAC_DEFAULT_GDPR_ENABLE', 'on' );
define( 'ZAC_DEFAULT_GDPR_MESSAGE', 'Your personal & cart information is being collected for marketing purpose.' );
define( 'ZAC_DEFAULT_GDPR_CONSENT', 'I am agree to share my information.' );
define( 'ZAC_DEFAULT_DELETE_DB', '' );
define( 'ZAC_DEFAULT_NOTIFICATION_ENABLE', 'on' );
define( 'ZAC_DEFAULT_NOTIFICATION_EMAIL', get_bloginfo( 'admin_email' ) );
define( 'ZAC_DEFAULT_TEST_EMAIL', get_bloginfo( 'admin_email' ) );


/**
 * The code that runs during plugin activation.
 */
function activate_zac() {
	ZAC_DB::instance()->create_database();
}

register_activation_hook( __FILE__, 'activate_zac' );

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_zac() {
	wp_clear_scheduled_hook( 'zac_scheduled_status_update' );
	wp_clear_scheduled_hook( 'zac_scheduled_follow_up' );
}

register_deactivation_hook( __FILE__, 'deactivate_zac' );

/**
 * The code that runs during plugin uninstallation.
 */
function uninstall_zac() {
	$delete_db = get_option( 'zac_delete_db', ZAC_DEFAULT_DELETE_DB );

	if ( 'on' !== $delete_db ) {
		return;
	}

	ZAC_DB::instance()->delete_database();
	ZAC_DB::instance()->delete_settings();
}

register_uninstall_hook( __FILE__, 'uninstall_zac' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-zeta-abandoned-cart.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_zac() {

	$plugin = new Zeta_Abandoned_Cart();
	$plugin->run();

}
run_zac();
