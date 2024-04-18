<?php
/**
 * CRON Jobs, Scheduled Actions.
 *
 * @link       https://zetasolutionsonline.com
 * @since      1.0.0
 *
 * Maintain all the scheduled actions in this class.
 *
 * @package    ZetaAbandonedCart
 * @subpackage ZetaAbandonedCart/includes
 * @author     ZETA Solutions <info@zetasolutionsonline.com>
 */

/**
 * Define all the scheduled actions.
 *
 * Create scheduled action that going to use in the plugin.
 *
 * @since      1.0.0
 * @package    ZetaAbandonedCart
 * @subpackage ZetaAbandonedCart/includes
 * @author     ZETA Solutions <info@zetasolutionsonline.com>
 */
class ZAC_Schedules {
	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;
	/**
	 * Initialize the Schedules.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter( 'cron_schedules', array( $this, 'zac_cron_schedules' ) ); // phpcs:ignore
	}
	/**
	 * Class Initiator - Singlton Instance.
	 *
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * Custom Cron Schedule.
	 *
	 * @param array $schedules schedules.
	 * @return mixed
	 * @since 1.0.0
	 */
	public function zac_cron_schedules( $schedules ) {

		$zac_status_update_interval = get_option( 'zac_status_update_interval', ZAC_DEFAULT_STATUS_UPDATE_INTERVAL );

		$schedules['zac_status_update_interval'] = array(
			'interval' => intval( $zac_status_update_interval ) * MINUTE_IN_SECONDS,
			'display'  => 'ZAC Status Update Interval',
		);

		$zac_follow_up_interval = get_option( 'zac_follow_up_interval', ZAC_DEFAULT_FOLLOW_UP_INTERVAL );

		$schedules['zac_follow_up_interval'] = array(
			// 'interval' => intval( $zac_follow_up_interval ) * MINUTE_IN_SECONDS,
			// FIXME it should be HOUR in production.
			'interval' => intval($zac_follow_up_interval) * HOUR_IN_SECONDS,
			'display'  => 'ZAC Follow Up Interval',
		);

		return $schedules;
	}

	/**
	 * Schedule Events.
	 *
	 * @since 1.0.0
	 */
	public function zac_schedculed_events() {
		// zac_scheduled_status_update.
		if ( ! wp_next_scheduled( 'zac_scheduled_status_update' ) ) {
			wp_schedule_event( time(), 'zac_status_update_interval', 'zac_scheduled_status_update' );
		}

		// zac_scheduled_follow_up.
		if ( ! wp_next_scheduled( 'zac_scheduled_follow_up' ) ) {
			wp_schedule_event( time(), 'zac_follow_up_interval', 'zac_scheduled_follow_up' );
		}
	}
}
