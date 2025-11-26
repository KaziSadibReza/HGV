<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remove Kadence settings meta box from 'jobs' post type
 *
 * @return void
 */
function remove_kadence_settings_for_jobs() {
    // Replace 'your_cpt_slug' with the actual slug of your custom post type.
    remove_meta_box( '_kad_classic_meta_control', 'jobs', 'side' );
}
add_action( 'add_meta_boxes', 'remove_kadence_settings_for_jobs', 20 );


/**
 * Remove the content editor for the 'property' post type
 *
 * @return void
 */
function remove_editor_jobs() {
    remove_post_type_support( 'jobs', 'editor' );
}
add_action( 'admin_menu' , 'remove_editor_jobs' );

/**
 * Remove default Featured Image box
 * @return void
 */
add_action('do_meta_boxes', function() {
    remove_meta_box('postimagediv', 'jobs', 'side');
});