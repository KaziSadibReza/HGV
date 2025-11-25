<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Display popularity badge for licences
 *
 * @param int $post_id Optional post ID. If not provided, uses current post ID.
 * @return string HTML output for popularity badge
 */
function display_licence_popularity( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	// Get the ACF field value
	$popularity = get_field( 'popularity', $post_id );

	// Check if popularity is set to "Most Popular"
	if ( $popularity === 'Most Popular' ) {
		return '<span class="licence-popularity-badge">⭐Most Popular</span>';
	}

	// Return empty string if "None" or not set
	return '<span class="no_popularity"></span>';
}

/**
 * Shortcode to display licence popularity badge
 * Usage: [licence_popularity] or [licence_popularity post_id="123"]
 */
function licence_popularity_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'post_id' => null,
	), $atts );

	return display_licence_popularity( $atts['post_id'] );
}
add_shortcode( 'licence_popularity', 'licence_popularity_shortcode' );