<?php
/**
 * WP Travel Payment Prototype Core Class.
 *
 * @package wp-travel-payment-prototype-core
 * @category Core
 * @author WEN Solutions
 */
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// WP Travel Payment Prototype core.
if ( ! class_exists( 'WP_Travel_Payment_Prototype_Core' ) ) :
	/**
	 * Core Class
	 */
	class WP_Travel_Payment_Prototype_Core {

		const WP_TRAVEL_PAYMENT_PROTOTYPE_HANDLE = 'wp_travel_payment_prototype_';
		/**
		 * ABSPATH
		 *
		 * @var string $abspath
		 */
		protected static $abspath;

		/**
		 * Plugin File Path
		 *
		 * @var string $plugin_file
		 */
		protected static $plugin_file;

		/**
		 * Plugin File URL
		 *
		 * @var string $plugin_url
		 */
		protected static $plugin_url;

		/**
		 * Plugin Version
		 *
		 * @var string $version
		 */
		protected static $version;

		/**
		 * The single instance of the class.
		 *
		 * @var WP Travel Payment Prototype Core
		 * @since 1.0.0
		 */
		protected static $_instance = null;

		/**
		 * Main WP_Travel_Payment_Prototype_Core Instance.
		 * Ensures only one instance of WP_Travel_Payment_Prototype_Core is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @see WP_Travel_Payment_Prototype_Core()
		 * @return WP_Travel_Payment_Prototype_Core - Main instance.
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Init core.
		 *
		 * @param array $plugin_data Plagin data.
		 */
		public static function init( $plugin_data ) {
			self::$abspath     = dirname( __FILE__ ) . '/';
			self::$plugin_file = __FILE__;
			self::$plugin_url  = plugin_dir_url( __FILE__ );
			self::$version     = $plugin_data['version'];

			self::includes();

			add_action( 'wp_enqueue_scripts', array( 'WP_Travel_Payment_Prototype_Core', 'frontend_assets' ), 30 );
			add_action( 'admin_enqueue_scripts', array( 'WP_Travel_Payment_Prototype_Core', 'admin_assets' ), 30 );

			// Payment Gateway list.
			add_filter( 'wp_travel_payment_gateway_lists', array( __CLASS__, 'wp_travel_gateway_payment_prototype' ) );
			add_filter( 'wp_travel_premium_addons_list', array( __CLASS__, 'wp_travel_addons_payment_prototype' ) );

			if ( self::uses_wp_travel_payment_prototype() ) {
				// Hooks for payment setup.
				add_action( 'wp_travel_after_frontend_booking_save', array( 'WP_Travel_Payment_Prototype_Core', 'set_up_payment_options' ), 30 );
				add_action( 'wp_travel_after_partial_payment_complete', array( 'WP_Travel_Payment_Prototype_Core', 'set_up_partial_payment' ), 30 );
			}

			isset( $_SESSION['used-wp-travel-payment-prototype'] ) && $_SESSION['used-wp-travel-payment-prototype'] && add_filter( 'wp_travel_booked_message', array( 'WP_Travel_Payment_Prototype_Core', 'booking_message' ), 30 );

		}

		/**
		 * Determine if booking used your payment method( wp-travel-payment-prototype ).
		 */
		private static function uses_wp_travel_payment_prototype() {
			return 'POST' === $_SERVER['REQUEST_METHOD'] && array_key_exists( 'wp_travel_booking_option', $_REQUEST ) && 'booking_with_payment' === $_REQUEST['wp_travel_booking_option'] && array_key_exists( 'wp_travel_payment_gateway', $_REQUEST ) && 'payment_prototype' === $_REQUEST['wp_travel_payment_gateway'];
		}

		/**
		 * Alter the booking travel message as payment has already been done.
		 *
		 * @param string $message Set trip booked message.
		 * @return string
		 */
		public static function booking_message( $message ) {

			unset( $_SESSION['used-wp-travel-payment-prototype'] );

			$message = esc_html__( "We've received your booking and payment details. We'll contact you soon.", 'wp-travel-pro' );

			return $message;

		}

		/**
		 * Determine if checkout is enabled.
		 */
		private static function is_enabled() {
			$settings = wp_travel_get_settings();
			return array_key_exists( 'payment_option_payment_prototype', $settings ) && 'yes' === $settings['payment_option_payment_prototype'];
		}

		/**
		 * Determing is checkout is disabled.
		 */
		private static function is_disabled() {
			return ! self::is_enabled();
		}

		/**
		 * Frontend assets.
		 */
		public static function frontend_assets() {
			if ( wptravel_can_load_payment_scripts() && self::is_enabled() ) {
				$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
				/**
				 * Enqueue frontend assets here.
				 *
				 * Example:
				 * wp_enqueue_script( 'wp-travel-payment-prototype-server', 'https://www.paypalobjects.com/api/checkout.js', array(), '0.0.1', true );
				 * wp_enqueue_script( 'wp-travel-payment-prototype', self::$plugin_url . 'assets/js/wp-travel-payment-prototype.js', array( 'wp-travel-payment-prototype-server' ), '0.0.1', true );
				 */
			}
		}

		/**
		 * Admin assets.
		 */
		public static function admin_assets() {

			$screen = get_current_screen();
			$screen = get_current_screen();
			if ( method_exists( 'WP_Travel', 'is_page' ) ) { // @since WP Travel 4.4.2
				$is_settings_page = WP_Travel::is_page( 'settings', true );
			} else {
				$is_settings_page = 'itinerary-booking_page_settings' == $screen->id;
			}
			if ( $is_settings_page ) {
				$deps                   = include_once sprintf( '%s/app/build/wp-travel-settings.asset.php', plugin_dir_path( __FILE__ ) );
				$deps['dependencies'][] = 'jquery';
				wp_enqueue_script( self::WP_TRAVEL_PAYMENT_PROTOTYPE_HANDLE . 'admin-settings', plugin_dir_url( __FILE__ ) . '/app/build/wp-travel-settings.js', $deps['dependencies'], $deps['version'], true );
			}
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 *
		 * @return void
		 */
		public static function includes() {

			if ( ! class_exists( 'WP_Travel' ) ) {
				return;
			}
			/**
			 * Include required files here.
			 *
			 * E.g
			 * include sprintf( '%sinc/options.php', self::$abspath );
			 */
			include sprintf( '%sinc/admin/settings.php', self::$abspath );
		}

		/**
		 * Return Payment Gateway list.
		 *
		 * @param array $gateways Payment Gateways.
		 */
		public static function wp_travel_gateway_payment_prototype( $gateways ) {
			if ( ! $gateways ) {
				return;
			}
			$gateways['payment_prototype'] = __( 'Payment Prototype', 'wp-travel-pro' );
			return $gateways;
		}

		/**
		 * Return Premium addons list.
		 *
		 * @param array $addons List of premium addons.
		 */
		public static function wp_travel_addons_payment_prototype( $addons ) {
			$addons['payment_prototype'] = __( 'Payment Prototype', 'wp-travel-pro' );
			return $addons;
		}

		/**
		 * Sets up payment options
		 *
		 * @param string $booking_id ID of booking.
		 * @return void
		 */
		public static function set_up_payment_options( $booking_id ) {

			if ( self::is_disabled() ) {
				return;
			}

			if ( ! self::uses_wp_travel_payment_prototype() ) {
				return;
			}

			if ( ! $booking_id ) {
				return;
			}

			do_action( 'wt_before_payment_process', $booking_id );

			/**
			 * Setting up payment options start here example.
			 */

			// $json = sanitize_text_field( wp_unslash( $_REQUEST['payment_details'] ) );

			// $detail = json_decode( $json );

			// $amount = array_sum( array_map( 'wp_travel_sum_amounts', $detail->transactions ) );
			// $payment_id = get_post_meta( $booking_id, 'wp_travel_payment_id', true );
			// $payment_method = 'payment_prototype';
			// update_post_meta( $payment_id, 'wp_travel_payment_gateway', $payment_method );

			// wp_travel_update_payment_status( $booking_id, $amount, 'paid', $detail, sprintf( '_%s_args', $payment_method ), $payment_id );

			// $_SESSION['used-wp-travel-payment-prototype'] = true;

			/**
			 * Ends here.
			 */

			do_action( 'wp_travel_after_successful_payment', $booking_id );

		}

		/**
		 * Sets up payment options
		 *
		 * @return void
		 */
		public static function set_up_partial_payment() {

			if ( self::is_disabled() ) {
				return;
			}

			if ( ! self::uses_wp_travel_payment_prototype() ) {
				return;
			}

			if ( ! isset( $_POST['complete_partial_payment'] ) ) {
				return;
			}

			if ( ! isset( $_POST['wp_travel_booking_id'] ) ) {
				return;
			}

			/**
			 * Setup for partial pay example starts here.
			 */
			// $booking_id  = sanitize_text_field( wp_unslash( $_POST['wp_travel_booking_id'] ) );
			// $payment_ids = array();
			// // get previous payment ids.
			// $payment_id = get_post_meta( $booking_id, 'wp_travel_payment_id', true );

			// $payment_method = sanitize_text_field( wp_unslash( $_POST['wp_travel_payment_gateway'] ) );
			// update_post_meta( $new_payment_id, 'wp_travel_payment_gateway', $payment_method );

			// if ( is_string( $payment_id ) && '' !== $payment_id ) {
			// 	$payment_ids[] = $payment_id;
			// } else {
			// 	$payment_ids = $payment_id;
			// }

			// // insert new payment id and update meta.
			// $title          = 'Payment - #' . $booking_id;
			// $post_array     = array(
			// 	'post_title'   => $title,
			// 	'post_content' => '',
			// 	'post_status'  => 'publish',
			// 	'post_slug'    => uniqid(),
			// 	'post_type'    => 'wp-travel-payment',
			// );
			// $new_payment_id = wp_insert_post( $post_array );
			// $payment_ids[]  = $new_payment_id;
			// update_post_meta( $booking_id, 'wp_travel_payment_id', $payment_ids );

			// $meta = get_post_meta( $booking_id, 'wp_travel_payment_id', true );

			// $payment_method = sanitize_text_field( wp_unslash( $_POST['wp_travel_payment_gateway'] ) );
			// update_post_meta( $new_payment_id, 'wp_travel_payment_gateway', $payment_method );

			// update_post_meta( $new_payment_id, 'wp_travel_payment_amount', $_POST['amount'] );
			// update_post_meta( $new_payment_id, 'wp_travel_payment_status', 'paid' );
			// update_post_meta( $new_payment_id, 'wp_travel_payment_mode', 'partial' );

			// $json = sanitize_text_field( wp_unslash( $_REQUEST['payment_details'] ) );

			// $detail = json_decode( $json );
			// $amount = array_sum( array_map( 'wp_travel_sum_amounts', $detail->transactions ) );

			// if ( $amount ) {
			// 	wp_travel_update_payment_status( $booking_id, $amount, 'paid', $detail, sprintf( '_%s_args', $payment_method ), $new_payment_id );

			// 	$_SESSION['used-wp-travel-payment-prototype'] = true;
			// }

			/** Ends here. */
		}

		/**
		 * What type of request is this?
		 *
		 * @param  string $type admin, ajax, cron or frontend.
		 * @return bool
		 */
		private static function is_request( $type ) {
			switch ( $type ) {
				case 'admin':
					return is_admin();
				case 'ajax':
					return defined( 'DOING_AJAX' );
				case 'cron':
					return defined( 'DOING_CRON' );
				case 'frontend':
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
			}
		}
	}
endif;
