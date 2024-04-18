<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://zetasolutionsonline.com
 * @since      1.0.0
 *
 * @package    ZetaAbandonedCart
 * @subpackage ZetaAbandonedCart/admin/partials
 */

if ( wp_get_referer() ) {
	$go_back = wp_get_referer();
} else {
	$go_back = add_query_arg(
		array(
			'page' => 'zac-dashboard',
		),
		admin_url( '/admin.php' )
	);
}
?>
<div class="wrap clear">
	<div class="cart-data-container">
		<div class="cart-data">
			<p class="legend"><?php esc_html_e( 'Customer Information', 'zeta-abandoned-cart' ); ?></p>
		<?php
			 $form_data = (object) unserialize( $view_data->form_data ); // phpcs:ignore
		if ( ! empty( $form_data->billing_first_name ) ) {
			printf( '<div class="cart-info"><label>%s</label>: <p>%s</p></div>', esc_html__( 'Name', 'zeta-abandoned-cart' ), esc_html( $form_data->billing_first_name . ' ' . $form_data->billing_last_name ) );
		}

		if ( ! empty( $form_data->billing_phone ) ) {
			printf( '<div class="cart-info"><label>%s</label>: <p>%s</p></div>', esc_html__( 'Phone', 'zeta-abandoned-cart' ), esc_html( $form_data->billing_phone ) );
		}

		if ( ! empty( $form_data->billing_address_1 ) ) {
			printf( '<div class="cart-info"><label>%s</label>: <p>%s</p></div>', esc_html__( 'Address 1', 'zeta-abandoned-cart' ), esc_html( $form_data->billing_address_1 ) );
		}

		if ( ! empty( $form_data->billing_address_2 ) ) {
			printf( '<div class="cart-info"><label>%s</label>: <p>%s</p></div>', esc_html__( 'Address 2', 'zeta-abandoned-cart' ), esc_html( $form_data->billing_address_2 ) );
		}

		if ( ! empty( $form_data->billing_city ) ) {
			printf( '<div class="cart-info"><label>%s</label>: <p>%s</p></div>', esc_html__( 'City', 'zeta-abandoned-cart' ), esc_html( $form_data->billing_city ) );
		}

		if ( ! empty( $form_data->billing_state ) ) {
			printf( '<div class="cart-info"><label>%s</label>: <p>%s</p></div>', esc_html__( 'State', 'zeta-abandoned-cart' ), esc_html( $form_data->billing_state ) );
		}

		if ( ! empty( $form_data->billing_postcode ) ) {
			printf( '<div class="cart-info"><label>%s</label>: <p>%s</p></div>', esc_html__( 'Postcode', 'zeta-abandoned-cart' ), esc_html( $form_data->billing_postcode ) );
		}

		if ( ! empty( $form_data->billing_country ) ) {
			printf( '<div class="cart-info"><label>%s</label>: <p>%s</p></div>', esc_html__( 'Country', 'zeta-abandoned-cart' ), esc_html( $form_data->billing_country ) );
		}
		?>
		</div>

		<div class="cart-data">
			<p class="legend"><?php esc_html_e( 'Shipping Information', 'zeta-abandoned-cart' ); ?></p>
		<?php
			$form_data = (object) unserialize( $view_data->form_data ); // phpcs:ignore

		if ( filter_var( $form_data->ship_to_different_address, FILTER_VALIDATE_BOOLEAN ) ) {
			if ( ! empty( $form_data->shipping_first_name ) ) {
				printf( '<div class="cart-info"><label>%s</label>: <p>%s</p></div>', esc_html__( 'Name', 'zeta-abandoned-cart' ), esc_html( $form_data->shipping_first_name . ' ' . $form_data->shipping_last_name ) );
			}

			if ( ! empty( $form_data->shipping_company ) ) {
				printf( '<div class="cart-info"><label>%s</label>: <p>%s</p></div>', esc_html__( 'Company', 'zeta-abandoned-cart' ), esc_html( $form_data->shipping_company ) );
			}

			if ( ! empty( $form_data->shipping_phone ) ) {
				printf( '<div class="cart-info"><label>%s</label>: <p>%s</p></div>', esc_html__( 'Phone', 'zeta-abandoned-cart' ), esc_html( $form_data->shipping_phone ) );
			}

			if ( ! empty( $form_data->shipping_address_1 ) ) {
				printf( '<div class="cart-info"><label>%s</label>: <p>%s</p></div>', esc_html__( 'Address 1', 'zeta-abandoned-cart' ), esc_html( $form_data->shipping_address_1 ) );
			}

			if ( ! empty( $form_data->shipping_address_2 ) ) {
				printf( '<div class="cart-info"><label>%s</label>: <p>%s</p></div>', esc_html__( 'Address 2', 'zeta-abandoned-cart' ), esc_html( $form_data->shipping_address_2 ) );
			}

			if ( ! empty( $form_data->shipping_city ) ) {
				printf( '<div class="cart-info"><label>%s</label>: <p>%s</p></div>', esc_html__( 'City', 'zeta-abandoned-cart' ), esc_html( $form_data->shipping_city ) );
			}

			if ( ! empty( $form_data->shipping_state ) ) {
				printf( '<div class="cart-info"><label>%s</label>: <p>%s</p></div>', esc_html__( 'State', 'zeta-abandoned-cart' ), esc_html( $form_data->shipping_state ) );
			}

			if ( ! empty( $form_data->shipping_postcode ) ) {
				printf( '<div class="cart-info"><label>%s</label>: <p>%s</p></div>', esc_html__( 'Postcode', 'zeta-abandoned-cart' ), esc_html( $form_data->shipping_postcode ) );
			}

			if ( ! empty( $form_data->shipping_country ) ) {
				printf( '<div class="cart-info"><label>%s</label>: <p>%s</p></div>', esc_html__( 'Country', 'zeta-abandoned-cart' ), esc_html( $form_data->shipping_country ) );
			}
		} else {
			printf( '<div class="cart-info same-shipping"><p>%s</p></div>', esc_html__( 'Same as Billing / Customer Information', 'zeta-abandoned-cart' ), esc_html( $form_data->shipping_country ) );
		}
		?>
		</div>
		<div class="cart-data spanned">
			<p class="legend"><?php esc_html_e( 'Cart Information', 'zeta-abandoned-cart' ); ?></p>
			<table cellspacing="0" class="widefat fixed striped posts">
				<thead>
					<tr>
					   <th><?php esc_html_e( 'Item', 'zeta-abandoned-cart' ); ?></th>
					   <th><?php esc_html_e( 'Name', 'zeta-abandoned-cart' ); ?></th>
					   <th><?php esc_html_e( 'Quantity', 'zeta-abandoned-cart' ); ?></th>
					   <th><?php esc_html_e( 'Price', 'zeta-abandoned-cart' ); ?></th>
					   <th><?php esc_html_e( 'Line Total', 'zeta-abandoned-cart' ); ?></th>
					</tr>
				</thead>
				<tbody>
		<?php
			$cart_data = unserialize( $view_data->cart ); // phpcs:ignore
			$discount  = 0;
			$total     = 0;
			$ztax      = 0;
		foreach ( $cart_data as $cart_item ) :
			$cart_item = (object) $cart_item;
			$p_id      = 0 !== $cart_item->variation_id ? $cart_item->variation_id : $cart_item->product_id;
			$p_img     = get_the_post_thumbnail_url( $p_id );
			$p_img     = ! empty( $p_img ) ? $p_img : get_the_post_thumbnail_url( $cart_item->product_id );

			$discount = number_format_i18n( $discount + ( $cart_item->line_subtotal - $cart_item->line_total ), 2 );
			$total    = number_format_i18n( $total + $cart_item->line_subtotal, 2 );
			$ztax     = number_format_i18n( $ztax + $cart_item->line_tax, 2 );

			$product = wc_get_product( $p_id );
			$p_name  = $product ? $product->get_formatted_name() : '';

			printf(
				'<tr><td><img class="demo_img" width="42" height="42" src="%s"/></td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
				esc_url( $p_img ),
				esc_html( $p_name ),
				esc_html( $cart_item->quantity ),
				wc_price( $product->get_price() ), // phpcs:ignore
				wc_price( $cart_item->line_total ) // phpcs:ignore
			);

			endforeach;

				printf( '<tr><td align="right" colspan="4">%s</td><td align="left">%s</td></tr>', esc_html__( 'Discount', 'zeta-abandoned-cart' ), wc_price( $discount ) ); // phpcs:ignore
				printf( '<tr><td align="right" colspan="4">%s</td><td align="left">%s</td></tr>', esc_html__( 'Tax', 'zeta-abandoned-cart' ), wc_price( $ztax ) ); // phpcs:ignore
				printf( '<tr><td align="right" colspan="4">%s</td><td align="left">%s</td></tr>', esc_html__( 'Shipping', 'zeta-abandoned-cart' ), wc_price( $discount + ( $view_data->total - $total ) - $ztax, 2 ) ); // phpcs:ignore
				printf( '<tr><td align="right" colspan="4">%s</td><td align="left">%s</td></tr>', esc_html__( 'Cart Total', 'zeta-abandoned-cart' ), wc_price( $view_data->total ) ); // phpcs:ignore
		?>
			</tbody>
		</table>
		</div>
	</div>
		<br><br>
		<div class="extras">
			<a href="<?php echo esc_url( $go_back ); ?>" class="button button-secondary back-button"><span class="dashicons dashicons-arrow-left"  style="vertical-align:text-top;"></span> <?php esc_html_e( 'Back to Carts List', 'zeta-abandoned-cart' ); ?> </a>
		</div>
</div>
