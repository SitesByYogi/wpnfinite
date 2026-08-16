<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wpnfinite_register_block_patterns() {
    $pattern_files = array(
        'hero'         => 'Hero Section',
        'pricing'      => 'Pricing Section',
        'faq'          => 'FAQ Section',
        'content-grid' => 'Content Grid',
        'cta'          => 'CTA Strip',
    );
    foreach ( $pattern_files as $slug => $title ) {
        $file = WPNFINITE_PATH . '/patterns/' . $slug . '.php';
        if ( file_exists( $file ) ) {
            register_block_pattern( 'wpnfinite/' . $slug, array(
                'title'      => __( $title, 'wpnfinite' ),
                'categories' => array( 'featured', 'text', 'columns' ),
                'content'    => file_get_contents( $file ),
            ) );
        }
    }
}
add_action( 'init', 'wpnfinite_register_block_patterns' );
