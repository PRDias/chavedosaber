<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<div class="wrap" >
    <?php require(GALLERY_IMG_TEMPLATES_PATH . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'gallery-img-admin-free-banner.php'); ?>
    <div style="clear:both;"></div>
    <div id="post-body-heading">
        <h3 id="gen_option_title"><?php echo __( 'Lightbox Options', 'gallery-img' ); ?></h3>
        <a onclick="document.getElementById('adminForm').submit()"
           class="save-gallery-options button-primary"><?php echo __( 'Save', 'gallery-img' ); ?></a>
    </div>
    <form action="admin.php?page=Options_gallery_lightbox_styles&task=save" method="post" id="adminForm"
          name="adminForm">
        <div class="lightbox-options-block">
            <h3><?php echo __( 'Dimensions', 'gallery-img' ); ?></h3>

            <div class="has-background">
                <label for="light_box_size_fix"><?php echo __( 'Popup size fix', 'gallery-img' ); ?></label>
                <input type="hidden" value="false" name="params[gallery_img_light_box_size_fix]"/>
                <input type="checkbox"
                       id="light_box_size_fix" <?php if ( $gallery_img_get_option[ 'gallery_img_light_box_size_fix' ] == 'true' ) {
                    echo 'checked="checked"';
                } ?> name="params[gallery_img_light_box_size_fix]" value="true"/>
            </div>

            <div class="fixed-size">
                <label for="light_box_width"><?php echo __( 'Popup width', 'gallery-img' ); ?></label>
                <input type="number" name="params[gallery_img_light_box_width]" id="light_box_width"
                       value="<?php echo $gallery_img_get_option[ 'gallery_img_light_box_width' ]; ?>" class="text">
                <span>px</span>
            </div>

            <div class="has-background fixed-size">
                <label for="light_box_height"><?php echo __( 'Popup height', 'gallery-img' ); ?></label>
                <input type="number" name="params[gallery_img_light_box_height]" id="light_box_height"
                       value="<?php echo $gallery_img_get_option[ 'gallery_img_light_box_height' ]; ?>" class="text">
                <span>px</span>
            </div>

            <div class="not-fixed-size">
                <label for="light_box_maxwidth"><?php echo __( 'Popup maxWidth', 'gallery-img' ); ?></label>
                <input type="number" name="params[gallery_img_light_box_maxwidth]" id="light_box_maxwidth"
                       value="<?php echo $gallery_img_get_option[ 'gallery_img_light_box_maxwidth' ]; ?>" class="text">
                <span>px</span>
            </div>
            <div class="has-background not-fixed-size">
                <label
                    for="light_box_maxheight"><?php echo __( 'Popup maxHeight', 'gallery-img' ); ?></label>
                <input type="number" name="params[gallery_img_light_box_maxheight]" id="light_box_maxheight"
                       value="<?php echo $gallery_img_get_option[ 'gallery_img_light_box_maxheight' ]; ?>" class="text">
                <span>px</span>
            </div>
            <div>
                <label
                    for="light_box_initialwidth"><?php echo __( 'Popup initial width', 'gallery-img' ); ?></label>
                <input type="number" name="params[gallery_img_light_box_initialwidth]" id="light_box_initialwidth"
                       value="<?php echo $gallery_img_get_option[ 'gallery_img_light_box_initialwidth' ]; ?>" class="text">
                <span>px</span>
            </div>
            <div class="has-background">
                <label
                    for="light_box_initialheight"><?php echo __( 'Popup initial height', 'gallery-img' ); ?></label>
                <input type="number" name="params[gallery_img_light_box_initialheight]" id="light_box_initialheight"
                       value="<?php echo $gallery_img_get_option[ 'gallery_img_light_box_initialheight' ]; ?>" class="text">
                <span>px</span>
            </div>
        </div>
        <div class="lightbox-options-block lightbox-grey-wrapper" >
            <h3 class="section_for_pro" ><?php echo __( 'Internationalization', 'gallery-img' ); ?></h3>
            <?php include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); ?>
                <div class="has-background">
                    <label for="light_box_style"><?php echo __( 'Lightbox style', 'gallery-img' ); ?></label>
                    <select id="light_box_style" >
                        <option <?php if ( $gallery_img_get_default_options[ 'gallery_img_light_box_style' ] == '1' ) {
                            echo 'selected="selected"';
                        } ?> value="1">1
                        </option>
                        <option <?php if ( $gallery_img_get_default_options[ 'gallery_img_light_box_style' ] == '2' ) {
                            echo 'selected="selected"';
                        } ?> value="2">2
                        </option>
                        <option <?php if ( $gallery_img_get_default_options[ 'gallery_img_light_box_style' ] == '3' ) {
                            echo 'selected="selected"';
                        } ?> value="3">3
                        </option>
                        <option <?php if ( $gallery_img_get_default_options[ 'gallery_img_light_box_style' ] == '4' ) {
                            echo 'selected="selected"';
                        } ?> value="4">4
                        </option>
                        <option <?php if ( $gallery_img_get_default_options[ 'gallery_img_light_box_style' ] == '5' ) {
                            echo 'selected="selected"';
                        } ?> value="5">5
                        </option>
                    </select>
                </div>
            <div>
                <label
                    for="light_box_transition"><?php echo __( 'Transition type', 'gallery-img' ); ?></label>
                <select id="light_box_transition" >
                    <option <?php if ( $gallery_img_get_default_options[ 'gallery_img_light_box_transition' ] == 'elastic' ) {
                        echo 'selected="selected"';
                    } ?> value="elastic"><?php echo __( 'Elastic', 'gallery-img' ); ?></option>
                    <option <?php if ( $gallery_img_get_default_options[ 'gallery_img_light_box_transition' ] == 'fade' ) {
                        echo 'selected="selected"';
                    } ?> value="fade"><?php echo __( 'Fade', 'gallery-img' ); ?></option>
                    <option <?php if ( $gallery_img_get_default_options[ 'gallery_img_light_box_transition' ] == 'none' ) {
                        echo 'selected="selected"';
                    } ?> value="none"><?php echo __( 'none', 'gallery-img' ); ?></option>
                </select>
            </div>
            <div class="has-background">
                <label for="light_box_speed"><?php echo __( 'Opening speed', 'gallery-img' ); ?></label>
                <input type="number" id="light_box_speed"
                       value="<?php echo $gallery_img_get_default_options[ 'gallery_img_light_box_speed' ]; ?>" class="text">
                <span><?php echo __( 'ms', 'gallery-img' ); ?></span>
            </div>
            <div>
                <label for="light_box_fadeout"><?php echo __( 'Closing speed', 'gallery-img' ); ?></label>
                <input type="number" id="light_box_fadeout"
                       value="<?php echo $gallery_img_get_default_options[ 'gallery_img_light_box_fadeout' ]; ?>" class="text">
                <span><?php echo __( 'ms', 'gallery-img' ); ?></span>
            </div>
            <div class="has-background">
                <label for="light_box_title"><?php echo __( 'Show the title', 'gallery-img' ); ?></label>
                <input type="hidden" value="false" />
                <input type="checkbox"
                       id="light_box_title" <?php if ( $gallery_img_get_default_options[ 'gallery_img_light_box_title' ] == 'true' ) {
                    echo 'checked="checked"';
                } ?>  value="true"/>
            </div>
            <div>
                <label
                    for="light_box_opacity"><?php echo __( 'Overlay transparency', 'gallery-img' ); ?></label>
                <div class="slider-container">
                    <input  id="light_box_opacity" data-slider-highlight="true"
                           data-slider-values="0,10,20,30,40,50,60,70,80,90,100" type="text" data-slider="true"
                           value="<?php echo $gallery_img_get_default_options[ 'gallery_img_light_box_opacity' ]; ?>"/>
                    <span><?php echo $gallery_img_get_default_options[ 'gallery_img_light_box_opacity' ]; ?>%</span>
                </div>
            </div>
            <div class="has-background">
                <label for="light_box_open"><?php echo __( 'Auto open', 'gallery-img' ); ?></label>
                <input type="hidden" value="false" />
                <input type="checkbox"
                       id="light_box_open" <?php if ( $gallery_img_get_default_options[ 'gallery_img_light_box_open' ] == 'true' ) {
                    echo 'checked="checked"';
                } ?> />
            </div>
            <div>
                <label
                    for="light_box_overlayclose"><?php echo __( 'Overlay close', 'gallery-img' ); ?><?php if ( $gallery_img_get_default_options[ 'gallery_img_light_box_overlayclose' ] ) {
                        echo "true";
                    } ?></label>
                <input type="hidden" value="false" />
                <input type="checkbox"
                       id="light_box_overlayclose" <?php if ( $gallery_img_get_default_options[ 'gallery_img_light_box_overlayclose' ] == 'true' ) {
                    echo 'checked="checked"';
                } ?> value="true"/>
            </div>
            <div class="has-background">
                <label for="light_box_esckey"><?php echo __( 'EscKey close', 'gallery-img' ); ?></label>
                <input type="hidden" value="false" />
                <input type="checkbox"
                       id="light_box_esckey" <?php if ( $gallery_img_get_default_options[ 'gallery_img_light_box_esckey' ] == 'true' ) {
                    echo 'checked="checked"';
                } ?> />
            </div>
            <div>
                <label
                    for="light_box_arrowkey"><?php echo __( 'Keyboard navigation', 'gallery-img' ); ?></label>
                <input type="hidden" value="false" />
                <input type="checkbox"
                       id="light_box_arrowkey" <?php if ( $gallery_img_get_default_options[ 'gallery_img_light_box_arrowkey' ] == 'true' ) {
                    echo 'checked="checked"';
                } ?> />
            </div>
            <div class="has-background">
                <label for="light_box_loop"><?php echo __( 'Loop content', 'gallery-img' ); ?></label>
                <input type="hidden" value="false" />
                <input type="checkbox"
                       id="light_box_loop" <?php if ( $gallery_img_get_default_options[ 'gallery_img_light_box_loop' ] == 'true' ) {
                    echo 'checked="checked"';
                } ?> />
            </div>
            <div>
                <label
                    for="light_box_closebutton"><?php echo __( 'Show close button', 'gallery-img' ); ?></label>
                <input type="hidden" value="false" />
                <input type="checkbox"
                       id="light_box_closebutton" <?php if ( $gallery_img_get_default_options[ 'gallery_img_light_box_closebutton' ] == 'true' ) {
                    echo 'checked="checked"';
                } ?>  value="true"/>
            </div>
        </div>
        <div class="lightbox-options-block lightbox-grey-wrapper" style="margin-top:-310px" >
            <h3 class="section_for_pro"><?php echo __( 'Slideshow', 'gallery-img' ); ?></h3>
            <div class="has-background">
                <label for="light_box_slideshow"><?php echo __( 'Slideshow', 'gallery-img' ); ?></label>
                <input type="hidden" value="false" />
                <input type="checkbox"
                       id="light_box_slideshow" <?php if ( $gallery_img_get_default_options[ 'gallery_img_light_box_slideshow' ] == 'true' ) {
                    echo 'checked="checked"';
                } ?>  value="true"/>
            </div>
            <div>
                <label
                    for="light_box_slideshowspeed"><?php echo __( 'Slideshow interval', 'gallery-img' ); ?></label>
                <input type="number"  id="light_box_slideshowspeed"
                       value="<?php echo $gallery_img_get_default_options[ 'gallery_img_light_box_slideshowspeed' ]; ?>" class="text">
                <span><?php echo __( 'ms', 'gallery-img' ); ?></span>
            </div>
            <div class="has-background">
                <label
                    for="light_box_slideshowauto"><?php echo __( 'Slideshow auto start', 'gallery-img' ); ?></label>
                <input type="hidden" value="false" />
                <input type="checkbox"
                       id="light_box_slideshowauto" <?php if ( $gallery_img_get_default_options[ 'gallery_img_light_box_slideshowauto' ] == 'true' ) {
                    echo 'checked="checked"';
                } ?>  value="true"/>
            </div>
            <div>
                <label
                    for="light_box_slideshowstart"><?php echo __( 'Slideshow start button text', 'gallery-img' ); ?></label>
                <input type="text"  id="light_box_slideshowstart"
                       value="<?php echo esc_attr( $gallery_img_get_default_options[ 'gallery_img_light_box_slideshowstart' ] ); ?>" class="text">
            </div>
            <div class="has-background">
                <label
                    for="light_box_slideshowstop"><?php echo __( 'Slideshow stop button text', 'gallery-img' ); ?></label>
                <input type="text"  id="light_box_slideshowstop"
                       value="<?php echo esc_attr( $gallery_img_get_default_options[ 'gallery_img_light_box_slideshowstop' ] ); ?>" class="text">
            </div>
        </div>
        <div class="lightbox-options-block lightbox-grey-wrapper" style="margin-top: -20px;">
            <h3 class="section_for_pro"><?php echo __( 'Positioning', 'gallery-img' ); ?></h3>

            <div class="has-background">
                <label for="light_box_fixed"><?php echo __( 'Fixed position', 'gallery-img' ); ?></label>
                <input type="hidden" value="false" />
                <input type="checkbox"
                       id="light_box_fixed" <?php if ( $gallery_img_get_default_options[ 'gallery_img_light_box_fixed' ] == 'true' ) {
                    echo 'checked="checked"';
                } ?>  value="true"/>
            </div>
            <div class="has-height">
                <label for=""><?php echo __( 'Popup position', 'gallery-img' ); ?></label>
                <div>
                    <table class="bws_position_table">
                        <tbody>
                        <tr>
                            <td><input type="radio" value="1" id="slideshow_title_top-left"
                                        <?php if ( $gallery_img_get_default_options[ 'gallery_img_lightbox_open_position' ] == '1' ) {
                                    echo 'checked="checked"';
                                } ?> /></td>
                            <td><input type="radio" value="2" id="slideshow_title_top-center"
                                        <?php if ( $gallery_img_get_default_options[ 'gallery_img_lightbox_open_position' ] == '2' ) {
                                    echo 'checked="checked"';
                                } ?> /></td>
                            <td><input type="radio" value="3" id="slideshow_title_top-right"
                                        <?php if ( $gallery_img_get_default_options[ 'gallery_img_lightbox_open_position' ] == '3' ) {
                                    echo 'checked="checked"';
                                } ?> /></td>
                        </tr>
                        <tr>
                            <td><input type="radio" value="4" id="slideshow_title_middle-left"
                                        <?php if ( $gallery_img_get_default_options[ 'gallery_img_lightbox_open_position' ] == '4' ) {
                                    echo 'checked="checked"';
                                } ?> /></td>
                            <td><input type="radio" value="5" id="slideshow_title_middle-center"
                                        <?php if ( $gallery_img_get_default_options[ 'gallery_img_lightbox_open_position' ] == '5' ) {
                                    echo 'checked="checked"';
                                } ?> /></td>
                            <td><input type="radio" value="6" id="slideshow_title_middle-right"
                                        <?php if ( $gallery_img_get_default_options[ 'gallery_img_lightbox_open_position' ] == '6' ) {
                                    echo 'checked="checked"';
                                } ?> /></td>
                        </tr>
                        <tr>
                            <td><input type="radio" value="7" id="slideshow_title_bottom-left"
                                        <?php if ( $gallery_img_get_default_options[ 'gallery_img_lightbox_open_position' ] == '7' ) {
                                    echo 'checked="checked"';
                                } ?> /></td>
                            <td><input type="radio" value="8" id="slideshow_title_bottom-center"
                                        <?php if ( $gallery_img_get_default_options[ 'gallery_img_lightbox_open_position' ] == '8' ) {
                                    echo 'checked="checked"';
                                } ?> /></td>
                            <td><input type="radio" value="9" id="slideshow_title_bottom-right"
                                        <?php if ( $gallery_img_get_default_options[ 'gallery_img_lightbox_open_position' ] == '9' ) {
                                    echo 'checked="checked"';
                                } ?> /></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php wp_nonce_field('huge_it_gallery_nonce_save_lightbox_options','huge_it_gallery_nonce_save_lightbox_options'); ?>
    </form>
</div>