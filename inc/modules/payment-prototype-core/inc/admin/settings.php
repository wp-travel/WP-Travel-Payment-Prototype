<?php
/**
 * Settings file of WP Travel Payment Prototype.
 *
 * @package WP Travel.
 */

/**
 * Setting field for payment prefix. Use this for adding fields value on backend.
 *
 * @param array $settings_fields Fields.
 * @return array $settings_fields.
 */
function wp_travel_payment_prototype_settings_field( $settings_fields ) {
	$settings_fields['payment_option_payment_prototype'] = 'no';
	return $settings_fields;
}
add_filter( 'wp_travel_settings_fields', 'wp_travel_payment_prototype_settings_field' );
