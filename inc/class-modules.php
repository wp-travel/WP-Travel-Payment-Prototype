<?php
/**
 * Class Module.
 *
 * @package inc/modules
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'WP_Travel_Payment_Prototype_Modules' ) ) :
	/**
	 * WP Travel Payment Prototype Modules.
	 */
	class WP_Travel_Payment_Prototype_Modules {
		/**
		 * Modules.
		 *
		 * @var array
		 */
		private static $modules;

		/**
		 * Param.
		 *
		 * @var array
		 */
		private static $params;

		/**
		 * Settings.
		 *
		 * @var array
		 */
		private static $settings;

		/**
		 * Default settings.
		 *
		 * @var array
		 */
		private static $default_settings;

		/**
		 * Saved settings.
		 *
		 * @var array
		 */
		private static $saved_settings;

		/**
		 * WP Travel User Since.
		 *
		 * @var array
		 */
		private static $user_since;

		/**
		 * Init.
		 *
		 * @param array $params Param of module.
		 */
		public static function init( $params = array() ) {
			self::$modules = array(
				'wp-travel-payment-prototype' => array(
					'title'      => __( 'WP Travel Payment Prototype', 'wp-travel-custom-payment' ),
					'core_class' => 'WP_Travel_Payment_Prototype_Core',
					'category'   => 'Payment',
				),
			);

			self::$params = $params;
			add_action(
				'init',
				function() {
					add_filter( 'wptravel_modules', array( 'WP_Travel_Payment_Prototype_Modules', 'modules' ), 20 );

					self::$settings         = wptravel_get_settings();
					self::$default_settings = wptravel_settings_default_fields();
					self::$user_since       = get_option( 'wp_travel_user_since', '3.0.0' );

					// Includes.
					self::includes();

				},
				0
			);
			// init settings.
			self::$saved_settings = get_option( 'wp_travel_settings', array() ); // To get only saved settings data.
		}

		/**
		 * Modules.
		 */
		public static function modules( $modules ) {

			if ( self::$modules ) {
				$settings         = self::$settings;
				$default_settings = self::$default_settings;
				$saved_settings   = self::$saved_settings;
				$default_modules  = isset( $default_settings['modules'] ) ? $default_settings['modules'] : array();
				$user_since       = self::$user_since;

				foreach ( self::$modules as $module_key => $module ) {
					$key         = 'show_' . str_replace( '-', '_', $module_key );
					$module_name = $key;

					// Enabled/disable module check.
					$enabled_module = false;

					if ( ! $saved_settings && version_compare( $user_since, '5.2.2', '>=' ) ) {
						$enabled_module = true; // By default enabled for new user from v 5.2.2.
					} elseif ( $saved_settings && isset( $saved_settings['modules'] ) && count( $saved_settings['modules'] ) > 0 && isset( $saved_settings['modules'][ $module_name ] ) ) { // check If data is saved in new array structure.
						$enabled_module = 'yes' === $saved_settings['modules'][ $module_name ]['value']; // Saved modules values.
					} else {
						// fetch saved value from old structure. // Legacy.
						$enabled_module = isset( $settings[ $module_name ] ) && 'yes' === $settings[ $module_name ]; // Saved modules values.
					}
					$module['value'] = $enabled_module ? 'yes' : 'no'; // just to indicate enable / disable.

					$module['force_enable'] = false;
					$modules[ $key ]    = $module;
				}
			}
			$columns = array_column( $modules, 'title' );
			array_multisort( $columns, SORT_ASC, $modules );
			return $modules;
		}

		/**
		 * WP_Travel_Custom_Payment includes.
		 */
		public static function includes() {
			$settings         = wptravel_get_settings(); // Need to re assign instead of using self::$settings to get updated data.
			$default_settings = wptravel_settings_default_fields(); // Need to re assign instead of using self::$default_settings to get updated data.
			$default_modules  = isset( $default_settings['modules'] ) ? $default_settings['modules'] : array();
			$modules          = isset( $settings['modules'] ) && count( $settings['modules'] ) > 0 ? $settings['modules'] : $default_modules;
			$saved_settings   = get_option( 'wp_travel_settings', array() );
			if ( is_array( $default_modules ) && count( $default_modules ) > 0 ) {
				foreach ( $default_modules as $key => $module ) {
					$module_name      = $key;
					$main_folder_key  = str_replace( '_', '-', $module_name );
					$main_folder_key  = str_replace( 'show-', '', $main_folder_key );
					$main_folder      = str_replace( 'wp-travel-', '', $main_folder_key ) . '-core';
					$module_core_file = sprintf( '%sinc/modules/%s/%s-core.php', WP_TRAVEL_PAYMENT_PROTOTYPE_ABSPATH, $main_folder, $main_folder_key );

					$enabled_module = isset( $modules[ $module_name ] ) && 'yes' === $modules[ $module_name ]['value'];
					$forced_enabled = isset( $modules[ $module_name ] ) && $modules[ $module_name ]['force_enable'];
					if ( file_exists( $module_core_file ) && ! class_exists( $module['core_class'] ) && ( $enabled_module || $forced_enabled ) ) {
						include_once $module_core_file;
						$module['core_class']::init( self::$params );
					}
				}
			} else {
				foreach ( self::$modules as $key => $module ) {
					$main_folder      = str_replace( 'wp-travel-', '', $key ) . '-core';
					$module_core_file = sprintf( '%sinc/modules/%s/%s-core.php', WP_TRAVEL_PAYMENT_PROTOTYPE_ABSPATH, $main_folder, $key );
					if ( file_exists( $module_core_file ) && ! class_exists( $module['core_class'] ) ) {
						include_once $module_core_file;
						$module['core_class']::init( self::$params );
					}
				}
			}
		}
	}

	$params = array(
		'abspath'     => WP_TRAVEL_PAYMENT_PROTOTYPE_ABSPATH,
		'plugin_file' => WP_TRAVEL_PAYMENT_PROTOTYPE_PLUGIN_FILE,
		'version'     => WP_TRAVEL_PAYMENT_PROTOTYPE_VERSION,
	);
	WP_Travel_Payment_Prototype_Modules::init( $params );
endif;
