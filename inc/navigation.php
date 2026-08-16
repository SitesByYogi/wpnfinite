<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function wpnfinite_auto_nav_items( $location = 'primary' ) {
    $items = array();
    $home_label = __( 'Home', 'wpnfinite' );
    $items[] = array( 'label' => $home_label, 'url' => home_url( '/' ) );

    if ( 'topics' === $location ) {
        $terms = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => true, 'number' => 8, 'orderby' => 'count', 'order' => 'DESC' ) );
        if ( ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) { $items[] = array( 'label' => $term->name, 'url' => get_term_link( $term ) ); }
        }
        return $items;
    }

    foreach ( array( 'blog' => __( 'Articles', 'wpnfinite' ), 'collection' => __( 'Shop', 'wpnfinite' ) ) as $type => $label ) {
        if ( post_type_exists( $type ) ) {
            $link = get_post_type_archive_link( $type );
            if ( $link ) { $items[] = array( 'label' => $label, 'url' => $link ); }
        }
    }

    $page_for_posts = (int) get_option( 'page_for_posts' );
    if ( $page_for_posts ) { $items[] = array( 'label' => get_the_title( $page_for_posts ), 'url' => get_permalink( $page_for_posts ) ); }

    $page_limit = 'footer' === $location ? 5 : 4;
    $pages = get_pages( array( 'parent' => 0, 'sort_column' => 'menu_order,post_title', 'number' => $page_limit ) );
    foreach ( $pages as $page ) {
        if ( $page_for_posts && (int) $page->ID === $page_for_posts ) { continue; }
        if ( (int) $page->ID === (int) get_option( 'page_on_front' ) ) { continue; }
        $items[] = array( 'label' => get_the_title( $page ), 'url' => get_permalink( $page ) );
    }
    return array_slice( $items, 0, 'footer' === $location ? 7 : 6 );
}

function wpnfinite_render_auto_nav( $location = 'primary', $class = 'wpnfinite-menu' ) {
    $items = wpnfinite_auto_nav_items( $location );
    if ( ! $items ) { return; }
    echo '<ul class="' . esc_attr( $class ) . '">';
    foreach ( $items as $item ) {
        if ( is_wp_error( $item['url'] ) ) { continue; }
        echo '<li><a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a></li>';
    }
    echo '</ul>';
}

function wpnfinite_primary_menu_fallback() { wpnfinite_render_auto_nav( 'primary', 'wpnfinite-menu' ); }
function wpnfinite_topics_menu_fallback() { wpnfinite_render_auto_nav( 'topics', 'wpnfinite-topics-menu' ); }
function wpnfinite_footer_menu_fallback() { wpnfinite_render_auto_nav( 'footer', 'wpnfinite-footer-menu' ); }
