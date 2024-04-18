<?php
/**
 * Plugin database class
 *
 * @link       https://zetasolutionsonline.com
 * @since      1.0.0
 *
 * @package    ZetaAbandonedCart
 * @subpackage ZetaAbandonedCart/includes
 */

/**
 * Plugin database class.
 *
 * This class defines all code necessary to perform database queries.
 *
 * @since      1.0.0
 * @package    ZetaAbandonedCart
 * @subpackage ZetaAbandonedCart/includes
 * @author     ZETA Solutions <info@zetasolutionsonline.com>
 */
class ZAC_DB {
	/**
	 * Member Variable.
	 *
	 * @var object instance.
	 */
	private static $instance;

	/**
	 * Member Variable.
	 *
	 * @var object WordPress database.
	 */
	private object $wpdb;

	/**
	 *  Class Constructor.
	 *  defined database's table names.
	 *
	 *  @since    1.0.0
	 */
	public function __construct() {
		$this->wpdb = $GLOBALS['wpdb'];
		// required for dbDelta method.
		include_once ABSPATH . 'wp-admin/includes/upgrade.php';
	}

	/**
	 *  Class Initiator.
	 *
	 *  @since    1.0.0
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Database Structure Creation.
	 *
	 * @since    1.0.0
	 */
	public function create_database() {
		$this->wpdb->hide_errors();
		$carts_table     = $this->wpdb->prefix . ZAC_CARTS_TABLE;
		$charset_collate = $this->wpdb->get_charset_collate();
        $table_check = ($this->wpdb->get_var( "SHOW TABLES LIKE '{$carts_table}'" ) === null); //phpcs:ignore

		if ( $table_check ) {
			$sql = "CREATE TABLE $carts_table (
	            `id` BIGINT(19) NOT NULL AUTO_INCREMENT,
	            `checkout_id` INT(10) NOT NULL,
	            `email` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_520_ci',
	            `cart` LONGTEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_520_ci',
	            `total` DECIMAL(10,2) NULL DEFAULT NULL,
	            `session_id` VARCHAR(60) NOT NULL COLLATE 'utf8mb4_unicode_520_ci',
	            `form_data` LONGTEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_520_ci',
	            `order_status` ENUM('raw','abandoned','recovered','lost') NULL DEFAULT 'raw' COLLATE 'utf8mb4_unicode_520_ci',
	            `coupon_code` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_520_ci',
	            `timestamp` DATETIME NULL DEFAULT NULL,
	            `follow_up` TINYINT(3) NULL DEFAULT '0',
	            `update_timestamp` DATETIME NULL DEFAULT NULL,
	            PRIMARY KEY (`id`, `session_id`),
	            UNIQUE INDEX `session_id_UNIQUE` (`session_id`)
		    ) $charset_collate;\n";

			dbDelta( $sql );
		}

		$email_templates_table = $this->wpdb->prefix . ZAC_EMAIL_TEMPLATES_TABLE;
        $table_check = ($this->wpdb->get_var( "SHOW TABLES LIKE '{$email_templates_table}'" ) === null); //phpcs:ignore

		if ( $table_check ) {
			$sql = "CREATE TABLE $email_templates_table (
			    `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `title` text NOT NULL,
                `subject` text NOT NULL,
                `body` mediumtext NOT NULL,
                `enabled` tinyint(1) NOT NULL DEFAULT '0',
                `interval` varchar(50) NOT NULL DEFAULT '1 Day',
                PRIMARY KEY (`id`)
		    ) $charset_collate;\n";

			dbDelta( $sql );
		}
	}
	/**
	 * Get all the carts.
	 *
	 * @since 1.0.0
	 */
	public function get_all_carts() {
		$query = $this->wpdb->prepare( 'SELECT * FROM ' . $this->wpdb->prefix . ZAC_CARTS_TABLE ); // phpcs:ignore
		return $this->wpdb->get_results( $query ); // phpcs:ignore
	}
	/**
	 * Get follow ups.
	 *
	 * @param string $email email to find.
	 * @since 1.0.0
	 */
	public function get_follow_up( $email ) {
		$query = $this->wpdb->prepare( 'SELECT follow_up FROM ' . $this->wpdb->prefix . ZAC_CARTS_TABLE . ' WHERE email = %s', $email ); // phpcs:ignore
		return $this->wpdb->get_results( $query ); // phpcs:ignore
	}
	/**
	 * Get carts by Status.
	 *
	 * @param string $status cart status.
	 * @since 1.0.0
	 */
	public function get_carts_by_status( $status ) {
		$query = $this->wpdb->prepare( 'SELECT * FROM ' . $this->wpdb->prefix . ZAC_CARTS_TABLE . ' WHERE order_status = %s', $status ); // phpcs:ignore
		return $this->wpdb->get_results( $query ); // phpcs:ignore
	}
	/**
	 * Get data for charts.
	 *
	 * @param string $status cart status.
	 * @since 1.0.0
	 */
	public function get_data_for_chart( $status ) {
		$query = $this->wpdb->prepare( 'SELECT DATE(timestamp) AS date, COUNT(order_status) AS orders FROM ' . $this->wpdb->prefix . ZAC_CARTS_TABLE . ' WHERE order_status = %s AND timestamp BETWEEN (CURDATE() - INTERVAL 1 WEEK) AND CURDATE() GROUP BY DATE(timestamp)', $status ); // phpcs:ignore
		return $this->wpdb->get_results( $query ); // phpcs:ignore
	}
	/**
	 * Get the checkout details by session id.
	 *
	 * @param string $zac_session_id checkout page session id.
	 * @since 1.0.0
	 */
	public function get_checkout_by_session_id( string $zac_session_id ) {
		$query = $this->wpdb->prepare( 'SELECT * FROM ' . $this->wpdb->prefix . ZAC_CARTS_TABLE . ' WHERE session_id = %s', $zac_session_id ); // phpcs:ignore
		return $this->wpdb->get_row( $query ); // phpcs:ignore
	}
	/**
	 * Get the checkout details by email id.
	 *
	 * @param string $email user email id.
	 * @since 1.0.0
	 */
	public function get_checkout_by_email( string $email ) {
		$query = $this->wpdb->prepare( 'SELECT * FROM ' . $this->wpdb->prefix . ZAC_CARTS_TABLE . ' WHERE email = %s', $email ); // phpcs:ignore
		return $this->wpdb->get_row( $query ); // phpcs:ignore
	}
	/**
	 * Insert data into database.
	 *
	 * @param string $table table name without prefix.
	 * @param array  $data associative array with keys as columns.
	 * @since 1.0.0
	 */
	public function insert( string $table, array $data ) {
		return $this->wpdb->insert( $this->wpdb->prefix . $table, $data );
	}
	/**
	 * Update data in database.
	 *
	 * @param string $table table name without prefix.
	 * @param array  $data associative array with keys as columns need to be updated.
	 * @param array  $where associative array of one element of condition column & value.
	 * @since 1.0.0
	 */
	public function update( string $table, array $data, array $where ) {
		return $this->wpdb->update( $this->wpdb->prefix . $table, $data, $where );
	}

	/**
	 * Update order status in database.
	 *
	 * @since 1.0.0
	 */
	public function update_order_status() {
		return $this->wpdb->update(
			$this->wpdb->prefix . ZAC_CARTS_TABLE,
			array(
				'order_status'     => 'abandoned',
				'update_timestamp' => current_time( 'Y-m-d H:i:s' ),
			),
			array( 'order_status' => null )
		);
	}
	/**
	 * Delete a cart record from database.
	 *
	 * @param int $id cart ID.
	 * @since 1.0.0
	 */
	public function delete( int $id ) {
		return $this->wpdb->delete( $this->wpdb->prefix . ZAC_CARTS_TABLE, array( 'id' => $id ), array( '%d' ) ); // phpcs:ignore
	}
	/**
	 * Delete a cart record from database.
	 *
	 * @param string $session_id session id.
	 * @since 1.0.0
	 */
	public function delete_by_session_id( string $session_id ) {
		return $this->wpdb->delete( $this->wpdb->prefix . ZAC_CARTS_TABLE, array( 'session_id' => $session_id ), array( '%s' ) ); // phpcs:ignore
	}
	/**
	 * Delete / drop ZAC databases.
	 *
	 * @since 1.0.0
	 */
	public function delete_database() {
		$this->wpdb->query( 'DROP TABLE IF EXISTS ' . $this->wpdb->prefix . ZAC_CARTS_TABLE ); // phpcs:ignore
		$this->wpdb->query( 'DROP TABLE IF EXISTS ' . $this->wpdb->prefix . ZAC_EMAIL_TEMPLATES_TABLE ); // phpcs:ignore
	}
	/**
	 * Delete ZAC settings.
	 *
	 * @since 1.0.0
	 */
	public function delete_settings() {
		$this->wpdb->query( "DELETE FROM wp_options WHERE option_name LIKE 'zac_%'" );
	}
}
