<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remove Kadence settings meta box from 'Agents' post type
 *
 * @return void
 */
function remove_kadence_settings_for_licences() {
    // Replace 'your_cpt_slug' with the actual slug of your custom post type.
    remove_meta_box( '_kad_classic_meta_control', 'licences', 'side' );
}
add_action( 'add_meta_boxes', 'remove_kadence_settings_for_licences', 20 );