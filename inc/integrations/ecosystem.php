<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function wpnfinite_has_shopblocks() {
    return post_type_exists( 'blog' ) || post_type_exists( 'collection' ) || defined( 'SHOPBLOCKS_VERSION' );
}

function wpnfinite_has_nfinite_dash() {
    return post_type_exists( 'my_projects' ) || defined( 'NFINITE_DASH_VERSION' );
}

function wpnfinite_has_woocommerce() {
    return class_exists( 'WooCommerce' );
}

function wpnfinite_has_nfinite_creators() {
    return post_type_exists( 'nfinite_creator' ) || defined( 'NFINITE_CREATORS_VERSION' );
}

function wpnfinite_ecosystem_body_classes( $classes ) {
    if ( wpnfinite_has_shopblocks() ) { $classes[] = 'wpnfinite-shopblocks-active'; }
    if ( wpnfinite_has_nfinite_dash() ) { $classes[] = 'wpnfinite-nfinite-active'; }
    if ( wpnfinite_has_woocommerce() ) { $classes[] = 'wpnfinite-woocommerce-active'; }
    if ( wpnfinite_has_nfinite_creators() ) { $classes[] = 'wpnfinite-creators-active'; }
    return $classes;
}
add_filter( 'body_class', 'wpnfinite_ecosystem_body_classes' );

function wpnfinite_ecosystem_assets() {
    if ( wpnfinite_has_shopblocks() ) {
        wp_enqueue_style( 'wpnfinite-shopblocks', WPNFINITE_URI . '/assets/css/integrations/shopblocks.css', array(), WPNFINITE_VERSION );
    }
    if ( wpnfinite_has_nfinite_dash() ) {
        wp_enqueue_style( 'wpnfinite-nfinite', WPNFINITE_URI . '/assets/css/integrations/nfinite.css', array(), WPNFINITE_VERSION );
    }
    if ( wpnfinite_has_nfinite_creators() ) {
        wp_enqueue_style( 'wpnfinite-creators', WPNFINITE_URI . '/assets/css/integrations/creators.css', array(), WPNFINITE_VERSION );
        wp_enqueue_style( 'wpnfinite-creators-platform', WPNFINITE_URI . '/assets/css/integrations/creators-platform.css', array( 'wpnfinite-creators' ), WPNFINITE_VERSION );
        wp_enqueue_script( 'wpnfinite-creators-platform', WPNFINITE_URI . '/assets/js/creators-platform.js', array(), WPNFINITE_VERSION, true );
    }
}
add_action( 'wp_enqueue_scripts', 'wpnfinite_ecosystem_assets', 20 );

/**
 * Load WooCommerce integration styles after WooCommerce's own frontend CSS.
 *
 * Keeping this separate from the other ecosystem styles gives WPNfinite the
 * final cascade on Shop/Product screens without increasing specificity across
 * the rest of the site.
 */
function wpnfinite_woocommerce_integration_assets() {
    if ( ! wpnfinite_has_woocommerce() ) {
        return;
    }

    wp_enqueue_style(
        'wpnfinite-woocommerce',
        WPNFINITE_URI . '/assets/css/integrations/woocommerce.css',
        array(),
        WPNFINITE_VERSION
    );
}
add_action( 'wp_enqueue_scripts', 'wpnfinite_woocommerce_integration_assets', 100 );
