<?php
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

/*
    =============================================
    Enqueue swiper script and styles
    =============================================
*/
function AV_enqueue_swiper_func() {    
    wp_register_script('swiper', get_stylesheet_directory_uri() . '/includes/swiper/js/swiper.min.js', '', '', true);
    wp_enqueue_script('swiper');
    wp_enqueue_style('swiper', get_stylesheet_directory_uri() . '/includes/swiper/css/swiper.min.css');
}
add_action('wp_enqueue_scripts', 'AV_enqueue_swiper_func');