<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php if ( ! wpnfinite_is_elementor_canvas() ) : ?>
<header class="wpnfinite-header" id="site-header">
    <div class="wpnfinite-container wpnfinite-header-inner">
        <div class="wpnfinite-branding">
            <?php if ( has_custom_logo() ) : ?>
                <div class="wpnfinite-logo"><?php the_custom_logo(); ?></div>
            <?php else : ?>
                <a class="wpnfinite-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
                    <span class="wpnfinite-brand-mark"><?php echo esc_html( strtoupper( substr( get_bloginfo( 'name' ), 0, 1 ) ) ); ?></span>
                    <span class="wpnfinite-brand-text"><span class="wpnfinite-brand-title"><?php bloginfo( 'name' ); ?></span><span class="wpnfinite-brand-subtitle"><?php bloginfo( 'description' ); ?></span></span>
                </a>
            <?php endif; ?>
        </div>
        <button class="wpnfinite-nav-toggle" aria-expanded="false" aria-controls="site-navigation"><span></span><span></span><span></span><span class="screen-reader-text"><?php esc_html_e( 'Toggle navigation', 'wpnfinite' ); ?></span></button>
        <nav id="site-navigation" class="wpnfinite-navigation" aria-label="<?php esc_attr_e( 'Primary menu', 'wpnfinite' ); ?>">
            <?php wp_nav_menu( array( 'theme_location' => 'primary', 'menu_id' => 'primary-menu', 'container' => false, 'menu_class' => 'wpnfinite-menu', 'fallback_cb' => 'wpnfinite_primary_menu_fallback', 'depth' => 2 ) ); ?>
        </nav>
        <div class="wpnfinite-header-actions">
            <a class="wpnfinite-search-link" href="<?php echo esc_url( home_url( '/?s=' ) ); ?>" aria-label="<?php esc_attr_e( 'Search', 'wpnfinite' ); ?>">⌕</a>
            <a class="wpnfinite-btn wpnfinite-btn-primary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Get Started', 'wpnfinite' ); ?></a>
        </div>
    </div>
    <div class="wpnfinite-topics-bar">
        <div class="wpnfinite-container">
            <span class="wpnfinite-topics-label"><?php esc_html_e( 'Explore', 'wpnfinite' ); ?></span>
            <nav aria-label="<?php esc_attr_e( 'Topics menu', 'wpnfinite' ); ?>">
                <?php wp_nav_menu( array( 'theme_location' => 'topics', 'container' => false, 'menu_class' => 'wpnfinite-topics-menu', 'fallback_cb' => 'wpnfinite_topics_menu_fallback', 'depth' => 1 ) ); ?>
            </nav>
        </div>
    </div>
</header>
<?php endif; ?>
<div id="page" class="site"><main id="primary" class="site-main">
