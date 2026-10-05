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

/**
 * Determine whether the secondary navigation should switch into Videos mode.
 * Supports both the Nfinite rewrite-powered /videos/ hub and a shortcode/page fallback.
 */
function wpnfinite_is_video_secondary_context() {
    if ( 'videos' === sanitize_key( get_query_var( 'nfinite_discovery_hub' ) ) ) {
        return true;
    }

    if ( class_exists( 'Nfinite_Creators_Discovery_Hubs' ) && Nfinite_Creators_Discovery_Hubs::is_hub( 'videos' ) ) {
        return true;
    }

    if ( is_page( 'videos' ) ) {
        return true;
    }

    return false;
}

function wpnfinite_is_music_secondary_context() {
    if ( post_type_exists( 'nfinite_release' ) && ( is_post_type_archive( 'nfinite_release' ) || is_singular( 'nfinite_release' ) ) ) {
        return true;
    }

    if ( taxonomy_exists( 'nfinite_release_type' ) && is_tax( 'nfinite_release_type' ) ) {
        return true;
    }

    if ( is_page( array( 'music', 'radio', 'all-releases', 'beats', 'producers', 'albums', 'mixtapes', 'singles', 'playlists' ) ) ) {
        return true;
    }

    return false;
}

/**
 * Pull matching URLs from the site's assigned Topics menu when available.
 * This preserves the site's existing destinations instead of replacing them.
 */
function wpnfinite_music_menu_saved_urls() {
    $urls      = array();
    $locations = get_nav_menu_locations();

    if ( empty( $locations['topics'] ) ) {
        return $urls;
    }

    $menu = wp_get_nav_menu_object( $locations['topics'] );
    if ( ! $menu ) {
        return $urls;
    }

    $items = wp_get_nav_menu_items( $menu->term_id );
    if ( ! $items || is_wp_error( $items ) ) {
        return $urls;
    }

    foreach ( $items as $item ) {
        $key = strtolower( trim( wp_strip_all_tags( $item->title ) ) );
        if ( $key && ! empty( $item->url ) ) {
            $urls[ $key ] = $item->url;
        }
    }

    return $urls;
}

function wpnfinite_music_release_type_url( $slugs ) {
    if ( ! taxonomy_exists( 'nfinite_release_type' ) ) {
        return '';
    }

    foreach ( (array) $slugs as $slug ) {
        $term = get_term_by( 'slug', $slug, 'nfinite_release_type' );
        if ( $term && ! is_wp_error( $term ) ) {
            $url = get_term_link( $term );
            if ( ! is_wp_error( $url ) ) {
                return $url;
            }
        }
    }

    return '';
}

/**
 * Music-specific secondary navigation. This is rendered only in Music
 * contexts, while the normal Explore Topics menu is used site-wide elsewhere.
 */
function wpnfinite_music_secondary_items() {
    $saved       = wpnfinite_music_menu_saved_urls();
    $music_url   = post_type_exists( 'nfinite_release' ) ? get_post_type_archive_link( 'nfinite_release' ) : home_url( '/music/' );
    $music_url   = $music_url ? $music_url : home_url( '/music/' );
    $radio_url   = home_url( '/radio/' );
    $all_url     = class_exists( 'Nfinite_Creators_Release_Library' ) ? Nfinite_Creators_Release_Library::page_url() : home_url( '/all-releases/' );

    $defaults = array(
        'new music' => $music_url,
        'radio'     => $radio_url,
        'all releases' => $all_url,
        'albums'    => wpnfinite_music_release_type_url( array( 'album', 'albums' ) ),
        'mixtapes'  => wpnfinite_music_release_type_url( array( 'mixtape', 'mixtapes' ) ),
        'singles'   => wpnfinite_music_release_type_url( array( 'single', 'singles' ) ),
        'playlists' => wpnfinite_music_release_type_url( array( 'playlist', 'playlists' ) ),
        'beats'     => home_url( '/beats/' ),
        'producers' => home_url( '/producers/' ),
    );

    $labels = array(
        'new music' => __( 'New Music', 'wpnfinite' ),
        'radio'     => __( 'Radio', 'wpnfinite' ),
        'all releases' => __( 'All Releases', 'wpnfinite' ),
        'albums'    => __( 'Albums', 'wpnfinite' ),
        'mixtapes'  => __( 'Mixtapes', 'wpnfinite' ),
        'singles'   => __( 'Singles', 'wpnfinite' ),
        'playlists' => __( 'Playlists', 'wpnfinite' ),
        'beats'     => __( 'Beats', 'wpnfinite' ),
        'producers' => __( 'Producers', 'wpnfinite' ),
    );

    $items = array();
    foreach ( $labels as $key => $label ) {
        $url = isset( $saved[ $key ] ) ? $saved[ $key ] : $defaults[ $key ];
        if ( ! $url ) {
            // Keep a useful destination even if a release-type term does not yet exist.
            $url = $music_url;
        }
        $items[] = array( 'label' => $label, 'url' => $url, 'key' => $key );
    }

    return $items;
}

function wpnfinite_render_music_secondary_nav() {
    $items = wpnfinite_music_secondary_items();
    if ( ! $items ) { return; }

    echo '<ul class="wpnfinite-topics-menu wpnfinite-music-topics-menu">';
    foreach ( $items as $item ) {
        $classes = array( 'menu-item', 'wpnfinite-music-menu-item' );
        if ( ( 'radio' === $item['key'] && is_page( 'radio' ) ) || ( 'all releases' === $item['key'] && is_page( 'all-releases' ) ) ) {
            $classes[] = 'current-menu-item';
        }
        echo '<li class="' . esc_attr( implode( ' ', $classes ) ) . '"><a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a></li>';
    }
    echo '</ul>';
}

/**
 * Video-specific secondary navigation. The links point into the programming
 * sections provided by Nfinite Creators, keeping navigation in the theme bar
 * instead of duplicating it inside the page content.
 */
function wpnfinite_video_secondary_items() {
    $base = trailingslashit( home_url( '/videos/' ) );

    return array(
        array( 'key' => '',                'label' => __( 'All', 'wpnfinite' ),           'url' => $base ),
        array( 'key' => 'music-videos',    'label' => __( 'Music Videos', 'wpnfinite' ),  'url' => $base . 'music-videos/' ),
        array( 'key' => 'podcasts',        'label' => __( 'Podcasts', 'wpnfinite' ),      'url' => $base . 'podcasts/' ),
        array( 'key' => 'vlogs',           'label' => __( 'Vlogs', 'wpnfinite' ),         'url' => $base . 'vlogs/' ),
        array( 'key' => 'live',            'label' => __( 'Live', 'wpnfinite' ),          'url' => $base . 'live/' ),
        array( 'key' => 'news',            'label' => __( 'News', 'wpnfinite' ),          'url' => $base . 'news/' ),
        array( 'key' => 'commentary',      'label' => __( 'Commentary', 'wpnfinite' ),    'url' => $base . 'commentary/' ),
        array( 'key' => 'documentaries',   'label' => __( 'Documentaries', 'wpnfinite' ), 'url' => $base . 'documentaries/' ),
        array( 'key' => 'movies',          'label' => __( 'Movies', 'wpnfinite' ),        'url' => $base . 'movies/' ),
        array( 'key' => 'interviews',      'label' => __( 'Interviews', 'wpnfinite' ),    'url' => $base . 'interviews/' ),
    );
}

function wpnfinite_render_video_secondary_nav() {
    $items   = wpnfinite_video_secondary_items();
    $current = sanitize_key( get_query_var( 'nfinite_video_filter' ) );
    if ( ! $items ) { return; }

    echo '<ul class="wpnfinite-topics-menu wpnfinite-video-topics-menu">';
    foreach ( $items as $item ) {
        $classes = array( 'menu-item', 'wpnfinite-video-menu-item' );
        if ( $current === $item['key'] ) {
            $classes[] = 'current-menu-item';
        }
        echo '<li class="' . esc_attr( implode( ' ', $classes ) ) . '"><a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a></li>';
    }
    echo '</ul>';
}

/**
 * Render the contextual secondary bar without changing the stored WordPress
 * menu. Videos and Music get dedicated destinations; other pages keep Explore.
 */
function wpnfinite_render_secondary_navigation() {
    if ( function_exists( 'wpnfinite_is_platform_secondary_context' ) && wpnfinite_is_platform_secondary_context() ) {
        wpnfinite_render_platform_secondary_nav();
        return;
    }

    if ( wpnfinite_is_video_secondary_context() ) {
        wpnfinite_render_video_secondary_nav();
        return;
    }

    if ( wpnfinite_is_music_secondary_context() ) {
        wpnfinite_render_music_secondary_nav();
        return;
    }

    wp_nav_menu(
        array(
            'theme_location' => 'topics',
            'container'      => false,
            'menu_class'     => 'wpnfinite-topics-menu',
            'fallback_cb'    => 'wpnfinite_topics_menu_fallback',
            'depth'          => 1,
        )
    );
}

function wpnfinite_secondary_navigation_label() {
    if ( function_exists( 'wpnfinite_is_platform_secondary_context' ) && wpnfinite_is_platform_secondary_context() ) {
        return __( 'Explore', 'wpnfinite' );
    }

    if ( wpnfinite_is_video_secondary_context() ) {
        return __( 'Videos', 'wpnfinite' );
    }

    return wpnfinite_is_music_secondary_context() ? __( 'Music', 'wpnfinite' ) : __( 'Explore', 'wpnfinite' );
}



/** Shared PairOfDice ecosystem navigation for every first-party surface. */
function wpnfinite_pairofdice_ecosystem_items() {
    return apply_filters( 'wpnfinite_pairofdice_ecosystem_items', array(
        'media' => array( 'label' => __( 'Media', 'wpnfinite' ), 'url' => 'https://pairofdice.media/' ),
        'music' => array( 'label' => __( 'Music', 'wpnfinite' ), 'url' => add_query_arg( 'pairofdice_player', '1', home_url( '/' ) ) . '#music' ),
        'tv'    => array( 'label' => __( 'TV', 'wpnfinite' ), 'url' => 'https://tv.pairofdice.media/' ),
        'beats' => array( 'label' => __( 'Beats', 'wpnfinite' ), 'url' => 'https://beats.pairofdice.media/' ),
        'intelligence' => array( 'label' => __( 'Creator Intelligence', 'wpnfinite' ), 'url' => 'https://ci.pairofdice.media/' ),
    ) );
}

function wpnfinite_pairofdice_ecosystem_active() {
    return 'media';
}

function wpnfinite_render_pairofdice_ecosystem_nav() {
    $active = wpnfinite_pairofdice_ecosystem_active();
    echo '<nav class="wpnfinite-ecosystem-nav" aria-label="' . esc_attr__( 'PairOfDice ecosystem', 'wpnfinite' ) . '"><div class="wpnfinite-container wpnfinite-ecosystem-nav__inner">';
    foreach ( wpnfinite_pairofdice_ecosystem_items() as $key => $item ) {
        $class = 'wpnfinite-ecosystem-nav__link' . ( $active === $key ? ' is-active' : '' );
        $current = $active === $key ? ' aria-current="page"' : '';
        echo '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $item['url'] ) . '"' . $current . '>' . esc_html( $item['label'] ) . '</a>';
    }
    echo '</div></nav>';
}

/** PairOfDice 1.5 company navigation: product-first, with Discover opening the media apps and browse destinations. */
function wpnfinite_render_strategic_primary_nav() {
    $creator_url = post_type_exists( 'nfinite_creator' ) ? get_post_type_archive_link( 'nfinite_creator' ) : home_url( '/creators/' );
    $creator_url = $creator_url ?: home_url( '/creators/' );
    $stories_url = function_exists( 'wpnfinite_media_archive_link' ) ? wpnfinite_media_archive_link( 'post' ) : home_url( '/stories/' );
    $player_url  = add_query_arg( 'pairofdice_player', '1', home_url( '/' ) );

    $discover_items = array(
        array( 'label' => __( 'Music', 'wpnfinite' ), 'url' => $player_url . '#music' ),
        array( 'label' => __( 'Radio', 'wpnfinite' ), 'url' => $player_url . '#radio' ),
        array( 'label' => __( 'Beats', 'wpnfinite' ), 'url' => 'https://beats.pairofdice.media/' ),
        array( 'label' => __( 'TV', 'wpnfinite' ), 'url' => 'https://tv.pairofdice.media/' ),
        array( 'label' => __( 'Creators', 'wpnfinite' ), 'url' => $creator_url ),
        array( 'label' => __( 'Stories', 'wpnfinite' ), 'url' => $stories_url ),
    );

    $items = array(
        array( 'label' => __( 'Creators', 'wpnfinite' ), 'url' => $creator_url ),
        array( 'label' => __( 'Creator Intelligence', 'wpnfinite' ), 'url' => home_url( '/creator-intelligence/' ) ),
        array( 'label' => __( 'Tools', 'wpnfinite' ), 'url' => home_url( '/#creator-tools' ) ),
        array( 'label' => __( 'Community', 'wpnfinite' ), 'url' => post_type_exists( 'nfinite_creator_post' ) ? get_post_type_archive_link( 'nfinite_creator_post' ) : home_url( '/community/' ) ),
        array( 'label' => __( 'Network', 'wpnfinite' ), 'url' => home_url( '/#pairofdice-network' ) ),
    );

    echo '<ul class="wpnfinite-menu wpnfinite-strategic-menu">';
    echo '<li class="menu-item menu-item-has-children wpnfinite-discover-menu">';
    echo '<button type="button" class="wpnfinite-nav-parent" aria-expanded="false">' . esc_html__( 'Discover', 'wpnfinite' ) . '<span class="wpnfinite-nav-chevron" aria-hidden="true">⌄</span></button>';
    echo '<ul class="sub-menu" aria-label="' . esc_attr__( 'Discover', 'wpnfinite' ) . '">';
    foreach ( $discover_items as $item ) {
        echo '<li><a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a></li>';
    }
    echo '</ul></li>';
    foreach ( $items as $item ) {
        echo '<li><a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a></li>';
    }
    echo '</ul>';
}
