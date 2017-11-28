<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Gallery_Img_Frontend_Scripts
 */
class Gallery_Img_Frontend_Scripts {

	/**
	 * Gallery_Img_Frontend_Scripts constructor.
	 */
	public function __construct() {
		add_action( 'gallery_img_shortcode_scripts', array( $this, 'frontend_scripts' ), 10, 4 );
		add_action( 'gallery_img_shortcode_scripts', array( $this, 'frontend_styles' ), 10, 2 );
		add_action( 'gallery_img_localize_scripts', array( $this, 'localize_scripts' ), 10, 1 );
	}

	/**
	 * Enqueue styles
	 */
	public function frontend_styles( $id, $gallery_view ) {
        $gallery_default_params = gallery_img_get_default_options();
		wp_register_style( 'gallery-all-css', plugins_url( '../assets/style/gallery-all.css', __FILE__ ) );
		wp_enqueue_style( 'gallery-all-css' );

		wp_register_style( 'style2-os-css', plugins_url( '../assets/style/style2-os.css', __FILE__ ) );
		wp_enqueue_style( 'style2-os-css' );

		wp_register_style( 'lightbox-css', plugins_url( '../assets/style/lightbox.css', __FILE__ ) );
		wp_enqueue_style( 'lightbox-css' );

		wp_register_style( 'fontawesome-css', plugins_url( '../assets/style/css/font-awesome.css', __FILE__ ) );
		wp_enqueue_style( 'fontawesome-css' );

		wp_enqueue_style( 'gallery_img_colorbox_css', untrailingslashit( Gallery_Img()->plugin_url() ) . '/assets/style/colorbox-' . $gallery_default_params[ 'gallery_img_light_box_style' ] . '.css' );

		if ( $gallery_view == '1' ) {
			wp_register_style( 'animate-css', plugins_url( '../assets/style/animate-min.css', __FILE__ ) ) ;
			wp_enqueue_style( 'animate-css' );
			wp_register_style( 'liquid-slider-css', plugins_url( '../assets/style/liquid-slider.css', __FILE__ ) );
			wp_enqueue_style( 'liquid-slider-css' );
		}
		if ( $gallery_view == '4' ) {
			wp_register_style( 'thumb_view-css', plugins_url( '../assets/style/thumb_view.css', __FILE__ ) );
			wp_enqueue_style( 'thumb_view-css' );
		}
		if ( $gallery_view == '6' ) {
			wp_register_style( 'thumb_view-css', plugins_url( '../assets/style/justifiedGallery.css', __FILE__ ) );
			wp_enqueue_style( 'thumb_view-css' );
		}
	}

	/**
	 * Enqueue scripts
	 */
	public function frontend_scripts( $id, $gallery_view ) {
		$view_slug = gallery_img_get_view_slag_by_id( $id );

		if ( ! wp_script_is( 'jquery' ) ) {
			wp_enqueue_script( 'jquery' );
		}

		wp_register_script( 'jquery.gicolorbox-js', plugins_url( '../assets/js/jquery.colorbox.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
		wp_enqueue_script( 'jquery.gicolorbox-js' );

		wp_register_script( 'gallery-hugeitmicro-min-js', plugins_url( '../assets/js/jquery.hugeitmicro.min.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
		wp_enqueue_script( 'gallery-hugeitmicro-min-js' );

		wp_register_script( 'front-end-js-'.$view_slug, plugins_url( '../assets/js/view-' . $view_slug . '.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
		wp_enqueue_script( 'front-end-js-'.$view_slug );

		wp_register_script( 'custom-js', plugins_url( '../assets/js/custom.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
		wp_enqueue_script( 'custom-js' );

		if ( $gallery_view == '1' ) {
			wp_register_script( 'easing-js', plugins_url( '../assets/js/jquery.easing.min.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
			wp_enqueue_script( 'easing-js' );
			wp_register_script( 'touch_swipe-js', plugins_url( '../assets/js/jquery.touchSwipe.min.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
			wp_enqueue_script( 'touch_swipe-js' );
			wp_register_script( 'liquid-slider-js', plugins_url( '../assets/js/jquery.liquid-slider.min.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
			wp_enqueue_script( 'liquid-slider-js' );
		}
		if ( $gallery_view == '4' ) {
			wp_register_script( 'thumb-view-js', plugins_url( '../assets/js/thumb_view.min.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
			wp_enqueue_script( 'thumb-view-js' );
			wp_register_script( 'lazyload-min-js', plugins_url( '../assets/js/jquery.lazyload.min.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
			wp_enqueue_script( 'lazyload--min-js' );
		}
		if ( $gallery_view == '6' ) {
			wp_register_script( 'jusiifed-js', plugins_url( '../assets/js/justifiedGallery.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
			wp_enqueue_script( 'jusiifed-js' );
		}

	}

	public function localize_scripts( $id ) {
		global $wpdb;
		global $post;
        $gallery_img_get_option = gallery_img_get_option();
		$pID                    = (string) $post->ID;
		$query                  = $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "huge_itgallery_gallerys WHERE id=%d", $id );
		$gallery                = $wpdb->get_results( $query );
		$admin_url              = admin_url( "admin-ajax.php" );
		$gallery_default_params = gallery_img_get_default_options();
		$query = $wpdb->prepare( "SELECT image_url FROM " . $wpdb->prefix . "huge_itgallery_images WHERE gallery_id=%d", $id );
		$images       = $wpdb->get_col( $query );
		$view_slug = gallery_img_get_view_slag_by_id( $id );
		$gallery_view = $gallery[0]->huge_it_sl_effects;
		$lightbox     = array(
			'lightbox_transition'     => $gallery_default_params[ 'gallery_img_light_box_transition' ],
			'lightbox_speed'          => $gallery_default_params[ 'gallery_img_light_box_speed' ],
			'lightbox_fadeOut'        => $gallery_default_params[ 'gallery_img_light_box_fadeout' ],
			'lightbox_title'          => $gallery_default_params[ 'gallery_img_light_box_title' ],
			'lightbox_scalePhotos'    => $gallery_default_params[ 'gallery_img_light_box_scalephotos' ],
			'lightbox_scrolling'      => $gallery_default_params[ 'gallery_img_light_box_scrolling' ],
			'lightbox_opacity'        => ( $gallery_default_params[ 'gallery_img_light_box_opacity' ] / 100 ) + 0.001,
			'lightbox_open'           => $gallery_default_params[ 'gallery_img_light_box_open' ],
			'lightbox_returnFocus'    => $gallery_default_params[ 'gallery_img_light_box_returnfocus' ],
			'lightbox_trapFocus'      => $gallery_default_params[ 'gallery_img_light_box_trapfocus' ],
			'lightbox_fastIframe'     => $gallery_default_params[ 'gallery_img_light_box_fastiframe' ],
			'lightbox_preloading'     => $gallery_default_params[ 'gallery_img_light_box_preloading' ],
			'lightbox_overlayClose'   => $gallery_default_params[ 'gallery_img_light_box_overlayclose' ],
			'lightbox_escKey'         => $gallery_default_params[ 'gallery_img_light_box_esckey' ],
			'lightbox_arrowKey'       => $gallery_default_params[ 'gallery_img_light_box_arrowkey' ],
			'lightbox_loop'           => $gallery_default_params[ 'gallery_img_light_box_loop' ],
			'lightbox_closeButton'    => $gallery_default_params[ 'gallery_img_light_box_closebutton' ],
			'lightbox_previous'       => $gallery_default_params[ 'gallery_img_light_box_previous' ],
			'lightbox_next'           => $gallery_default_params[ 'gallery_img_light_box_next' ],
			'lightbox_close'          => $gallery_default_params[ 'gallery_img_light_box_close' ],
			'lightbox_html'           => $gallery_default_params[ 'gallery_img_light_box_html' ],
			'lightbox_photo'          => $gallery_default_params[ 'gallery_img_light_box_photo' ],
			'lightbox_innerWidth'     => $gallery_default_params[ 'gallery_img_light_box_innerwidth' ],
			'lightbox_innerHeight'    => $gallery_default_params[ 'gallery_img_light_box_innerheight' ],
			'lightbox_initialWidth'   => $gallery_img_get_option[ 'gallery_img_light_box_initialwidth' ],
			'lightbox_initialHeight'  => $gallery_img_get_option[ 'gallery_img_light_box_initialheight' ],
			'lightbox_slideshow'      => $gallery_default_params[ 'gallery_img_light_box_slideshow' ],
			'lightbox_slideshowSpeed' => $gallery_default_params[ 'gallery_img_light_box_slideshowspeed' ],
			'lightbox_slideshowAuto'  => $gallery_default_params[ 'gallery_img_light_box_slideshowauto' ],
			'lightbox_slideshowStart' => $gallery_default_params[ 'gallery_img_light_box_slideshowstart' ],
			'lightbox_slideshowStop'  => $gallery_default_params[ 'gallery_img_light_box_slideshowstop' ],
			'lightbox_fixed'          => $gallery_default_params[ 'gallery_img_light_box_fixed' ],
			'lightbox_reposition'     => $gallery_default_params[ 'gallery_img_light_box_reposition' ],
			'lightbox_retinaImage'    => $gallery_default_params[ 'gallery_img_light_box_retinaimage' ],
			'lightbox_retinaUrl'      => $gallery_default_params[ 'gallery_img_light_box_retinaurl' ],
			'lightbox_retinaSuffix'   => $gallery_default_params[ 'gallery_img_light_box_retinasuffix'
            ],
			'lightbox_maxWidth'       => $gallery_img_get_option[ 'gallery_img_light_box_maxwidth' ],
			'lightbox_maxHeight'      => $gallery_img_get_option[ 'gallery_img_light_box_maxheight' ],
			'lightbox_sizeFix'        => $gallery_img_get_option[ 'gallery_img_light_box_size_fix'],
			'galleryID'				  => $id,
			'liquidSliderInterval'	  => $gallery[0]->description
		);

		if ( $gallery_img_get_option[ 'gallery_img_light_box_size_fix' ] == 'false' ) {
			$lightbox['lightbox_width'] = '';
		} else {
			$lightbox['lightbox_width'] = $gallery_img_get_option[ 'gallery_img_light_box_width' ];
		}

		if ( $gallery_img_get_option[ 'gallery_img_light_box_size_fix' ] == 'false' ) {
			$lightbox['lightbox_height'] = '';
		} else {
			$lightbox['lightbox_height'] = $gallery_img_get_option[ 'gallery_img_light_box_height' ];
		}

		$pos = $gallery_default_params[ 'gallery_img_lightbox_open_position' ];
		switch ( $pos ) {
			case 1:
				$lightbox['lightbox_top']    = '10%';
				$lightbox['lightbox_bottom'] = 'false';
				$lightbox['lightbox_left']   = '10%';
				$lightbox['lightbox_right']  = 'false';
				break;
			case 2:
				$lightbox['lightbox_top']    = '10%';
				$lightbox['lightbox_bottom'] = 'false';
				$lightbox['lightbox_left']   = 'false';
				$lightbox['lightbox_right']  = 'false';
				break;
			case 3:
				$lightbox['lightbox_top']    = '10%';
				$lightbox['lightbox_bottom'] = 'false';
				$lightbox['lightbox_left']   = 'false';
				$lightbox['lightbox_right']  = '10%';
				break;
			case 4:
				$lightbox['lightbox_top']    = 'false';
				$lightbox['lightbox_bottom'] = 'false';
				$lightbox['lightbox_left']   = '10%';
				$lightbox['lightbox_right']  = 'false';
				break;
			case 5:
				$lightbox['lightbox_top']    = 'false';
				$lightbox['lightbox_bottom'] = 'false';
				$lightbox['lightbox_left']   = 'false';
				$lightbox['lightbox_right']  = 'false';
				break;
			case 6:
				$lightbox['lightbox_top']    = 'false';
				$lightbox['lightbox_bottom'] = 'false';
				$lightbox['lightbox_left']   = 'false';
				$lightbox['lightbox_right']  = '10%';
				break;
			case 7:
				$lightbox['lightbox_top']    = 'false';
				$lightbox['lightbox_bottom'] = '10%';
				$lightbox['lightbox_left']   = '10%';
				$lightbox['lightbox_right']  = 'false';
				break;
			case 8:
				$lightbox['lightbox_top']    = 'false';
				$lightbox['lightbox_bottom'] = '10%';
				$lightbox['lightbox_left']   = 'false';
				$lightbox['lightbox_right']  = 'false';
				break;
			case 9:
				$lightbox['lightbox_top']    = 'false';
				$lightbox['lightbox_bottom'] = '10%';
				$lightbox['lightbox_left']   = 'false';
				$lightbox['lightbox_right']  = '10%';
				break;
		}

		$justified        = array(
			'imagemargin'            => $gallery_default_params[ 'gallery_img_ht_view8_element_padding' ],
			'imagerandomize'         => $gallery_default_params[ 'gallery_img_ht_view8_element_randomize' ],
			'imagecssAnimation'      => $gallery_default_params[ 'gallery_img_ht_view8_element_cssAnimation' ],
			'imagecssAnimationSpeed' => $gallery_default_params[ 'gallery_img_ht_view8_element_animation_speed' ],
			'imageheight'            => $gallery_default_params[ 'gallery_img_ht_view8_element_height' ],
			'imagejustify'           => $gallery_default_params[ 'gallery_img_ht_view8_element_justify' ],
			'imageshowcaption'       => $gallery_default_params[ 'gallery_img_ht_view8_element_show_caption' ]
		);
		$justified_params = array();
		foreach ( $justified as $name => $value ) {
			$justified_params[ $name ] = $value;
		}

		wp_localize_script( 'front-end-js-'.$view_slug, 'param_obj', $gallery_default_params );
		wp_localize_script( 'front-end-js-'.$view_slug, 'gallery_obj', $gallery );
		wp_localize_script( 'front-end-js-'.$view_slug, 'adminUrl', $admin_url );
		wp_localize_script( 'front-end-js-'.$view_slug, 'postID', $pID );
		wp_localize_script( 'front-end-js-'.$view_slug, 'postID', $pID );
		wp_localize_script( 'jquery.gicolorbox-js', 'lightbox_obj', $lightbox );
		wp_localize_script( 'custom-js', 'galleryId', $id );
		wp_localize_script( 'jusiifed-js', 'justified_obj', $justified );
	}
}

new Gallery_Img_Frontend_Scripts();