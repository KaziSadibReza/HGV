<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remove Kadence settings meta box from 'testimonials' post type
 *
 * @return void
 */
function remove_kadence_settings_for_testimonials() {
    // Replace 'your_cpt_slug' with the actual slug of your custom post type.
    remove_meta_box( '_kad_classic_meta_control', 'testimonials', 'side' );
}
add_action( 'add_meta_boxes', 'remove_kadence_settings_for_testimonials', 20 );