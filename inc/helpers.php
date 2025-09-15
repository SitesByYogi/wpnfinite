<?php
/**
 * Helpers: Vite asset loader with dev-server support.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Return Vite dev server URL if running, else false.
 */
function wpnfinite_vite_dev_url() {
    $host = apply_filters( 'wpnfinite_vite_host', 'http://localhost:5173' );
    // Quick check: use a transient flag file created by `npm run dev` script.
    $flag = get_stylesheet_directory() . '/.vite-dev';
    if ( file_exists( $flag ) ) {
        return rtrim( $host, '/' );
    }
    return false;
}

/**
 * Locate built asset path via Vite manifest.
 */
function wpnfinite_asset( $entry ) {
    $manifest_path = get_stylesheet_directory() . '/assets/manifest.json';
    if ( ! file_exists( $manifest_path ) ) {
        return '';
    }
    $manifest = json_decode( file_get_contents( $manifest_path ), true );
    return isset( $manifest[ $entry ]['file'] ) ? trailingslashit( get_stylesheet_directory_uri() ) . 'assets/' . $manifest[ $entry ]['file'] : '';
}
