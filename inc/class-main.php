<?php
namespace NPFC;

class Main {
	/**
	 * The instance *Singleton* of this class
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return Main the *Singleton* instance.
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		$this->get_set_data();
		$this->require_widgets();
	}

	function enqueue_styles() {
		// Enqueue styles goes here
		wp_enqueue_style( 'dashicons' );
	}

	function require_widgets() {
		require_once( 'nfcw-commodity-price.php' );
		require_once( 'nfcw-ex-rates.php' );
		require_once( 'nfcw-fuel-price.php' );
	}

	function get_set_data() {
		global $npfc_json;
		$npfc_json = get_transient( 'npfc_json' );
		if ( false === $npfc_json ) {
			// It wasn't there, so regenerate the data and save the transient
			$get       = wp_remote_get( NPFC_API_URL );
			$response  = wp_remote_retrieve_body( $get );
			$npfc_json = json_decode( $response, true );
			
			// Set transient only if all three are set. 
			if ( isset( $npfc_json['fuel'] ) && isset( $npfc_json['forex'] ) && isset( $npfc_json['commodity'] ) ) {
				set_transient( 'npfc_json', $npfc_json, HOUR_IN_SECONDS );
			}
		}
	}

	public static function source( $url, $name ) {
		return '<span class="source">Source: <a href="' . $url . '" target="_blank">' . $name . ' </a></span>';
	}

}
