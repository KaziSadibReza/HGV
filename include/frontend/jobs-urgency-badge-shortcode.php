<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Display urgency badge for jobs
 *
 * @param int $post_id Optional post ID. If not provided, uses current post ID.
 * @return string HTML output for urgency badge
 */
function display_jobs_urgency( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	// Get the ACF field value
	$urgency = get_field( 'job_urgency', $post_id );

	// Check if urgency is set to "Most Popular"
	if ( $urgency === 'Urgent' ) {
		return '<span class="jobs-urgency-badge">Urgent</span>';
	}

    if ( $urgency === 'High Priority' ) {
        return '<span class="jobs-urgency-badge">High Priority</span>';
    }

	// Return empty string if "None" or not set
	return '<span class="no_urgency"></span>';
}

/**
 * Shortcode to display jobs urgency badge
 * Usage: [jobs_urgency] or [jobs_urgency post_id="123"]
 */
function jobs_urgency_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'post_id' => null,
	), $atts );

	return display_jobs_urgency( $atts['post_id'] );
}
add_shortcode( 'jobs_urgency', 'jobs_urgency_shortcode' );