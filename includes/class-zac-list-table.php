<?php
/**
 * Tables class
 *
 * @link       https://zetasolutionsonline.com
 * @since      1.0.0
 *
 * @package    ZetaAbandonedCart
 * @subpackage ZetaAbandonedCart/includes
 */

/**
 * Tables class.
 *
 * This class defines all data tables used inside admin area.
 *
 * @since      1.0.0
 * @package    ZetaAbandonedCart
 * @subpackage ZetaAbandonedCart/includes
 * @author     ZETA Solutions <info@zetasolutionsonline.com>
 */
class ZAC_List_Table extends WP_List_Table {
	/**
	 * Constructor
	 */
	public function __construct() {

		parent::__construct(
			array(
				'singular' => __( 'Cart', 'zeta-abandoned-cart' ), // singular name of the listed records.
				'plural'   => __( 'Carts', 'zeta-abandoned-cart' ), // plural name of the listed records.
				'ajax'     => false, // does this table support ajax?
			)
		);

	}

	/**
	 * Retrieve all the carts data from the database
	 *
	 * @param int $per_page items per page.
	 * @param int $page_number page number start from.
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public static function get_carts( $per_page = 5, $page_number = 1 ) {

		global $wpdb;

		$sql     = 'SELECT * FROM ' . $wpdb->prefix . ZAC_CARTS_TABLE;
		$orderby = filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_STRING );
		$order   = filter_input( INPUT_GET, 'order', FILTER_SANITIZE_STRING );

		if ( ! empty( $orderby ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $orderby );
			$sql .= ! empty( $order ) ? ' ' . esc_sql( $order ) : ' ASC';
		}

		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

		return $wpdb->get_results( $sql, 'ARRAY_A' ); // phpcs:ignore
	}

	/**
	 * Delete a cart record from database.
	 *
	 * @param int $id cart ID.
	 * @since 1.0.0
	 */
	public function delete_cart( int $id ) {
		global $wpdb;

		return $wpdb->delete(
			$wpdb->prefix . ZAC_CARTS_TABLE,
			array( 'ID' => $id ),
			array( '%d' )
		);
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;

		$sql = 'SELECT COUNT(*) FROM ' . $wpdb->prefix . ZAC_CARTS_TABLE;

		return $wpdb->get_var( $sql ); // phpcs:ignore
	}


	/**
	 * Text displayed when no cart data is available
	 */
	public function no_items() {
		esc_html_e( 'No abandoned carts avaliable.', 'zeta-abandoned-cart' );
	}

	/**
	 * Render date column
	 *
	 * @param  object $item - row (key, value array).
	 * @return HTML
	 */
	public function column_timestamp( $item ) {
		return esc_html( gmdate( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $item['timestamp'] ) ) );
	}

	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array  $item items array.
	 * @param string $column_name column name.
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		return esc_html( $item[ $column_name ] );
	}

	/**
	 * Render the bulk edit checkbox.
	 *
	 * @param array $item items array.
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="bulk-delete[]" value="%s" />', esc_attr( $item['id'] ) );
	}


	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data.
	 *
	 * @return string
	 */
	public function column_name( $item ) {

		$form_data = unserialize( $item['form_data'] ); // phpcs:ignore
		$full_name = '';

		if ( isset( $form_data['billing_first_name'] ) ) {
			$full_name .= $form_data['billing_first_name'];
			if ( isset( $form_data['billing_last_name'] ) ) {
				$full_name .= ' ' . $form_data['billing_last_name'];
			}
		}

		$full_name    = '<strong>' . $full_name . '</strong>';
			$view_url = add_query_arg(
				array(
					'page'       => filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING ),
					'action'     => 'view-cart',
					'session_id' => sanitize_text_field( $item['session_id'] ),
				),
				admin_url( '/admin.php' )
			);

		$delete_url = add_query_arg(
			array(
				'page'   => filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING ),
				'action' => 'delete-cart',
				'id'     => absint( $item['id'] ),
				'nonce'  => wp_create_nonce( 'zac_delete_a_cart' ),
			),
			admin_url( '/admin.php' )
		);

		$actions = array(
			'view'   => sprintf( '<a href="%s">%s</a>', esc_url( $view_url ), __( 'View', 'zeta-abandoned-cart' ) ),
			'delete' => sprintf( '<a onclick="return confirm(\'Are you sure to delete this order?\');" href="%s">%s</a>', esc_url( $delete_url ), __( 'Delete', 'zeta-abandoned-cart' ) ),
		);

		return $full_name . $this->row_actions( $actions );
	}


	/**
	 *  Associative array of columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'           => '<input type="checkbox" />',
			'name'         => __( 'Name', 'zeta-abandoned-cart' ),
			'email'        => __( 'Email', 'zeta-abandoned-cart' ),
			'total'        => __( 'Cart Amount', 'zeta-abandoned-cart' ),
			'order_status' => __( 'Status', 'zeta-abandoned-cart' ),
			'timestamp'    => __( 'Time', 'zeta-abandoned-cart' ),
		);
		return $columns;
	}


	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'timestamp' => array( 'timestamp', true ),
		);

		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => 'Delete',
		);

		return $actions;
	}


	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$this->_column_headers = $this->get_column_info();

		// Process bulk action.
		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'carts_per_page', 5 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args(
			array(
				'total_items' => $total_items, // WE have to calculate the total number of items.
				'per_page'    => $per_page, // WE have to determine how many items to show on a page.
			)
		);

		$this->items = self::get_carts( $per_page, $current_page );
	}
	/**
	 * Process action in bulk.
	 */
	public function process_bulk_action() {

		// Detect when a bulk action is being triggered.
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING );

			if ( ! wp_verify_nonce( $nonce, 'zac_delete_a_cart' ) ) {
				die( 'Go get a life script kiddies' );
			} else {

				$zetaabandonedcart = filter_input( INPUT_GET, 'zetaabandonedcart', FILTER_SANITIZE_NUMBER_INT );
				$this->delete_cart( esc_sql( $zetaabandonedcart ) );

				// esc_url_raw() is used to prevent converting ampersand in url to "#038;".
				// add_query_arg() return the current url.
				wp_redirect( esc_url_raw( add_query_arg() ) );
				exit;
			}
		}

		// If the delete bulk action is triggered.
		if ( ( isset( $_POST['action'] ) && 'bulk-delete' === $_POST['action'] )
			|| ( isset( $_POST['action2'] ) && 'bulk-delete' === $_POST['action2'] )
		) {

			$delete_ids = array_map('absint', $_POST['bulk-delete']); // phpcs:ignore
			
			// loop over the array of record IDs and delete them.
			foreach ( $delete_ids as $id ) {
				
				$this->delete_cart( esc_sql( $id ) );

			}

			// esc_url_raw() is used to prevent converting ampersand in url to "#038;".
			// add_query_arg() return the current url.
			wp_redirect( esc_url_raw( add_query_arg() ) );
			exit;
		}
	}

}
