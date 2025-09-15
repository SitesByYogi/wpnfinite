<?php
/**
 * WPNfinite — bootstrap.
 *
 * @package wpnfinite
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'WPNFINITE_VERSION', '0.1.0' );
define( 'WPNFINITE_DIR', trailingslashit( get_stylesheet_directory() ) );
define( 'WPNFINITE_URI', trailingslashit( get_stylesheet_directory_uri() ) );

// Load core includes.
$inc = array(
    'setup',
    'assets',
    'helpers',
    'blocks',
    'patterns',
    'security',
);

foreach ( $inc as $file ) {
    $path = WPNFINITE_DIR . 'inc/' . $file . '.php';
    if ( file_exists( $path ) ) {
        require_once $path;
    }
}
