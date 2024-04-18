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

$recoverable = $this->db->get_carts_by_status( 'abandoned' );
$recovered   = $this->db->get_carts_by_status( 'recovered' );

$recoverable_for_chart = $this->db->get_data_for_chart( 'abandoned' );
$recovered_for_chart  = $this->db->get_data_for_chart( 'recovered' );

$dates = $this->createRange(gmdate('Y-m-d'), gmdate('Y-m-d', strtotime('now - 1week')));

$vars = array(
	'dates' => array_keys($dates),
	'recoverable' => array_values(array_merge($dates, array_column($recoverable_for_chart, 'orders', 'date'))),
	'recovered' => array_values(array_merge($dates, array_column($recovered_for_chart, 'orders', 'date'))),
	'title' => esc_html__( 'Last 7 Days Summary', 'zeta-abandoned-cart' ),
);

wp_enqueue_script( 'zac-graph-script' );
wp_localize_script( 'zac-dashboard-script', 'vars', $vars );
wp_enqueue_script( 'zac-dashboard-script' );

print_r($recoverable_for_chart);
print_r($recovered_for_chart);
?>
<div class="wrap clear">
<h2>ZETA Abandoned Cart</h2>
<hr/>
	<div class="zac-stat-cards">
		<div class="zac-stat-card">
			<p class="title"><?php esc_html_e( 'Recoverable Orders', 'zeta-abandoned-cart' ); ?></p>
			<p class="body"><?php printf( '%s', count( $recoverable ) ); ?></p>
			<p class="details"><?php esc_html_e( 'Total Recoverable Orders', 'zeta-abandoned-cart' ); ?></p>
		</div>
		<div class="zac-stat-card">
			<p class="title"><?php esc_html_e( 'Recovered Orders', 'zeta-abandoned-cart' ); ?></p>
			<p class="body"><?php printf( '%s', count( $recovered ) ); ?></p>
			<p class="details"><?php esc_html_e( 'Total Recovered Orders', 'zeta-abandoned-cart' ); ?></p>
		</div>
		<div class="zac-stat-card graph-container">
			<p class="title">Summary</p>
			<div id="orderSummaryGraph"></div>
		</div>
		<div class="zac-stat-card">
			<p class="title"><?php esc_html_e( 'Recoverable Amount', 'zeta-abandoned-cart' ); ?></p>
			<p class="body"><?php printf( '%s', wc_price( array_sum( array_column( $recoverable, 'total' ) ), 2 ) ); // phpcs:ignore ?></p>
			<p class="details"><?php esc_html_e( 'Total Recoverable Amount', 'zeta-abandoned-cart' ); ?></p>
		</div>
		<div class="zac-stat-card">
			<p class="title"><?php esc_html_e( 'Recovered Amount', 'zeta-abandoned-cart' ); ?></p>
			<p class="body"><?php printf( '%s', wc_price( array_sum( array_column( $recovered, 'total' ) ), 2 ) ); // phpcs:ignore ?></p>
			<p class="details"><?php esc_html_e( 'Total Recovered Amount', 'zeta-abandoned-cart' ); ?></p>
		</div>
	</div>
	<!-- carts table -->
	<form method="post">
	<?php
		$view_data->prepare_items();
		$view_data->display();
	?>
	</form>
</div>
