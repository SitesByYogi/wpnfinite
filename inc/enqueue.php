<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wpnfinite_enqueue_assets() {
    $css_files = array(
        'base'       => '/assets/css/base.css',
        'utilities'  => '/assets/css/utilities.css',
        'components' => '/assets/css/components.css',
        'header'     => '/assets/css/header.css',
        'footer'     => '/assets/css/footer.css',
        'blocks'     => '/assets/css/blocks.css',
        'elementor'  => '/assets/css/elementor.css',
        'media-home' => '/assets/css/media-home.css',
        'homepage'   => '/assets/css/homepage.css',
        'editorial'  => '/assets/css/editorial.css',
        'services'   => '/assets/css/services.css',
        'creator-intelligence' => '/assets/css/creator-intelligence.css',
    );
    foreach ( $css_files as $handle => $path ) {
        wp_enqueue_style( 'wpnfinite-' . $handle, WPNFINITE_URI . $path, array(), WPNFINITE_VERSION );
    }
    wp_enqueue_script( 'wpnfinite-theme', WPNFINITE_URI . '/assets/js/theme.js', array(), WPNFINITE_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'wpnfinite_enqueue_assets' );

function wpnfinite_editor_assets() {
    add_editor_style( array( 'assets/css/base.css', 'assets/css/blocks.css', 'assets/css/editorial.css', 'assets/css/services.css', 'assets/css/creator-intelligence.css' ) );
}
add_action( 'after_setup_theme', 'wpnfinite_editor_assets' );
