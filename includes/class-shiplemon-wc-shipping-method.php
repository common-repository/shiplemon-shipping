<?php
/**
 * Shiplemon WooCommerce shipping method class
 *
 * @package Shiplemon
 * @version 1.0.0
 * @since 1.0.0
 */

define( 'SHIPLEMON_MIN_WEIGHT', 500 );
define( 'SHIPLEMON_MAX_WEIGHT', 35000 );

if ( ! class_exists( 'Shiplemon_WC_Shipping_Method' ) ) {
	/**
	 *  Shiplemon shipping class extending WC_Shipping_Method.
	 */
	class Shiplemon_WC_Shipping_Method extends WC_Shipping_Method {

		/**
		 * Constructor for your shipping class
		 *
		 * @access public
		 * @return void
		 */
		public function __construct() {

			// Id for your shipping method. Should be unique.
			$this->id = 'shiplemon_shipping';
			// Title shown in admin.
			$this->title = __( 'Shiplemon', 'shiplemon-shipping' );
			// Title shown in admin.
			$this->method_title = __( 'Shiplemon', 'shiplemon-shipping' );
			// Description shown in admin.
			$this->method_description = __( 'Do you need help? Contact as at <a href="http://www.shiplemon.com">www.shiplemon.com</a>', 'shiplemon-shipping' );

			$this->init();
		}

		/**
		 *  Function to add WooCommerce hooks
		 *
		 *  @return void
		 */
		public function init_actions_and_filters() {
			add_action( 'woocommerce_order_status_processing', array( $this, 'purchase_shipment' ), 20, 1 );
		}

		/**
		 *  Initializes class functions
		 *
		 *  @return void
		 */
		public function init() {

			// Load the settings API.
			// This is part of the settings API. Override the method to add your own settings.
			$this->init_form_fields();

			// This is part of the settings API. Loads settings you previously init.
			$this->init_settings();

			// Save settings in admin if you have any defined.
			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		/**
		 *  Set the shipping method form fields
		 *
		 *  @return void
		 */
		public function init_form_fields() {
			$this->form_fields = apply_filters( 'shiplemon_shipping_fields', shiplemon_get_form_fields() );
		}

		/**
		 *  Calculates the shippigng cost of the order
		 *
		 *  @param array $package Stores packages to ship and to get quotes for.
		 *  @return void
		 */
		public function calculate_shipping( $package = array() ) {

			$unit_of_measurement = get_option( 'woocommerce_unit_weight' );

			$invoice_lines         = array();
			$total_weight_in_grams = 0;
			$cart_total_value      = 0;

			foreach ( WC()->cart->get_cart() as $package ) {

				$product  = wc_get_product( $package['product_id'] );
				$quantity = $package['quantity'];

				$product_weight          = (float) ( $product->get_weight() ? $product->get_weight() : $this->get_option( 'default_product_weight' ) );
				$product_weight_in_grams = shiplemon_get_product_weight( $unit_of_measurement, $product_weight, SHIPLEMON_MIN_WEIGHT, SHIPLEMON_MAX_WEIGHT );

				$total_weight_in_grams = $product_weight_in_grams * $quantity;
				$cart_total_value      = $cart_total_value + floatval( $product->get_price() * $quantity );

				$invoice_lines[] = array(
					'value'             => floatval( $product->get_price() ),
					'currency'          => 'EUR',
					'quantity'          => (int) $quantity,
					'description'       => $product->get_name(),
					'country_of_origin' => $product->get_attribute( 'country_of_origin' ),
					'commodity_code'    => $product->get_attribute( 'commodity_code' ),
					'eccn'              => $product->get_attribute( 'eccn' ),
					'weight'            => $product->get_weight(),
				);
			}

			$items       = array();
			$weight_left = $total_weight_in_grams;
			do {
				$weight      = $weight_left > SHIPLEMON_MAX_WEIGHT ? SHIPLEMON_MAX_WEIGHT : $weight_left;
				$weight_left = $weight_left - $weight;

				$items[] = array(
					'weight'        => $weight,
					'height'        => $this->get_option( 'default_item_height' ),
					'width'         => $this->get_option( 'default_item_width' ),
					'length'        => $this->get_option( 'default_item_length' ),
					'notes'         => 'Box',
					'invoice_lines' => $invoice_lines,
				);

				$total_weight_in_grams = $total_weight_in_grams - $weight;
			} while ( $weight_left > 0 );

			$address_from = array(
				'country' => WC()->countries->get_base_country(),
				'zip'     => WC()->countries->get_base_postcode(),
				'city'    => WC()->countries->get_base_city(),
			);

			$address_to = array(
				'country' => WC()->customer->get_shipping_country(),
				'zip'     => WC()->customer->get_shipping_postcode(),
				'city'    => WC()->customer->get_shipping_city(),
			);

			$api = shiplemon_get_api_url( $this->get_option( 'api_url' ), '/v1/rates' );

			$payload = array(
				'headers'     => array(
					'x-api-key'     => $this->get_option( 'api_key' ),
					'x-api-context' => 'plugin',
					'Content-Type'  => 'application/json',
				),
				'data_format' => 'body',
				'method'      => 'POST',
				'timeout'     => 45,
				'body'        => wp_json_encode(
					array(
						'address_from'     => $address_from,
						'address_to'       => $address_to,
						'items'            => $items,
						'cart_total_value' => $cart_total_value,
					)
				),
			);

			$response = wp_remote_post( $api, $payload );

			$response_code  = wp_remote_retrieve_response_code( $response );
			$response_body  = json_decode( wp_remote_retrieve_body( $response ), true );
			$response_error = null;

			if ( 500 === $response_code ) {
				$logger = wc_get_logger();
				$logger->error( 'Response code: ' . $response_code, array( 'source' => 'shiplemon' ) );
				$logger->error( print_r( $response_body, true ), array( 'source' => 'shiplemon' ) );

				return;
			}
			// TODO: We should do a better error handling.
			if ( is_wp_error( $response ) ) {

				$response_error = $response;

				return;
			} elseif ( 200 !== $response_code ) {
				$response_error = new WP_Error(
					'shiplemon - rates - api - error',
					/* translators: %d: Integer of API response status */
					sprintf( __( 'Invalid API response code(%d).', 'shiplemon-shipping' ), (int) $response_code )
				);

				$logger = wc_get_logger();
				$logger->error( print_r( json_decode( $response_body, true ) ), array( 'source' => 'shiplemon' ) );

				return;
			}

			if ( $response_error ) {
				define( 'WP_DEBUG', true );
				define( 'WP_DEBUG_LOG', true );
				define( 'WP_DEBUG_DISPLAY', false );

				$logger = wc_get_logger();
				$logger->error( 'shiplemon failed to fetch rates', array( 'source' => 'shiplemon' ) );
			}

			if ( 'ok' === $response_body['status'] ) {

				$estimated_delivery_in_days_text    = $this->get_option( 'estimated_delivery_in_days_text' );
				$estimated_delivery_in_days_enabled = $this->get_option( 'estimated_delivery_in_days_enabled' );

				foreach ( $response_body['data'] as $rate ) {
					// used to persist the user selection, should not change when asking for rates but the same rate is returned.
					$rate_id = $rate['service']['code'];

					$service_name_without_provider_name = str_replace( $rate['provider']['name'], '', $rate['service']['name'] );
					$custom_name                        = join( ' - ', array_filter( array( $rate['provider']['name'], $service_name_without_provider_name ) ) );
					$label                              = ! empty( $rate['friendly_name'] ) ? $rate['friendly_name'] : $custom_name;

					$this->add_rate(
						array(
							'id'        => $rate_id,
							'label'     => $label,
							'cost'      => $rate['total_amount'],
							'meta_data' => array(
								'class'             => $rate_id,
								'shiplemon_rate_id' => $rate['id'],
								'image'             => $rate['provider']['image'],
								'estimated_delivery_in_days_text' => $estimated_delivery_in_days_text,
								'estimated_delivery_in_days_enabled' => $estimated_delivery_in_days_enabled,
								'estimated_delivery_in_days' => $rate['service']['estimated_delivery_in_days'],
								'provider'          => $rate['provider']['name'],
								'service'           => $rate['service']['name'],
							),
						)
					);
				}
			}
		}

		/**
		 *  Sends order info to the Shiplemon API
		 *
		 *  @param int $order_id The WooCommerce order ID number.
		 *  @return void
		 */
		public function purchase_shipment( $order_id ) {

			// Bailout early if in wp-admin.
			if ( is_user_logged_in() && is_admin() ) {
				return;
			}

			$order = wc_get_order( $order_id );

			$chosen_shipping_methods = $order->get_shipping_methods();
			$chosen_shipping_methods = array_reverse( $chosen_shipping_methods );
			$chosen_shipping_method  = array_pop( $chosen_shipping_methods );
			$shiplemon_rate_id       = $chosen_shipping_method->get_meta( 'shiplemon_rate_id' );

			// Bail out early if we don't have a shiplemon rate id.
			if ( empty( $shiplemon_rate_id ) ) {
				return;
			}

			$address_from = array(
				'country'  => WC()->countries->get_base_country(),
				'zip'      => WC()->countries->get_base_postcode(),
				'name'     => $this->get_option( 'shop_name' ),
				'company'  => $this->get_option( 'shop_company' ),
				'address'  => WC()->countries->get_base_address(),
				'address2' => WC()->countries->get_base_address_2(),
				'city'     => WC()->countries->get_base_city(),
				'phone'    => $this->get_option( 'shop_phone' ),
				'email'    => $this->get_option( 'shop_email' ),
			);

			$address_to = array(
				'country'  => ! empty( $order->get_shipping_country() )
					? $order->get_shipping_country()
					: $order->get_billing_country(),
				'zip'      => ! empty( $order->get_shipping_country() )
					? $order->get_shipping_postcode()
					: $order->get_billing_postcode(),
				'name'     => ! empty( $order->get_shipping_country() )
					? $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name()
					: $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
				'company'  => ! empty( $order->get_shipping_country() )
					? $order->get_shipping_company()
					: $order->get_billing_company(),
				'address'  => ! empty( $order->get_shipping_country() )
					? $order->get_shipping_address_1()
					: $order->get_billing_address_1(),
				'address2' => ! empty( $order->get_shipping_country() )
					? $order->get_shipping_address_2()
					: $order->get_billing_address_2(),
				'city'     => ! empty( $order->get_shipping_country() )
					? $order->get_shipping_city()
					: $order->get_billing_city(),
				'phone'    => $order->get_billing_phone(),
				'email'    => $order->get_billing_email(),
			);

			$arguments = array(
				'headers'     => array(
					'x-api-key'     => $this->get_option( 'api_key' ),
					'x-api-context' => 'plugin',
					'Content-Type'  => 'application/json',
				),
				'data_format' => 'body',
				'method'      => 'POST',
				'timeout'     => 45,
				'body'        => wp_json_encode(
					array(
						'draft'        => true,
						'rate_id'      => $shiplemon_rate_id,
						'address_from' => $address_from,
						'address_to'   => $address_to,
					)
				),
			);

			$response = wp_remote_post( shiplemon_get_api_url( $this->get_option( 'api_url' ), '/v1/shipments' ), $arguments );

			$response_code  = wp_remote_retrieve_response_code( $response );
			$response_body  = json_decode( wp_remote_retrieve_body( $response ), true );
			$response_error = null;

			update_post_meta( $order_id, '_shiplemon_shipment_rate_id', $shiplemon_rate_id );

			if ( 500 === $response_code ) {
				$logger = wc_get_logger();
				$logger->error( 'Response code: ' . $response_code, array( 'source' => 'shiplemon' ) );
				$logger->error( print_r( $response_body, true ), array( 'source' => 'shiplemon' ) );

				return;
			}

			// TODO: We should do a better error handling.
			if ( is_wp_error( $response ) ) {
				$response_error = $response;

				return;
			} elseif ( 200 !== $response_code ) {
				$response_error = new WP_Error(
					'shiplemon - rates - api - error',
					/* translators: %d: Integer for API responce code */
					sprintf( __( 'Invalid API response code(%d).', 'shiplemon-shipping' ), (int) $response_code )
				);

				return;
			}

			if ( 'ok' === $response_body['status'] ) {
				// We might not need everything here, keeping it for now.
				update_post_meta( $order_id, '_shiplemon_shipment_raw', $response_body['data'] );
				update_post_meta( $order_id, '_shiplemon_shipment_id', $response_body['data']['id'] );
			}
		}
	}
}
