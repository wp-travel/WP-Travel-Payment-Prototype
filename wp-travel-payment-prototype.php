<?php
/**
 * Plugin Name: WP Travel Payment Prototype
 * Plugin URI: http://wptravel.io/downloads/wp-travel-payment-prototype
 * Description: WP Travel Payment Prototype is the addons plugin for WP Travel. Which made for Payment Prototype payment process.
 * Version: 1.0.0
 * Author: WEN Solutions
 * Author URI: http://wptravel.io
 * Requires at least: 4.4
 * Requires PHP: 5.5
 * Tested up to: 5.6
 *
 * Text Domain: wp-travel-payment-prototype
 * Domain Path: /i18n/languages/
 *
 * @package wp-travel-payment-prototype
 * @category Core
 * @author WEN Solutions
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Travel_Payment_Prototype' ) ) :
	/**
	 * Class WP_Travel_Payment_Prototype.
	 */
	class WP_Travel_Payment_Prototype {

		/**
		 * Plugin Name.
		 *
		 * @var string
		 */
		public $plugin_name = 'wp-travel-payment-prototype';

		/**
		 * Version.
		 *
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * The single instance of the class.
		 *
		 * @var string $_instance
		 */
		protected static $_instance = null;

		/**
		 * Main WP_Travel_Payment_Prototype Instance.
		 * Ensures only one instance of WP_Travel_Payment_Prototype is loaded or can be loaded.
		 *
		 * @return WP_Travel_Payment_Prototype - Main instance.
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->define_constants();
			add_action( 'admin_notices', array( $this, 'check_dependency' ) );
			if ( function_exists( 'WP_Travel' ) ) {
				$this->init_hooks();
			}
		}

		/**
		 * Plugin constants.
		 */
		private function define_constants() {
			$this->define( 'WP_TRAVEL_PAYMENT_PROTOTYPE_PLUGIN_FILE', __FILE__ );
			$this->define( 'WP_TRAVEL_PAYMENT_PROTOTYPE_ABSPATH', dirname( __FILE__ ) . '/' );
			$this->define( 'WP_TRAVEL_PAYMENT_PROTOTYPE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			$this->define( 'WP_TRAVEL_PAYMENT_PROTOTYPE_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
			$this->define( 'WP_TRAVEL_PAYMENT_PROTOTYPE_VERSION', $this->version );
		}

		/**
		 * Define constants.
		 *
		 * @param string $name Constant name.
		 * @param string $value Constant value.
		 */
		public function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Include  required function/ class files.
		 */
		private function includes() {
			include sprintf( '%s/inc/class-modules.php', WP_TRAVEL_PAYMENT_PROTOTYPE_ABSPATH );
		}

		/**
		 * Hooks to init in start.
		 */
		public function init_hooks() {
			$this->includes();
			add_action( 'init', array( $this, 'load_textdomain' ) );
			add_filter( 'wp_travel_premium_addons_list', array( $this, 'updater' ) );
			add_filter( 'wp_travel_show_upsell_message', array( $this, 'remove_upsell_message' ), 10, 2 );
		}

		/**
		 * Load localisation files.
		 */
		public function load_textdomain() {
			$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
			$locale = apply_filters( 'plugin_locale', $locale, 'wp-travel-payment-prototype' );
			unload_textdomain( 'wp-travel-payment-prototype' );

			load_textdomain( 'wp-travel-payment-prototype', WP_LANG_DIR . '/wp-travel/wp-travel-payment-prototype-' . $locale . '.mo' );
			load_plugin_textdomain( 'wp-travel-payment-prototype', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages' );
		}

		/**
		 * This will uninstall this plugin if parent WP Travel plugin not found.
		 */
		public function check_dependency() {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}

			if ( ! function_exists( 'wp_travel' ) ) {
				echo '<div class="error wp-travel-admin-notice-error wp-travel-admin-notice">';
				echo wp_kses_post( '<p><strong>WP Travel Payment Prototype</strong> requires the <a href="https://wptravel.io/" target="__blank">WP Travel</a> plugin to work. Please activate it first.</p>' ) ;
				echo '</div>';
			} else {
				if ( version_compare( WP_TRAVEL_VERSION, '4.3', '<' ) ) { 
					echo '<div class="error wp-travel-admin-notice-error wp-travel-admin-notice">';
					echo wp_kses_post( '<p>Current version of <strong>WP Travel Payment Prototype</strong> requires at least WP Travel Version 4.3. </p>' ) ;
					echo '</div>';
				}
			}
		}
		/**
		 * Updater callback to hook core update.
		 *
		 * @param  array $addons Array of addons.
		 */
		public function updater( $addons ) {
			$addons[ $this->plugin_name ] = array(
				'version'        => WP_TRAVEL_PAYMENT_PROTOTYPE_VERSION,
				'item_name'      => 'WP Travel Payment Prototype',
				'author'         => 'WEN Solutions',
				'_option_prefix' => 'wp_travel_payment_prototype_license_',
				'_file_path'     => WP_TRAVEL_PAYMENT_PROTOTYPE_PLUGIN_FILE,
			);
			return $addons;
		}

		/**
		 * Remove upsell message from WP Travel.
		 */
		public function remove_upsell_message( $display, $types ) {
			if ( in_array( 'wp-travel-payment-prototype', $types ) ) {
				return false;
			}
			return $display;
		}
	}

	/**
	 * Initiate WP Travel Payment Prototype addon.
	 */
	function wp_travel_payment_prototype_init() {
		return WP_Travel_Payment_Prototype::instance();
	}

	add_action( 'plugins_loaded', 'wp_travel_payment_prototype_init' );
endif;
