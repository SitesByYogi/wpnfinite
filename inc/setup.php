<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wpnfinite_setup() {
    load_theme_textdomain( 'wpnfinite', WPNFINITE_PATH . '/languages' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'align-wide' );
    add_theme_support( 'editor-styles' );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
    add_theme_support( 'custom-logo', array( 'height' => 80, 'width' => 280, 'flex-width' => true, 'flex-height' => true ) );
    register_nav_menus( array(
        'primary' => __( 'Primary Navigation', 'wpnfinite' ),
        'topics'  => __( 'Topics Navigation', 'wpnfinite' ),
        'footer'  => __( 'Footer Navigation', 'wpnfinite' ),
    ) );
}
add_action( 'after_setup_theme', 'wpnfinite_setup' );

function wpnfinite_content_width() {
    $GLOBALS['content_width'] = apply_filters( 'wpnfinite_content_width', 760 );
}
add_action( 'after_setup_theme', 'wpnfinite_content_width', 0 );
