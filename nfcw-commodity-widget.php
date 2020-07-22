<?php
/*
Plugin Name:  NP Forex Commodity Widget
Plugin URI:   https://wordpress.org/plugins/np-forex-commodity-widget/
Description:  NP Forex Commodity Widget is a simple and light weight plugin to that to add up a widget that shows current commodity prices, exchange rates and fuel rates.
Version:      1.6
Author:       maheshmaharjan, tikarambhandari, pratikshrestha, skandha
Author URI:   https://mahesh-maharjan.com.np
License:      GPL3
License URI:  https://www.gnu.org/licenses/gpl-3.0.html
Text Domain:  nfcw-widget
Domain Path:  /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hide_Archive_Title
 */
class NP_Forex_Commodity {
	/** Constructor */
	function __construct() {
		/**
		 * Display admin error message if PHP version is older than 5.3.2.
		 * Otherwise execute the main plugin class.
		 */
		if ( version_compare( phpversion(), '5.3.2', '<' ) ) {
			add_action( 'admin_notices', array( $this, 'old_php_admin_error_notice' ) );
		} else {
			$this->set_constants();
			require_once NPFC_PATH . 'inc/class-main.php';
			NPFC\Main::get_instance();
		}
	}

	/** Set Plugin constants */
	function set_constants() {
		if ( ! defined( 'NPFC_URL' ) ) {
			define( 'NPFC_URL', plugin_dir_url( __FILE__ ) );
		}
		if ( ! defined( 'NPFC_PATH' ) ) {
			define( 'NPFC_PATH', plugin_dir_path( __FILE__ ) );
		}
		if ( ! defined( 'NPFC_API_URL' ) ) {
			define( 'NPFC_API_URL', 'https://nfcw.herokuapp.com/nfcw' );
		}
		define( 'NPFC_FUEL_SRC_URL', '//nepaloil.com.np' );
		define( 'NPFC_FOREX_SRC_URL', '//nrb.org.np' );
		define( 'NPFC_COMMODITY_SRC_URL', '//fenegosida.org' );
		define( 'NPFC_FUEL_SRC', 'Nepal Oil Corporation Limited' );
		define( 'NPFC_FOREX_SRC', 'Nepal Rastra Bank' );
		define( 'NPFC_COMMODITY_SRC', 'Federation of Nepal Gold & Silver Dealer\'s Association' );

		add_action( 'plugin_loaded', array( $this, 'set_plugin_version_constant' ) );
	}

	/** Set Plugin version constant */
	function set_plugin_version_constant() {

		if ( ! defined( 'NPFC_VERSION' ) ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			$plugin_data = get_plugin_data( __FILE__ );
			define( 'NPFC_VERSION', $plugin_data['Version'] );
		}
	}
}

$hide_archive_title = new NP_Forex_Commodity();
