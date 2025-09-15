<?php
/**
 * Handle front-end assets through Vite.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_enqueue_scripts', function() {
    $handle = 'wpnfinite-app';

    if ( $dev = wpnfinite_vite_dev_url() ) {
        // Dev: load from Vite server with module scripts (no versioning needed).
        wp_enqueue_script( $handle, $dev . '/src/js/app.js', array(), null, true );
        // Inject HMR client; Vite handles CSS injection.
        wp_add_inline_script( $handle, 'window.__vite_is_wp = true;', 'before' );
    } else {
        // Prod: use manifest to load built assets.
        $js  = wpnfinite_asset( 'src/js/app.js' );
        $css = wpnfinite_asset( 'src/css/app.css' );

        if ( $css ) {
            wp_enqueue_style( $handle, $css, array(), WPNFINITE_VERSION );
        }
        if ( $js ) {
            wp_enqueue_script( $handle, $js, array(), WPNFINITE_VERSION, true );
        }
    }
}, 20 );
