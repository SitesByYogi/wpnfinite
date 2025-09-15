<?php
/**
 * Micro-hardening without plugins.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// Hide WP version.
add_filter( 'the_generator', '__return_empty_string' );

// Remove emoji scripts/styles on front end for slight perf.
add_action( 'init', function() {
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
} );
