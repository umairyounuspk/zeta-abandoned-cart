<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://zetasolutionsonline.com
 * @since      1.0.0
 *
 * @package    ZetaAbandonedCart
 * @subpackage ZetaAbandonedCart/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    ZetaAbandonedCart
 * @subpackage ZetaAbandonedCart/includes
 * @author     ZETA Solutions <info@zetasolutionsonline.com>
 */
class ZAC_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( 'zac_cron_schedule_action' );
	}

}
