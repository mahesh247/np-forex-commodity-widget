<?php
/*
Plugin Name:  NP Forex Commodity Widget
Plugin URI:   https://wordpress.org/plugins/np-forex-commodity-widget/
Description:  NP Forex Commodity Widget is a simple and light weight plugin to that to add up a widget that shows current commodity prices, exchange rates and fuel rates.
Version:      1.4
Author:       maheshmaharjan, tikarambhandari, pratikshrestha, skandha
Author URI:   https://mahesh-maharjan.com.np
License:      GPL2
License URI:  http://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  nfcw-widget
Domain Path:  /languages
*/

define('API_URL', 'https://mahesh-maharjan.com.np/api/v1/');

include_once( 'nfcw-commodity-price.php' );
include_once( 'nfcw-ex-rates.php' );
include_once( 'nfcw-oil-price.php' );