<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Class Gallery_Img_Widgets
 */
class Gallery_Img_Widgets{

	/**
	 * Gallery_Img_Widgets constructor.
	 */
	public function __construct() {
		add_action( 'widgets_init', array($this,'register_widget'));
	}

	/**
	 * Register Huge-IT Portfolio Gallery Widget
	 */
	public function register_widget(){
		register_widget( 'Huge_it_gallery_Widget' );
	}
}

new Gallery_Img_Widgets();
