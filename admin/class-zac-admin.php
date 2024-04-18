<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://zetasolutionsonline.com
 * @since      1.0.0
 *
 * @package    ZetaAbandonedCart
 * @subpackage ZetaAbandonedCart/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    ZetaAbandonedCart
 * @subpackage ZetaAbandonedCart/admin
 * @author     ZETA Solutions <info@zetasolutionsonline.com>
 */
class ZAC_Admin {

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
	 * The database of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      object $db    The instance of database class of this plugin.
	 */
	private $db;

	/**
	 * The database of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      object $carts_table The instance of database class of this plugin.
	 */
	private $carts_table;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->db = ZAC_DB::instance();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/zetaabandonedcart-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/zetaabandonedcart-admin.js', array( 'jquery' ), $this->version, false );
		wp_register_script( 'zac-graph-script', plugin_dir_url( __FILE__ ) . 'js/plotly-2.14.0.min.js', array(), $this->version, true );
		wp_register_script( 'zac-dashboard-script', plugin_dir_url( __FILE__ ) . 'js/zetaabandonedcart-dashboard.js', array( 'jquery', 'jquery-ui-datepicker', 'zac-graph-script' ), $this->version, true );
		
	}

	/**
	 * Check Plugin Requirements.
	 *
	 * @action admin_init
	 * @since    1.0.0
	 */
	public function requirements_check() {
		if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
			$notices = array(
				'type'       => 'error',
				'message'    => 'Are you sure Woocommerce plugin is Installed & Active? \'Abandoned Cart\' works with woocommerce only!',
				'deactivate' => true,
			);
			update_option( 'zac_admin_notice', $notices );
		}
	}

	/**
	 * Admin Notices.
	 *
	 * @action admin_notices
	 * @since    1.0.0
	 */
	public function notices() {
		$notice = get_option( 'zac_admin_notice' );
		if ( $notice ) {

			printf( '<div class="%s notice is-dismissible"><p>%s</p></div>',  esc_attr( $notice['type'] ), esc_html( $notice['message'] ) );

			if ( $notice['deactivate'] ) {
				if ( isset( $_GET['activate'] ) ) {
					unset( $_GET['activate'] );
				}

				deactivate_plugins( 'zeta-abandoned-cart/zeta-abandoned-cart.php' );
			}

			delete_option( 'zac_admin_notice' );
		}
	}

	/**
	 * Add options page.
	 *
	 * @since 1.0.0
	 */
	public function add_plugin_menu() {
		// This page will be under "Settings".
		add_menu_page(
			'',
			'Abandoned Cart',
			'manage_options',
			'zac-dashboard',
			array( $this, 'zac_dashboard_page' ),
			'dashicons-welcome-widgets-menus',
			56
		);
		add_submenu_page(
			'zac-dashboard',
			'Zeta Abandoned Cart',
			'Dashboard',
			'manage_options',
			'zac-dashboard',
			array( $this, 'zac_dashboard_page' ),
			1
		);
		add_submenu_page(
			'zac-dashboard',
			'Abandoned Cart - Settings',
			'Settings',
			'manage_options',
			'zac-settings',
			array( $this, 'zac_settings_page' ),
			2
		);
	}

	/**
	 * Set Screen.
	 *
	 * @param mixed $status status.
	 * @param mixed $option option.
	 * @param mixed $value value.
	 * @since 1.0.0
	 */
	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	/**
	 * Screen options.
	 *
	 * @since 1.0.0
	 */
	public function screen_option() {

		$option = 'per_page';
		$args   = array(
			'label'   => 'Carts',
			'default' => 5,
			'option'  => 'carts_per_page',
		);

		add_screen_option( $option, $args );
		$this->carts_table = new ZAC_List_Table();
	}

	/**
	 * Options page callback.
	 *
	 * @since 1.0.0
	 */
	public function zac_settings_page() {
		if ( isset( $_GET['settings-updated'] ) ) {
			add_settings_error( 'zac-settings', 'zac-settings-notice', esc_html__( 'Settings has been saved successfully', 'zeta-abandoned-cart' ), 'updated' );
		}
		settings_errors( 'zac-settings' );
		// Set class property.
		$this->options = get_option( 'zac_settings' );
		?>
		<div class="wrap zac-settings">
			<form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields.
				settings_fields( 'zac-settings-group' );
				do_settings_sections( 'zac-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register and add settings.
	 *
	 * @since 1.0.0
	 */
	public function page_init() {
		// START - general settings section.
		add_settings_section(
			'zac-general-settings',
			'General Settings',
			array( $this, 'zac_settings_section' ),
			'zac-settings'
		);

		add_settings_field(
			'zac_capture_enable',
			esc_html__( 'Enable Cart Capturing', 'zeta-abandoned-cart' ),
			array( $this, 'settings_checkbox_callback' ),
			'zac-settings',
			'zac-general-settings',
			array(
				'name'    => 'zac_capture_enable',
				'type'    => 'checkbox',
				'default' => ZAC_DEFAULT_CAPTURE_ENABLE,
			)
		);

		register_setting(
			'zac-settings-group',
			'zac_capture_enable',
			'sanitize_text_field'
		);

		add_settings_field(
			'zac_status_update_interval',
			esc_html__( 'Mark the Cart Abandoned after (minutes)', 'zeta-abandoned-cart' ),
			array( $this, 'settings_input_callback' ),
			'zac-settings',
			'zac-general-settings',
			array(
				'name'    => 'zac_status_update_interval',
				'type'    => 'number',
				'default' => ZAC_DEFAULT_STATUS_UPDATE_INTERVAL,
			)
		);

		register_setting(
			'zac-settings-group',
			'zac_status_update_interval',
			'intval'
		);

		add_settings_field(
			'zac_gdpr_enable',
			esc_html__( 'Enable GDPR Message', 'zeta-abandoned-cart' ),
			array( $this, 'settings_checkbox_callback' ),
			'zac-settings',
			'zac-general-settings',
			array(
				'name'    => 'zac_gdpr_enable',
				'type'    => 'checkbox',
				'default' => ZAC_DEFAULT_GDPR_ENABLE,
			)
		);

		register_setting(
			'zac-settings-group',
			'zac_gdpr_enable',
			'sanitize_text_field'
		);

		add_settings_field(
			'zac_gdpr_message',
			esc_html__( 'GDPR Message', 'zeta-abandoned-cart' ),
			array( $this, 'settings_textarea_callback' ),
			'zac-settings',
			'zac-general-settings',
			array(
				'name'    => 'zac_gdpr_message',
				'type'    => 'textarea',
				'default' => ZAC_DEFAULT_GDPR_MESSAGE,
			)
		);

		register_setting(
			'zac-settings-group',
			'zac_gdpr_message',
			'sanitize_textarea_field'
		);

		add_settings_field(
			'zac_gdpr_consent',
			esc_html__( 'GDPR User Consent Message', 'zeta-abandoned-cart' ),
			array( $this, 'settings_input_callback' ),
			'zac-settings',
			'zac-general-settings',
			array(
				'name'    => 'zac_gdpr_consent',
				'type'    => 'text',
				'default' => ZAC_DEFAULT_GDPR_CONSENT,
			)
		);

		register_setting(
			'zac-settings-group',
			'zac_gdpr_consent',
			'sanitize_text_field'
		);

		add_settings_field(
			'zac_notification_enable',
			esc_html__( 'Enable Notification', 'zeta-abandoned-cart' ),
			array( $this, 'settings_checkbox_callback' ),
			'zac-settings',
			'zac-general-settings',
			array(
				'name'    => 'zac_notification_enable',
				'type'    => 'checkbox',
				'default' => ZAC_DEFAULT_NOTIFICATION_ENABLE,
			)
		);

		register_setting(
			'zac-settings-group',
			'zac_notification_enable',
			'sanitize_text_field'
		);

		add_settings_field(
			'zac_notification_email',
			esc_html__( 'Notification Recipient', 'zeta-abandoned-cart' ),
			array( $this, 'settings_input_callback' ),
			'zac-settings',
			'zac-general-settings',
			array(
				'name'    => 'zac_notification_email',
				'type'    => 'email',
				'default' => ZAC_DEFAULT_NOTIFICATION_EMAIL,
			)
		);

		register_setting(
			'zac-settings-group',
			'zac_notification_email',
			'sanitize_email'
		);
		// END - general setting section.

		// START - email settings section.
		add_settings_section(
			'zac-email-settings',
			'Email Settings',
			array( $this, 'zac_settings_section' ),
			'zac-settings'
		);

		add_settings_field(
			'zac_follow_up_interval',
			esc_html__( 'Send follow up email after (hours)', 'zeta-abandoned-cart' ),
			array( $this, 'settings_input_callback' ),
			'zac-settings',
			'zac-email-settings',
			array(
				'name'    => 'zac_follow_up_interval',
				'type'    => 'number',
				'default' => ZAC_DEFAULT_FOLLOW_UP_INTERVAL,
			)
		);

		register_setting(
			'zac-settings-group',
			'zac_follow_up_interval',
			'intval'
		);

		add_settings_field(
			'zac_from_name',
			esc_html__( 'Name (Email From)', 'zeta-abandoned-cart' ),
			array( $this, 'settings_input_callback' ),
			'zac-settings',
			'zac-email-settings',
			array(
				'name'    => 'zac_from_name',
				'type'    => 'text',
				'default' => ZAC_DEFAULT_FROM_NAME,
			)
		);

		register_setting(
			'zac-settings-group',
			'zac_from_name',
			'sanitize_text_field'
		);

		add_settings_field(
			'zac_from_email',
			esc_html__( 'Email (From)', 'zeta-abandoned-cart' ),
			array( $this, 'settings_input_callback' ),
			'zac-settings',
			'zac-email-settings',
			array(
				'name'    => 'zac_from_email',
				'type'    => 'email',
				'default' => ZAC_DEFAULT_FROM_EMAIL,
			)
		);

		register_setting(
			'zac-settings-group',
			'zac_from_email',
			'sanitize_email'
		);

		add_settings_field(
			'zac_reply_email',
			esc_html__( 'Reply to', 'zeta-abandoned-cart' ),
			array( $this, 'settings_input_callback' ),
			'zac-settings',
			'zac-email-settings',
			array(
				'name'    => 'zac_reply_email',
				'type'    => 'email',
				'default' => ZAC_DEFAULT_FROM_EMAIL,
			)
		);

		register_setting(
			'zac-settings-group',
			'zac_reply_email',
			'sanitize_email'
		);

		add_settings_field(
			'zac_email_subject',
			esc_html__( 'Email Subject', 'zeta-abandoned-cart' ),
			array( $this, 'settings_input_callback' ),
			'zac-settings',
			'zac-email-settings',
			array(
				'name'    => 'zac_email_subject',
				'type'    => 'text',
				'default' => ZAC_DEFAULT_EMAIL_SUBJECT,
			)
		);

		register_setting(
			'zac-settings-group',
			'zac_email_subject',
			'sanitize_text_field'
		);

		add_settings_field(
			'zac_email_body',
			esc_html__( 'Email Body', 'zeta-abandoned-cart' ),
			array( $this, 'settings_editor_callback' ),
			'zac-settings',
			'zac-email-settings',
			array(
				'name'    => 'zac_email_body',
				'type'    => 'text',
				'default' => ZAC_DEFAULT_EMAIL_BODY,
			)
		);

		register_setting(
			'zac-settings-group',
			'zac_email_body',
			'wp_kses_data'
		);
		// END - email setting section.

		// START - coupon settings section.
		add_settings_section(
			'zac-coupon-settings',
			'Coupon Settings',
			array( $this, 'zac_settings_section' ),
			'zac-settings'
		);

		add_settings_field(
			'zac_coupon_enable',
			esc_html__( 'Create Coupon Automatically', 'zeta-abandoned-cart' ),
			array( $this, 'settings_checkbox_callback' ),
			'zac-settings',
			'zac-coupon-settings',
			array(
				'name'    => 'zac_coupon_enable',
				'type'    => 'checkbox',
				'default' => ZAC_DEFAULT_COUPON_ENABLE,
			)
		);

		register_setting(
			'zac-settings-group',
			'zac_coupon_enable',
			'sanitize_text_field'
		);

		add_settings_field(
			'zac_coupon_discount',
			esc_html__( 'Coupon Discount', 'zeta-abandoned-cart' ),
			array( $this, 'settings_input_callback' ),
			'zac-settings',
			'zac-coupon-settings',
			array(
				'name'    => 'zac_coupon_discount',
				'type'    => 'number',
				'default' => ZAC_DEFAULT_COUPON_DISCOUNT,
			)
		);

		register_setting(
			'zac-settings-group',
			'zac_coupon_discount',
			'intval'
		);

		add_settings_field(
			'zac_discount_type',
			esc_html__( 'Coupon Type', 'zeta-abandoned-cart' ),
			array( $this, 'settings_coupon_type_callback' ),
			'zac-settings',
			'zac-coupon-settings',
			array(
				'name'    => 'zac_discount_type',
				'type'    => 'radio',
				'default' => ZAC_DEFAULT_DISCOUNT_TYPE,
			)
		);

		register_setting(
			'zac-settings-group',
			'zac_discount_type',
			'sanitize_text_field'
		);
		// END - coupon setting section.
		// START - plugin setting section.
		add_settings_section(
			'zac-plugin-settings',
			'Coupon Settings',
			array( $this, 'zac_settings_section' ),
			'zac-settings'
		);

		add_settings_field(
			'zac_delete_db',
			esc_html__( 'Delete Plugin Data & Settings', 'zeta-abandoned-cart' ),
			array( $this, 'settings_checkbox_callback' ),
			'zac-settings',
			'zac-plugin-settings',
			array(
				'name'    => 'zac_delete_db',
				'type'    => 'checkbox',
				'default' => ZAC_DEFAULT_DELETE_DB,
			)
		);

		register_setting(
			'zac-settings-group',
			'zac_delete_db',
			'sanitize_text_field'
		);
		// END - plugin setting section.
	}

	/**
	 * Callback for settings input field.
	 *
	 * @param array $args args.
	 * @since 1.0.0
	 */
	public static function settings_input_callback( $args ) {
		$value = get_option( $args['name'], $args['default'] );
		printf( '<input type="%s" id="%s" name="%s" value="%s" />', esc_attr( $args['type'] ), esc_attr( $args['name'] ), esc_attr( $args['name'] ), esc_attr( $value ) );
	}

	/**
	 * Callback for settings input field.
	 *
	 * @param array $args args.
	 * @since 1.0.0
	 */
	public static function settings_coupon_type_callback( $args ) {
		$discount_type = get_option( 'zac_discount_type', ZAC_DEFAULT_DISCOUNT_TYPE );
		printf( '<div class="discount_type_wrapper">' );
		printf( '<input type="%s" id="%s" name="%s" value="fixed_cart" %s/>', esc_attr( $args['type'] ), esc_attr( $args['name'] ) . '_price', esc_attr( $args['name'] ), ( 'fixed_cart' === $discount_type ) ? 'checked' : '' );
		printf( '<label for="%s">%s</label>', esc_attr( $args['name'] ) . '_price', esc_html__( 'Price', 'zeta-abandoned-cart' ) );
		printf( '<input type="%s" id="%s" name="%s" value="percent" %s/>', esc_attr( $args['type'] ), esc_attr( $args['name'] ) . '_percent', esc_attr( $args['name'] ), ( 'percent' === $discount_type ) ? 'checked' : '' );
		printf( '<label for="%s">%s</label>', esc_attr( $args['name'] ) . '_percent', esc_html__( 'Percent', 'zeta-abandoned-cart' ) );
		printf( '</div>' );

	}

	/**
	 * Callback for settings text editor field.
	 *
	 * @param array $args args.
	 * @since 1.0.0
	 */
	public static function settings_editor_callback( $args ) {
		$content = get_option( $args['name'], $args['default'] );

		$editor_args = array(
			'media_buttons' => true, // This setting removes the media button.
			'textarea_rows' => 15, // Determine the number of rows.
			'quicktags'     => false, // Remove view as HTML button.
		);
		wp_editor( $content, $args['name'], $editor_args );

		printf( '<br><label for="zac_test_email" style="vertical-align: initial;">Enter Recipient </label>' );
		printf( '<input type="email" id="zac_test_email" value="%s" /> ', esc_attr( ZAC_DEFAULT_TEST_EMAIL ) );
		printf( '<a href="javascript:;" data-nonce="%s" data-url="%s" id="zac_test_email_send" class="button button-primary">Send Test Email</a>', wp_create_nonce( 'zetasolutions_test_email' ), admin_url( 'admin-ajax.php' ) ); //phpcs:ignore
		printf( '<span id="test_email_status"></span>' );
	}

	/**
	 * Callback for settings checkbox field.
	 *
	 * @param array $args args.
	 * @since 1.0.0
	 */
	public static function settings_checkbox_callback( $args ) {
		$value = checked( get_option( $args['name'], $args['default'] ), 'on', false );
		printf( '<label class="switch"><input type="checkbox" id="%s" name="%s" %s /><span class="slider"></span></label>', esc_attr( $args['name'] ), esc_attr( $args['name'] ), esc_attr( $value ) );
	}

	/**
	 * Callback for settings textarea field.
	 *
	 * @param array $args args.
	 * @since 1.0.0
	 */
	public static function settings_textarea_callback( $args ) {
		$value = get_option( $args['name'], $args['default'] );
		printf( '<textarea id="%s" name="%s">%s</textarea>', esc_attr( $args['name'] ), esc_attr( $args['name'] ), esc_textarea( $value ) );
	}

	/**
	 * Sanitize each setting field as needed.
	 *
	 * @param array $input Contains all settings fields as array keys.
	 * @since 1.0.0
	 */
	public function zac_sanitize( $input ) {
		// var_export($input);
		// TODO sanitization.
		return $input;
	}

	/**
	 * Print the Section text.
	 *
	 * @since 1.0.0
	 */
	public function zac_dashboard_page() {
		$notice = get_transient( 'zac_transient_notice' );
		if ( $notice ) {
			printf( '<div class="%s notice is-dismissible"><p>%s</p></div>', esc_attr( $notice['type'] ), esc_html( $notice['message'] ) );
			delete_transient( 'zac_transient_notice' );
		}

		$action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );

		switch ( $action ) {
			case 'view-cart':
				$session_id = filter_input( INPUT_GET, 'session_id', FILTER_SANITIZE_STRING );
				$data       = $this->db->get_checkout_by_session_id( $session_id );
				$this->load_view( 'view-cart', $data );
				break;
			case 'delete-cart':
				$id    = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_STRING );
				$nonce = filter_input( INPUT_GET, 'nonce', FILTER_SANITIZE_STRING );
				if ( wp_verify_nonce( $nonce, 'zac_delete_a_cart' ) ) {
					if ( $this->db->delete( $id ) ) {
						$message = array(
							'type'    => 'error',
							'message' => sprintf( esc_html__( '%d item/s deleted.', 'zeta-abandoned-cart' ), 1 ), //phpcs:ignore
						);
					} else {
						$message = array(
							'type'    => 'error',
							'message' => 'There\'s something wrong, please contact developer.',
						);
					}
					set_transient( 'zac_transient_notice', $message, 5 );
					wp_safe_redirect( wp_get_referer() );
				}
				break;
			default:
				$this->load_view( 'dashboard', $this->carts_table );
		}
	}

	/**
	 * Print the Section text.
	 *
	 * @since 1.0.0
	 */
	public function zac_settings_section() {
		echo '<hr/>';
	}

	/**
	 * Saving Cart data - called by Ajax from Checkout page.
	 *
	 * @since 1.0.0
	 */
	public function save_zac_data() {
		check_admin_referer( 'zetasolutions_save_abandoned_cart', 'nonce' );
		$sanitized = array();
		foreach ( $_POST as $key => $value ) {
			$filter = FILTER_SANITIZE_STRING;
			if ( strpos( $key, 'email' ) ) {
				$filter = FILTER_SANITIZE_EMAIL;
			}
			$sanitized[ $key ] = filter_input( INPUT_POST, $key, $filter );
		}

		unset( $sanitized['nonce'], $sanitized['action'] );
		$captured_checkout['checkout_id'] = $sanitized['checkout_id'];
		unset( $sanitized['checkout_id'] );
		$captured_checkout['email']     = $sanitized['billing_email'];
		$captured_checkout['cart']      = serialize( WC()->cart->get_cart() ); // phpcs:ignore
		$captured_checkout['total']     = sanitize_text_field( WC()->cart->total );
		$captured_checkout['form_data'] = serialize( $sanitized ); // phpcs:ignore
		$captured_checkout['timestamp'] = wp_date( 'Y-m-d H:i:s' );

		$zac_session_id   = WC()->session->get( 'zac_session_id' );
		$session_checkout = null;

		if ( isset( $zac_session_id ) ) {
			// already exists.
			$session_checkout = $this->db->get_checkout_by_session_id( $zac_session_id );
		} else {
			// check by email.
			$session_checkout = $this->db->get_checkout_by_email( $sanitized['billing_email'] );
			if ( $session_checkout ) {
				// found using email.
				$zac_session_id = $session_checkout->session_id;
				WC()->session->set( 'zac_session_id', $zac_session_id );
			} else {
				// not exist, created new.
				$zac_session_id = md5( uniqid( 'zac', true ) );
				WC()->session->set( 'zac_session_id', $zac_session_id );
			}
		}

		if ( isset( $session_checkout ) && 'recovered' === $session_checkout->order_status ) {
			WC()->session->__unset( 'zac_session_id' );
			$zac_session_id = md5( uniqid( 'zac', true ) );
		}

		$zac_session_id = sanitize_text_field( $zac_session_id );

		if ( isset( $captured_checkout['total'] ) && $captured_checkout['total'] > 0 ) {
			if ( ( ! is_null( $zac_session_id ) ) && ! is_null( $session_checkout ) ) {
				$this->db->update(
					ZAC_CARTS_TABLE,
					$captured_checkout,
					array(
						'session_id' => $session_checkout->session_id,
					)
				);
			} else {
				$captured_checkout['session_id'] = $zac_session_id;
				$this->db->insert( ZAC_CARTS_TABLE, $captured_checkout );
			}
		}

		wp_send_json_success();
	}

	/**
	 * Checking order if it's available in.
	 *
	 * @param int $order_id order id.
	 * @since 1.0.0
	 */
	public function zac_order_process( $order_id ) {
		$order = wc_get_order( $order_id );

		// we are considering orders in 'processing' as compeleted for abandoned carts table.
		if ( ! in_array( $order->get_status(), array( 'processing', 'on-hold', 'completed' ), true ) ) {
			return;
		}

		$restored_session  = null;
		$abandoned_session = null;

		$restored_session  = WC()->session->get( 'zac_session_restored' );
		$abandoned_session = WC()->session->get( 'abandoned_session' );
		$zac_session_id    = WC()->session->get( 'zac_session_id' );

		if ( ( null === $restored_session && null === $abandoned_session ) && null !== $zac_session_id ) {
			$this->db->delete_by_session_id( $zac_session_id );
			WC()->session->__unset( 'zac_session_id' );
			return;
		}

		$this->db->update(
			ZAC_CARTS_TABLE,
			array(
				'order_status'     => 'recovered',
				'update_timestamp' => current_time( 'Y-m-d H:i:s' ),
			),
			array( 'session_id' => $abandoned_session )
		);

		$order->add_order_note( esc_html__( 'Order recovered using ZETA Abandoned Cart Plugin.', 'zeta-abandoned-cart' ) );

		WC()->session->__unset( 'zac_session_restored' );
		WC()->session->__unset( 'abandoned_session' );
		WC()->session->__unset( 'zac_session_id' );
	}

	/**
	 * Send follow up emails.
	 *
	 * @param string $to_name name of the recepient.
	 * @param string $to_email email of the recepient.
	 * @param string $session_id session id of the abandoned cart (optional).
	 * @return mixed
	 */
	public function zac_send_follow_up_email( $to_name, $to_email, $session_id = '' ) {

		$to_email = filter_var( $to_email, FILTER_SANITIZE_EMAIL );
		$to_name  = filter_var( $to_name, FILTER_SANITIZE_STRING );

		if ( ! filter_var( $to_email, FILTER_VALIDATE_EMAIL ) ) {
			return false;
		}

		$from_name = get_option( 'zac_from_name', ZAC_DEFAULT_FROM_NAME );
		$subject   = get_option( 'zac_email_subject', ZAC_DEFAULT_EMAIL_SUBJECT );

		$body = get_option( 'zac_email_body', ZAC_DEFAULT_EMAIL_BODY );

		$substitue = array(
			'{email_from_name}'       => $from_name,
			'{customer_name}'         => $to_name,
			'{site_title}'            => get_bloginfo( 'name' ),
			'http://{checkout_link}'  => sprintf( '%s?abandoned_session=%s', wc_get_checkout_url(), $session_id ),
			'{checkout_link}'         => sprintf( '%s?abandoned_session=%s', wc_get_checkout_url(), $session_id ),
			'{cart_unsubscribe_link}' => 'unsubscribe link',
		);

		$body = str_replace( array_keys( $substitue ), array_values( $substitue ), $body );

		$headers   = array( 'Content-Type: text/html; charset=UTF-8' );
		$headers[] = sprintf( 'From: %s <%s>', $from_name, get_option( 'zac_from_email', ZAC_DEFAULT_FROM_EMAIL ) );
		$headers[] = sprintf( 'Reply-To: %s', get_option( 'zac_reply_email', ZAC_DEFAULT_FROM_EMAIL ) );

		return wp_mail( $to_email, $subject, wpautop( $body ), $headers );
	}

	/**
	 * Prepare Cart.
	 *
	 * @since 1.0.0
	 */
	public function zac_restore_abandoned_cart() {
		$abandoned_session = filter_input( INPUT_GET, 'abandoned_session', FILTER_SANITIZE_STRING );

		if ( null === $abandoned_session ) {
			return;
		}

		if ( 'zac-dummy-session' === $abandoned_session ) {
			$this->create_dummy_cart();
			return;
		}

		$checkout = $this->db->get_checkout_by_session_id( $abandoned_session );

		if ( null === $checkout ) {
			return;
		}

		$cart = unserialize( $checkout->cart ); // phpcs:ignore

		if ( empty( $cart ) ) {
			return;
		}

		global $woocommerce;

		$woocommerce->cart->empty_cart();
		wc_clear_notices();

		foreach ( $cart as $item ) {

			$item_data      = array();
			$variation_data = array();
			$id             = $item['product_id'];
			$qty            = $item['quantity'];

			// Skip bundled products when added main product.
			if ( isset( $item['bundled_by'] ) ) {
				continue;
			}

			if ( isset( $item['variation'] ) ) {
				foreach ( $item['variation'] as $key => $value ) {
					$variation_data[ $key ] = $value;
				}
			}

			$item_data = $item;

			$woocommerce->cart->add_to_cart( $id, $qty, $item['variation_id'], $variation_data, $item_data );
		}

		if ( ! $woocommerce->cart->applied_coupons && ! is_null( $checkout->coupon_code ) ) {
			$woocommerce->cart->add_discount( $checkout->coupon_code );
		}

		$form                      = unserialize( $checkout->form_data ); // phpcs:ignore
		$ship_to_different_address = $form['ship_to_different_address'];
		unset( $form['ship_to_different_address'] );

		if ( filter_var( $ship_to_different_address, FILTER_VALIDATE_BOOLEAN ) ) {
			// script to click ship to different address checkbox so that.
			// it set to checked & populate shipping fields automatically.
			add_action( 'wp_footer', array( $this, 'add_script_to_footer' ) );
		}

		foreach ( $form as $key => $value ) {
			// skipping shipping fields when ship to different address is not checked.
			if ( ! $ship_to_different_address && str_starts_with( $key, 'shipping' ) ) {
				continue;
			}

			if ( str_contains( $key, 'email' ) ) {
				$_POST[ $key ] = sanitize_email( $value );
			} else {
				$_POST[ $key ] = sanitize_text_field( $value );
			}
		}

		WC()->session->set( 'zac_session_restored', true );
		WC()->session->set( 'abandoned_session', $abandoned_session );
	}

	/**
	 * Dummy cart creation for testing.
	 *
	 * @since 1.0.0
	 */
	private function create_dummy_cart() {
		global $woocommerce;

		$woocommerce->cart->empty_cart();
		wc_clear_notices();

		$args = array(
			'numberposts' => 3,
			'orderby'     => 'rand',
			'post_type'   => 'product',
            'meta_query'     => array( //phpcs:ignore
				// Exclude out of stock products.
				array(
					'compare' => 'NOT IN',
					'key'     => '_stock_status',
					'value'   => 'outofstock',
				),
			),
		);

		$random_products = get_posts( $args );
		foreach ( $random_products as $random_product ) {
			$woocommerce->cart->add_to_cart( $random_product->ID );
		}
	}

	/**
	 * View Loader for Partials.
	 *
	 * @param string $view filename (without .php).
	 * @param array  $view_data array of data to be used in view.
	 * @since 1.0.0
	 */
	private function load_view( $view, $view_data = null ) {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/' . $view . '.php';
	}

	/**
	 * Send scheduled follow up emails.
	 *
	 * @since 1.0.0
	 */
	public function zac_send_scheduled_follow_up_emails() {

		$abandoned_carts = $this->db->get_carts_by_status( 'abandoned' );
		foreach ( $abandoned_carts as $abandoned_cart ) {
			$form_data = (object) unserialize( $abandoned_cart->form_data ); // phpcs:ignore

			// TODO need to do something if follow up more than one time.
			if ( $abandoned_cart->follow_up >= 1 ) {
				continue;
			}

			// FIXME maybe need to make it dynamic?
			// this is the case when system unable to capture first & last name, or user didn't enter name at all.
			$customer_name = 'Dear';

			if ( $form_data->billing_first_name ) {
				$customer_name = $form_data->billing_first_name;
				if ( $form_data->billing_last_name ) {
					$customer_name .= ' ' . $form_data->billing_last_name;
				}
			}

			$mail_result = $this->zac_send_follow_up_email( $customer_name, $abandoned_cart->email, $abandoned_cart->session_id );
			// TODO email logs.
			$follow_up = $abandoned_cart->follow_up + 1;
			$this->db->update(
				ZAC_CARTS_TABLE,
				array(
					'follow_up'        => $follow_up,
					'update_timestamp' => current_time( 'Y-m-d H:i:s' ),
				),
				array( 'session_id' => $abandoned_cart->session_id )
			);
		}
	}

	/**
	 * Script to auto click ship to different address checkbox.
	 *
	 * @since 1.0.0
	 */
	public function add_script_to_footer() {
		?>
	<script id="ship-to-different-address-auto-click">
		jQuery( document ).ready(function() {
			jQuery("#ship-to-different-address-checkbox").click();
		});
	</script>
		<?php
	}

	/**
	 * Marking Carts Abandoned & Coupon Creation.
	 *
	 * @since 1.0.0
	 */
	public function update_order_status() {

		$raw_orders           = $this->db->get_carts_by_status( 'raw' );
		$raw_orders_num       = count( $raw_orders );
		$coupon_enabled       = get_option( 'zac_coupon_enable', ZAC_DEFAULT_COUPON_ENABLE );
		$notification_enabled = get_option( 'zac_notification_enable', ZAC_DEFAULT_NOTIFICATION_ENABLE );
		$discount             = get_option( 'zac_coupon_discount', ZAC_DEFAULT_COUPON_DISCOUNT );
		$discount_type        = get_option( 'zac_discount_type', ZAC_DEFAULT_DISCOUNT_TYPE );

		foreach ( $raw_orders as $raw_order ) {
			$this->db->update(
				ZAC_CARTS_TABLE,
				array(
					'order_status'     => 'abandoned',
					'update_timestamp' => current_time( 'Y-m-d H:i:s' ),
				),
				array( 'id' => $raw_order->id )
			);

			if ( 'on' !== $coupon_enabled || null !== $raw_order->coupon_code ) {
				continue;
			}

			$coupon_code = substr( $raw_order->session_id, 5, 10 );

			$coupon = new WC_Coupon();
			$coupon->set_code( $coupon_code );
			$coupon->set_description( esc_html__( 'Created with ZETA Abandoned Cart Plugin.', 'zeta-abandoned-cart' ) );
			$coupon->set_discount_type( $discount_type );
			$coupon->set_amount( $discount );
			$coupon->set_free_shipping( false );

			$raw_cart = unserialize( $raw_order->cart ); // phpcs:ignore
			$coupon->set_individual_use( true );

			$coupon_product_ids = array();
			foreach ( $raw_cart as $raw_item ) {
				$coupon_product_ids[] = absint( $raw_item['product_id'] );
			}

			$coupon->set_product_ids( $coupon_product_ids );
			$coupon->set_email_restrictions( array( $raw_order->email ) );
			$coupon->set_usage_limit( 1 );
			$coupon->set_usage_limit_per_user( 1 );
			$coupon->save();

			$this->db->update(
				ZAC_CARTS_TABLE,
				array(
					'coupon_code'      => $coupon_code,
					'update_timestamp' => current_time( 'Y-m-d H:i:s' ),
				),
				array( 'id' => $raw_order->id )
			);
		}

		if ( 'on' === $notification_enabled && $raw_orders_num > 0 ) {
			$notification_email = get_option( 'zac_notification_email', ZAC_DEFAULT_NOTIFICATION_EMAIL );

			$num_cart = 'is ' . $raw_orders_num . ' order';
			if ( $raw_orders_num > 1 ) {
				$num_cart = 'are ' . $raw_orders_num . ' orders';
			}

			$subject = 'Update - Zeta Abandoned Cart Plugin';

			$from_name = get_option( 'zac_from_name', ZAC_DEFAULT_FROM_NAME );

			$body  = '<p>Hello,</p>';
			$body .= '<p>There ' . $num_cart . ' get abandoned at ' . current_time( 'H:i:s' ) . ' on ' . current_time( 'Y-m-d' ) . '. Please see the details on following link.</p>';
			$body .= '<p><a href="' . admin_url( 'admin.php?page=zac-dashboard' ) . '" target="_blank">' . admin_url( 'admin.php?page=zac-dashboard' ) . '</a></p>';

			$headers   = array( 'Content-Type: text/html; charset=UTF-8' );
			$headers[] = sprintf( 'From: %s <%s>', $from_name, get_option( 'zac_from_email', ZAC_DEFAULT_FROM_EMAIL ) );

			$mail_result = wp_mail( $notification_email, $subject, $body, $headers );
			// TODO email logs.
		}
	}

	/**
	 * Add GDPR Message.
	 *
	 * @since 1.0.0
	 */
	public function add_gdpr_message() {
		$gdpr_enable = get_option( 'zac_gdpr_enable', ZAC_DEFAULT_GDPR_ENABLE );

		if ( 'on' !== $gdpr_enable ) {
			return;
		}

		$gdpr_message = get_option( 'zac_gdpr_message', ZAC_DEFAULT_GDPR_MESSAGE );
		$gdpr_consent = get_option( 'zac_gdpr_consent', ZAC_DEFAULT_GDPR_CONSENT );

		printf(
			'<p style="font-size: x-small;">%s<br>
                <input type="checkbox" id="zac-user-consent" checked/><label for="zac-user-consent">%s</label>
                </p>',
			esc_html( $gdpr_message ),
			esc_html( $gdpr_consent )
		);
	}

	/**
	 * Send test email to specified recipient.
	 *
	 * @since 1.0.0
	 */
	public function zac_test_email() {
		check_admin_referer( 'zetasolutions_test_email', 'nonce' );

		$zac_test_email = filter_input( INPUT_POST, 'email', FILTER_SANITIZE_STRING );

		if ( ! filter_var( $zac_test_email, FILTER_VALIDATE_EMAIL ) ) {
			wp_send_json_error( 'Please enter a valid email.' );
		}

		$mail_result = $this->zac_send_follow_up_email( 'John Doe', $zac_test_email, 'zac-dummy-session' );
		// TODO email logs.
		wp_send_json_success( 'Email Sent Successfully!' );
	}

	/**
	 * Create date range for Graphs
	 *
	 * @since 1.0.0
	 */
	public function createRange($start, $end, $format = 'Y-m-d') {
		$start  = new DateTime($start);
		$end    = new DateTime($end);
		$invert = $start > $end;
	
		$dates = array();
		$dates[$start->format($format)] = 0;
		while ($start != $end) {
			$start->modify(($invert ? '-' : '+') . '1 day');
			$dates[$start->format($format)] = 0;
		}
		return $dates;
	}
}
