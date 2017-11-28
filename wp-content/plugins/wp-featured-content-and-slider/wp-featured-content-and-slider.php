<?php
/**
 * Plugin Name: WP Featured Content and Slider
 * Plugin URI: http://www.wponlinesupport.com/
 * Text Domain: wp-featured-content-and-slider
 * Domain Path: /languages/
 * Description: Easy to add and display what features your company, product or service offers, using our shortcode OR template code.
 * Author: WP Online Support
 * Version: 1.2.3
 * Author URI: http://www.wponlinesupport.com/
 *
 * @package WordPress
 * @author WP Online Support
 */
 
if ( ! defined( 'ABSPATH' ) ) exit;

if( !defined( 'WPFCAS_VERSION' ) ) {
	define( 'WPFCAS_VERSION', '1.2.3' ); // Version of plugin
}

add_action('plugins_loaded', 'wp_wpfcas_load_textdomain');
function wp_wpfcas_load_textdomain() {
	load_plugin_textdomain( 'wp-featured-content-and-slider', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
}

/**
 * Function to get unique value number
 * 
 * @package WP Featured Content and Slider
 * @since 1.2.1
 */
function wpfcas_get_unique() {
	static $unique = 0;
	$unique++;

	return $unique;
}

add_action( 'wp_enqueue_scripts','wpfcas_style_css' );
function wpfcas_style_css() {
	
	wp_register_style( 'wpfcas-font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css', array(), WPFCAS_VERSION );
	wp_enqueue_style( 'wpfcas-font-awesome' );
	
	wp_enqueue_style( 'wpfcas_style',  plugin_dir_url( __FILE__ ). 'assets/css/featured-content-style.css', array(), WPFCAS_VERSION );
	wp_enqueue_script( 'wpfcas_slick_jquery', plugin_dir_url( __FILE__ ) . 'assets/js/slick.min.js', array( 'jquery' ), WPFCAS_VERSION );
	wp_enqueue_style( 'wpfcas_slick_style',  plugin_dir_url( __FILE__ ) . 'assets/css/slick.css', array(), WPFCAS_VERSION);
}

require_once( 'includes/featured-content-functions.php' );
require_once( 'includes/featured-content_menu_function.php' );
require_once( 'templates/featured-content-template.php' );
require_once( 'templates/featured-content-slider-template.php' );