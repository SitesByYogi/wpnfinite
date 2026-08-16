<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wpnfinite_is_elementor_canvas() {
    if ( ! is_singular() || ! class_exists( '\Elementor\Plugin' ) ) {
        return false;
    }
    $document = \Elementor\Plugin::$instance->documents->get( get_the_ID() );
    if ( ! $document || ! method_exists( $document, 'get_settings' ) ) {
        return false;
    }
    return 'elementor_canvas' === $document->get_settings( 'template' );
}

function wpnfinite_read_more_link() {
    return sprintf( '<a class="wpnfinite-read-more" href="%1$s">%2$s</a>', esc_url( get_permalink() ), esc_html__( 'Read more', 'wpnfinite' ) );
}
