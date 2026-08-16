<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wpnfinite_elementor_setup() {
    add_theme_support( 'elementor' );
    add_theme_support( 'elementor-pro' );
    add_theme_support( 'elementor-post-thumbnails' );
    add_theme_support( 'elementor-page-templates' );
}
add_action( 'after_setup_theme', 'wpnfinite_elementor_setup' );

function wpnfinite_elementor_content_width() {
    return 1240;
}
add_filter( 'elementor/page_templates/canvas/page_content_width', 'wpnfinite_elementor_content_width' );
add_filter( 'elementor/page_templates/header-footer/page_content_width', 'wpnfinite_elementor_content_width' );

function wpnfinite_register_page_templates( $page_templates, $theme, $post ) {
    $page_templates['templates/template-full-width.php'] = __( 'WPNfinite Full Width', 'wpnfinite' );
    $page_templates['templates/template-narrow-content.php'] = __( 'WPNfinite Narrow Content', 'wpnfinite' );
    $page_templates['templates/template-elementor-canvas.php'] = __( 'WPNfinite Elementor Canvas', 'wpnfinite' );
    return $page_templates;
}
add_filter( 'theme_page_templates', 'wpnfinite_register_page_templates', 10, 3 );
