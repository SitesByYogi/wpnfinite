<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * WPNfinite 1.4 platform bridge for Nfinite Creators 0.56+.
 *
 * Nfinite owns creator/media/business logic. WPNfinite owns presentation,
 * discoverability and fan-facing navigation around that logic.
 */

function wpnfinite_creators_platform_active() {
    return function_exists( 'wpnfinite_has_nfinite_creators' ) && wpnfinite_has_nfinite_creators();
}

function wpnfinite_platform_pages() {
    return array(
        'discover' => array(
            'title'     => __( 'Discover', 'wpnfinite' ),
            'shortcode' => '[nfinite_discovery_hub]',
            'requires'  => 'nfinite_discovery_hub',
        ),
        'find' => array(
            'title'     => __( 'Search', 'wpnfinite' ),
            'shortcode' => '[nfinite_search]',
            'requires'  => 'nfinite_search',
        ),
        'my-library' => array(
            'title'     => __( 'My Library', 'wpnfinite' ),
            'shortcode' => '[nfinite_library]',
            'requires'  => 'nfinite_library',
        ),
        'playlists' => array(
            'title'     => __( 'Playlists', 'wpnfinite' ),
            'shortcode' => "[nfinite_playlist_builder]\n\n[nfinite_playlists limit=24]",
            'requires'  => 'nfinite_playlists',
        ),
        'opportunities' => array(
            'title'     => __( 'Opportunities', 'wpnfinite' ),
            'shortcode' => '[nfinite_opportunities]',
            'requires'  => 'nfinite_opportunities',
        ),
        'organizations' => array(
            'title'     => __( 'Organizations', 'wpnfinite' ),
            'shortcode' => '[nfinite_organizations]',
            'requires'  => 'nfinite_organizations',
        ),
    );
}

/**
 * Create the fan-facing utility pages once after installing this theme update.
 * Existing pages are always preserved and never overwritten.
 */
function wpnfinite_maybe_create_platform_pages() {
    if ( ! is_admin() || ! current_user_can( 'manage_options' ) || ! wpnfinite_creators_platform_active() ) {
        return;
    }

    $marker = '1.4.0';
    if ( $marker === (string) get_option( 'wpnfinite_platform_pages_version', '' ) ) {
        return;
    }

    foreach ( wpnfinite_platform_pages() as $slug => $config ) {
        if ( ! shortcode_exists( $config['requires'] ) || get_page_by_path( $slug ) ) {
            continue;
        }

        wp_insert_post(
            array(
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'post_title'   => $config['title'],
                'post_name'    => $slug,
                'post_content' => $config['shortcode'],
            )
        );
    }

    update_option( 'wpnfinite_platform_pages_version', $marker, false );
}
add_action( 'admin_init', 'wpnfinite_maybe_create_platform_pages', 35 );

function wpnfinite_platform_page_url( $slug ) {
    $page = get_page_by_path( sanitize_title( $slug ) );
    if ( $page && 'trash' !== $page->post_status ) {
        return get_permalink( $page );
    }

    return home_url( '/' . trim( $slug, '/' ) . '/' );
}

function wpnfinite_platform_search_url() {
    if ( shortcode_exists( 'nfinite_search' ) ) {
        return wpnfinite_platform_page_url( 'find' );
    }
    return home_url( '/?s=' );
}

function wpnfinite_platform_library_url() {
    return wpnfinite_platform_page_url( 'my-library' );
}

function wpnfinite_platform_notifications_url() {
    if ( class_exists( 'Nfinite_Creators_Notifications' ) && is_callable( array( 'Nfinite_Creators_Notifications', 'page_url' ) ) ) {
        return Nfinite_Creators_Notifications::page_url();
    }
    return home_url( '/notifications/' );
}

function wpnfinite_is_platform_secondary_context() {
    if ( ! wpnfinite_creators_platform_active() ) {
        return false;
    }

    if ( is_page( array( 'discover', 'find', 'my-library', 'playlists', 'notifications', 'opportunities', 'organizations' ) ) ) {
        return true;
    }

    if ( post_type_exists( 'nfinite_playlist' ) && ( is_singular( 'nfinite_playlist' ) || is_post_type_archive( 'nfinite_playlist' ) ) ) {
        return true;
    }

    if ( post_type_exists( 'nfinite_organization' ) && ( is_singular( 'nfinite_organization' ) || is_post_type_archive( 'nfinite_organization' ) ) ) {
        return true;
    }

    return false;
}

function wpnfinite_platform_secondary_items() {
    $items = array(
        array( 'key' => 'discover',      'label' => __( 'Discover', 'wpnfinite' ),      'url' => wpnfinite_platform_page_url( 'discover' ) ),
        array( 'key' => 'find',          'label' => __( 'Search', 'wpnfinite' ),        'url' => wpnfinite_platform_search_url() ),
        array( 'key' => 'playlists',     'label' => __( 'Playlists', 'wpnfinite' ),     'url' => wpnfinite_platform_page_url( 'playlists' ) ),
        array( 'key' => 'organizations', 'label' => __( 'Organizations', 'wpnfinite' ), 'url' => wpnfinite_platform_page_url( 'organizations' ) ),
        array( 'key' => 'opportunities', 'label' => __( 'Opportunities', 'wpnfinite' ), 'url' => wpnfinite_platform_page_url( 'opportunities' ) ),
    );

    if ( is_user_logged_in() ) {
        $items[] = array( 'key' => 'my-library', 'label' => __( 'My Library', 'wpnfinite' ), 'url' => wpnfinite_platform_library_url() );
        $items[] = array( 'key' => 'notifications', 'label' => __( 'Notifications', 'wpnfinite' ), 'url' => wpnfinite_platform_notifications_url() );
    }

    return $items;
}

function wpnfinite_render_platform_secondary_nav() {
    $current_slug = is_page() ? get_post_field( 'post_name', get_queried_object_id() ) : '';
    echo '<ul class="wpnfinite-topics-menu wpnfinite-platform-topics-menu">';
    foreach ( wpnfinite_platform_secondary_items() as $item ) {
        $classes = array( 'menu-item', 'wpnfinite-platform-menu-item' );
        if ( $current_slug === $item['key'] ) {
            $classes[] = 'current-menu-item';
        }
        echo '<li class="' . esc_attr( implode( ' ', $classes ) ) . '"><a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a></li>';
    }
    echo '</ul>';
}

/** Header fan controls. */
function wpnfinite_render_fan_header_actions() {
    if ( ! wpnfinite_creators_platform_active() || ! is_user_logged_in() ) {
        return;
    }

    echo '<a class="wpnfinite-fan-action wpnfinite-library-link" href="' . esc_url( wpnfinite_platform_library_url() ) . '" aria-label="' . esc_attr__( 'My Library', 'wpnfinite' ) . '"><span aria-hidden="true">♡</span><span class="screen-reader-text">' . esc_html__( 'My Library', 'wpnfinite' ) . '</span></a>';

    if ( shortcode_exists( 'nfinite_notification_bell' ) ) {
        echo '<span class="wpnfinite-header-notifications">' . do_shortcode( '[nfinite_notification_bell]' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    } else {
        echo '<a class="wpnfinite-fan-action" href="' . esc_url( wpnfinite_platform_notifications_url() ) . '" aria-label="' . esc_attr__( 'Notifications', 'wpnfinite' ) . '"><span aria-hidden="true">●</span></a>';
    }
}

/**
 * A compact homepage layer for the newer Nfinite systems.
 * Keeps theme presentation separate from Nfinite ranking/business logic.
 */
function wpnfinite_render_platform_home_sections() {
    if ( ! wpnfinite_creators_platform_active() ) {
        return;
    }

    if ( shortcode_exists( 'nfinite_placement' ) ) {
        $sponsor = do_shortcode( '[nfinite_placement key="home_featured"]' );
        if ( trim( $sponsor ) ) {
            echo '<section class="wpnfinite-container wpnfinite-platform-sponsor">' . $sponsor . '</section>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    }

    if ( shortcode_exists( 'nfinite_discovery' ) ) {
        $settings = function_exists( 'wpnfinite_home_control_settings' ) ? wpnfinite_home_control_settings() : array( 'discovery_min'=>4, 'fallback_feed'=>'trending', 'curated_ids'=>array(), 'discovery_heading'=>'Discover Something New', 'discovery_description'=>'Creators, music, stories, video and opportunities worth discovering now.', 'discovery_link_label'=>'Explore Discovery' );
        $personalized = function_exists( 'wpnfinite_discovery_has_personalization' ) && wpnfinite_discovery_has_personalization( absint( $settings['discovery_min'] ) );
        if ( $personalized ) {
            $feed_html = do_shortcode( '[nfinite_discovery feed="recommended" limit="8" title="For You"]' );
            $title = $settings['discovery_heading'];
        } elseif ( ! empty( $settings['curated_ids'] ) && function_exists( 'wpnfinite_render_curated_discovery' ) ) {
            $feed_html = wpnfinite_render_curated_discovery( $settings['curated_ids'] );
            $title = $settings['discovery_heading'];
        } else {
            $fallback = in_array( $settings['fallback_feed'], array( 'trending','popular','new' ), true ) ? $settings['fallback_feed'] : 'trending';
            $feed_html = do_shortcode( '[nfinite_discovery feed="' . esc_attr( $fallback ) . '" limit="8" title="Discover Now"]' );
            $title = $settings['discovery_heading'];
        }
        echo '<section class="wpnfinite-container wpnfinite-platform-discovery wpnfinite-home-discovery"><div class="wpnfinite-media-section__head"><div><span>' . esc_html__( 'PairOfDice Discovery', 'wpnfinite' ) . '</span><h2>' . esc_html( $title ) . '</h2><p>' . esc_html( $settings['discovery_description'] ) . '</p></div><a href="' . esc_url( wpnfinite_platform_page_url( 'discover' ) ) . '">' . esc_html( $settings['discovery_link_label'] ) . ' →</a></div><div class="wpnfinite-home-discovery__feed">' . $feed_html . '</div></section>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}

/** Add useful page classes for styling and future WPNfinite integrations. */
function wpnfinite_platform_body_classes( $classes ) {
    if ( wpnfinite_is_platform_secondary_context() ) {
        $classes[] = 'wpnfinite-platform-context';
    }
    if ( is_user_logged_in() && wpnfinite_creators_platform_active() ) {
        $classes[] = 'wpnfinite-fan-session';
    }
    return $classes;
}
add_filter( 'body_class', 'wpnfinite_platform_body_classes', 25 );
