<?php
/**
 * Register pattern category and include seed pattern(s).
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'init', function() {
    register_block_pattern_category(
        'wpnfinite',
        array( 'label' => __( 'WPNfinite', 'wpnfinite' ) )
    );
} );
