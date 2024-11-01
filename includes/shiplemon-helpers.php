<?php
/**
 * Shiplemon helper functions
 *
 * @package Shiplemon
 * @version 1.0.0
 * @since  1.0.0
 */

/**
 *  Helper function to create Shiplemon options
 *
 *  @return array
 */
function shiplemon_get_form_fields() {
	return array(
		'api_url'                            => array(
			'type'        => 'text',
			'title'       => esc_html__( 'API URL', 'shiplemon-shipping' ),
			'default'     => 'https://api.shiplemon.com',
			'placeholder' => 'https://api.shiplemon.com',
		),
		'api_key'                            => array(
			'type'  => 'text',
			'title' => esc_html__( 'API Key', 'shiplemon-shipping' ),
		),
		'estimated_delivery_in_days_enabled' => array(
			'type'    => 'checkbox',
			'title'   => esc_html__( 'Days to deliverance', 'shiplemon-shipping' ),
			'label'   => esc_html__( 'Show this text:', 'shiplemon-shipping' ),
			'default' => 'no',
		),
		'estimated_delivery_in_days_text'    => array(
			'type'        => 'text',
			'title'       => '&nbsp;',
			/* translators: %d: Integer for number of days */
			'default'     => esc_html__( 'To be delivered in %d working days', 'shiplemon-shipping' ),
			/* translators: %d: Integer for number of days */
			'placeholder' => esc_html__( 'To be delivered in %d working days', 'shiplemon-shipping' ),
			'desc_tip'    => true,
			/* translators: %d: Integer for number of days description */
			'description' => esc_html__( 'On this text use the "%d" to display the number of days', 'shiplemon-shipping' ),
		),
		'default_product_weight'             => array(
			'type'        => 'number',
			'title'       => esc_html__( 'Default product weight when not set (in grams)', 'shiplemon-shipping' ),
			'default'     => esc_html__( '500', 'shiplemon-shipping' ),
			'desc_tip'    => true,
			'description' => esc_html__( 'Set the default weight for any product that does not have it set. The weight must be set in grams.', 'shiplemon-shipping' ),
		),
		'default_item_height'                => array(
			'type'        => 'number',
			'title'       => esc_html__( 'Default package height', 'shiplemon-shipping' ),
			'default'     => esc_html__( '20', 'shiplemon-shipping' ),
			'desc_tip'    => true,
			'description' => esc_html__( 'Default height of package (in cm) for cost calculation.', 'shiplemon-shipping' ),
		),
		'default_item_width'                 => array(
			'type'        => 'number',
			'title'       => esc_html__( 'Default package width', 'shiplemon-shipping' ),
			'default'     => esc_html__( '20', 'shiplemon-shipping' ),
			'desc_tip'    => true,
			'description' => esc_html__( 'Default width of package (in cm) for cost calculation.', 'shiplemon-shipping' ),
		),
		'default_item_length'                => array(
			'type'        => 'number',
			'title'       => esc_html__( 'Default package length', 'shiplemon-shipping' ),
			'default'     => esc_html__( '10', 'shiplemon-shipping' ),
			'desc_tip'    => true,
			'description' => esc_html__( 'Default length of the package (in cm) for cost calculation.', 'shiplemon-shipping' ),
		),
	);
}
/**
 *  Helper function to format the Shiplemon API URL
 *
 *  @param string $api_url URL to the Shiplemon API.
 *  @param string $path To the API function.
 *  @return string
 */
function shiplemon_get_api_url( $api_url, string $path ) {
	return rtrim( $api_url, '/' ) . '/' . ltrim( $path, '/' );
}

/**
 *  Helper function to format the Shiplemon Dashboard API URL
 *
 *  @param string $app_url URL to the Shiplemon API.
 *  @param string $shipment_id the ID of the shipment on the API.
 *  @return string
 */
function get_shiplemon_dashboard_url( string $app_url, string $shipment_id ): string {
	return rtrim( $app_url, '/' ) . '/shipments/' . $shipment_id;
}

/**
 *  Helper function to get the URL of the Shiplemon iFrame
 *
 *  @param string $app_url The URL to be used to contact Shiplemon API.
 *  @param string $shipment_id THe ID of the shipment to be sent to the API.
 *  @param string $api_key The API key to authenticate the request.
 *  @return string
 */
function get_shiplemon_iframe( string $app_url, string $shipment_id, string $api_key ): string {
	return rtrim( $app_url, '/' ) . '/ext/shipments/' . $shipment_id . '?api_key=' . $api_key . '&lang=' . get_locale();
}

/**
 *  Helper function to get the product weight
 *
 *  @param string $unit_of_measurement the used measurement unit.
 *  @param float  $product_weight The weight of the product.
 *  @param float  $item_minimum_weight The minimum weight to be assigned to a product.
 *  @param float  $item_maximum_weight The maximum weight to be assigned to a product.
 *  @return float
 */
function shiplemon_get_product_weight( string $unit_of_measurement, float $product_weight, float $item_minimum_weight, float $item_maximum_weight ): float {
	switch ( $unit_of_measurement ) {
		case 'kg':
			$weight_in_grams = $product_weight * 1000;
			break;
		case 'lbs':
			$weight_in_grams = $product_weight * 453.59237;
			break;
		case 'oz':
			$weight_in_grams = $product_weight * 28.34952;
			break;
		// Default is grams.
		default:
			$weight_in_grams = $product_weight;
	}

	if ( $weight_in_grams < $item_minimum_weight ) {
		$weight_in_grams = $item_minimum_weight;
	}

	if ( $weight_in_grams > $item_maximum_weight ) {
		$weight_in_grams = $item_maximum_weight;
	}

	return $weight_in_grams;
}
