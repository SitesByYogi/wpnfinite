<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wpnfinite_body_classes( $classes ) {
    if ( is_singular() ) {
        $classes[] = 'wpnfinite-singular';
    }
    if ( class_exists( '\Elementor\Plugin' ) ) {
        $classes[] = 'wpnfinite-elementor-active';
    }
    return $classes;
}
add_filter( 'body_class', 'wpnfinite_body_classes' );

function wpnfinite_pingback_header() {
    if ( is_singular() && pings_open() ) {
        printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
    }
}
add_action( 'wp_head', 'wpnfinite_pingback_header' );

function wpnfinite_posted_on() {
    printf( '<span class="wpnfinite-meta-item">%s</span>', esc_html( get_the_date() ) );
}

function wpnfinite_posted_by() {
    printf( '<span class="wpnfinite-meta-item">%s</span>', esc_html( get_the_author() ) );
}
