<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WPNFINITE_VERSION', '1.1.3' );
define( 'WPNFINITE_PATH', get_template_directory() );
define( 'WPNFINITE_URI', get_template_directory_uri() );

require_once WPNFINITE_PATH . '/inc/setup.php';
require_once WPNFINITE_PATH . '/inc/enqueue.php';
require_once WPNFINITE_PATH . '/inc/template-functions.php';
require_once WPNFINITE_PATH . '/inc/helpers.php';
require_once WPNFINITE_PATH . '/inc/editor.php';
require_once WPNFINITE_PATH . '/inc/elementor.php';
require_once WPNFINITE_PATH . '/inc/media-home.php';
require_once WPNFINITE_PATH . '/inc/navigation.php';
require_once WPNFINITE_PATH . '/inc/integrations/ecosystem.php';
