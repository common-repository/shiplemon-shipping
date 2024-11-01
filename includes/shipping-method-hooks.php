<?php
/**
 * Shiplemon hooks for WooCommerce
 *
 * @package Shiplemon
 * @version 1.0.0
 * @since  1.0.0
 */

add_action( 'woocommerce_init', 'shiplemon_init_shipping_method' );
/**
 *  Includes needes files and init plugin actions
 *
 *  @return void
 */
function shiplemon_init_shipping_method() {
	include_once 'class-shiplemon-wc-shipping-method.php';

	$shiplemon_shipping_method = new Shiplemon_WC_Shipping_Method();
	$shiplemon_shipping_method->init_actions_and_filters();
}


add_filter( 'woocommerce_shipping_methods', 'shiplemon_register_shipping_method' );
/**
 *  Add Shiplemon to the WooCommerce shipping methods
 *
 *  @param array $methods WooCOmmerce shipping methos array.
 *  @return Return description
 *
 *  @details More details
 */
function shiplemon_register_shipping_method( $methods ) {

	$methods[] = 'Shiplemon_WC_Shipping_Method';

	return $methods;
}

add_action( 'woocommerce_after_shipping_rate', 'shiplemon_woocommerce_after_shipping_rate', 20, 2 );
/**
 *  Add info about the shipping in the WooCommerce cart and checkout page
 *
 *  @param object $method WC_Shipping_Method shipping method object.
 *  @param int    $index Autoincrement number.
 *  @return void
 */
function shiplemon_woocommerce_after_shipping_rate( $method, $index ) {
	// @var WC_Shipping_Rate $method.
	if ( $method->get_method_id() === 'shiplemon_shipping' ) {
		$meta_data = $method->get_meta_data();

		echo '<div class="shiplemon-additional-meta ' . esc_attr( $meta_data['class'] ) . '">';

		echo '<div class="shiplemon-additional-meta-notes">';

		if ( 'no' !== $meta_data['estimated_delivery_in_days_enabled'] ) {
			echo '<small>';

			echo sprintf(
				esc_attr( $meta_data['estimated_delivery_in_days_text'] ),
				esc_attr( $meta_data['estimated_delivery_in_days'] )
			);
			echo '</small>';
		}

		echo '</div>';
		echo '</div>';
	}
}

add_filter( 'plugin_action_links_shiplemon-shipping/shiplemon-shipping.php', 'shiplemon_settings_link' );
/**
 *  Add direct link to the admin shipping settings via the plugin page
 *
 *  @param array $links plugins list links.
 *  @return array
 */
function shiplemon_settings_link( $links ) {

	// Build and escape the URL.
	$url = esc_url(
		add_query_arg(
			array(
				'page'    => 'wc-settings',
				'tab'     => 'shipping',
				'section' => 'shiplemon_shipping',
			),
			get_admin_url() . 'admin.php'
		)
	);
	// Create the link.
	$settings_link = "<a href='$url'>" . __( 'Settings', 'shiplemon-shipping' ) . '</a>';
	// Adds the link to the end of the array.
	array_push(
		$links,
		$settings_link
	);
	return $links;
}

add_action( 'admin_notices', 'shiplemon_admin_dash_notice' );
/**
 *  Add admin dashboard notice if API key is missing
 *
 *  @return void
 */
function shiplemon_admin_dash_notice() {
	global $pagenow;

	$woocommerce_shiplemon_shipping_settings = get_option( 'woocommerce_shiplemon_shipping_settings' );

	if ( 'index.php' === $pagenow && empty( $woocommerce_shiplemon_shipping_settings['api_key'] ) ) {

		// Build and escape the URL.
		$url = esc_url(
			add_query_arg(
				array(
					'page'    => 'wc-settings',
					'tab'     => 'shipping',
					'section' => 'shiplemon_shipping',
				),
				get_admin_url() . 'admin.php'
			)
		);

		?>
		<div class="notice notice-warning is-dismissible">
			<p><?php esc_html_e( 'Your Shiplemon API KEY is missing', 'shiplemon-shipping' ); ?>
				<a href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Click here to add one.', 'shiplemon-shipping' ); ?></a>
			</p>
		</div>
		<?php
	}
}
