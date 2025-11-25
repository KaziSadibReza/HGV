<?php

/**
 * ci cd work
 *
 * @return void
 */
/**
 * Enqueue child theme styles
 */
function kadence_child_enqueue_styles() {
    // Load Parent theme css 
    wp_enqueue_style( 'kadence-parent-style', get_template_directory_uri() . '/style.css' );

    // Load Child theme css
    wp_enqueue_style( 'kadence-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('kadence-parent-style')
    );
}
add_action( 'wp_enqueue_scripts', 'kadence_child_enqueue_styles' );

/**
 * Include licenses post type feature
 */
require_once get_stylesheet_directory() . '/include/admin/licenses-post-type.php';

/**
 * Include licenses popularity popularity feature 
 */
require_once get_stylesheet_directory() . '/include/frontend/licenses-popularity-shortcode.php';

/**
 * Include jobs post type feature
 */
 require_once get_stylesheet_directory() . '/include/admin/jobs-post-type.php';

 /**
  * Include jobs urgency badge feature
  */
require_once get_stylesheet_directory() . '/include/frontend/jobs-urgency-badge-shortcode.php';