<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Content types WPNfinite can compose into the automatic media homepage.
 */
function wpnfinite_media_post_types() {
    $types = array( 'post' );

    foreach ( array( 'blog', 'collection' ) as $type ) {
        if ( post_type_exists( $type ) ) {
            $types[] = $type;
        }
    }

    return array_values( array_unique( apply_filters( 'wpnfinite_media_post_types', $types ) ) );
}

/**
 * Public-facing labels. ShopBlocks' live "shoppable" content type is Collections.
 */
function wpnfinite_media_type_label( $post_id ) {
    $type = get_post_type( $post_id );

    $labels = array(
        'post'       => __( 'Story', 'wpnfinite' ),
        'blog'       => __( 'Blog', 'wpnfinite' ),
        'collection' => __( 'Collection', 'wpnfinite' ),
    );

    return apply_filters(
        'wpnfinite_media_type_label',
        isset( $labels[ $type ] ) ? $labels[ $type ] : ucfirst( (string) $type ),
        $post_id,
        $type
    );
}

function wpnfinite_media_query( $args = array() ) {
    $defaults = array(
        'post_type'           => wpnfinite_media_post_types(),
        'post_status'         => 'publish',
        'posts_per_page'      => 8,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => false,
        'no_found_rows'       => true,
    );

    return new WP_Query( wp_parse_args( $args, $defaults ) );
}

function wpnfinite_media_get_excerpt( $post_id, $words = 24 ) {
    $excerpt = get_the_excerpt( $post_id );

    if ( ! $excerpt ) {
        $excerpt = wp_strip_all_tags( get_post_field( 'post_content', $post_id ) );
    }

    return wp_trim_words( $excerpt, $words, '…' );
}

function wpnfinite_media_archive_link( $post_type ) {
    if ( 'post' === $post_type ) {
        $page_for_posts = (int) get_option( 'page_for_posts' );
        return $page_for_posts ? get_permalink( $page_for_posts ) : home_url( '/blog/' );
    }

    $link = get_post_type_archive_link( $post_type );
    return $link ? $link : home_url( '/' );
}

function wpnfinite_media_section_query( $post_type, $limit = 6, $exclude = array() ) {
    if ( ! post_type_exists( $post_type ) && 'post' !== $post_type ) {
        return null;
    }

    return wpnfinite_media_query(
        array(
            'post_type'      => $post_type,
            'posts_per_page' => absint( $limit ),
            'post__not_in'   => array_map( 'absint', $exclude ),
        )
    );
}

function wpnfinite_media_add_used_ids( &$used_ids, $posts ) {
    if ( empty( $posts ) ) {
        return;
    }

    foreach ( $posts as $post ) {
        if ( is_object( $post ) && isset( $post->ID ) ) {
            $used_ids[] = (int) $post->ID;
        }
    }

    $used_ids = array_values( array_unique( array_map( 'absint', $used_ids ) ) );
}

/**
 * Return published IDs for one content type, newest first.
 */
function wpnfinite_media_inventory_ids( $post_type, $limit = 12 ) {
    if ( ! post_type_exists( $post_type ) && 'post' !== $post_type ) {
        return array();
    }

    $query = new WP_Query(
        array(
            'post_type'           => $post_type,
            'post_status'         => 'publish',
            'posts_per_page'      => max( 1, absint( $limit ) ),
            'orderby'             => 'date',
            'order'               => 'DESC',
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
            'fields'              => 'ids',
        )
    );

    return array_map( 'absint', $query->posts );
}

/**
 * Reserve enough specialist content for dedicated homepage modules before
 * broad mixed-content queries are allowed to consume it.
 */
function wpnfinite_media_reservations() {
    $reservations = array(
        'blog'       => array(),
        'collection' => array(),
    );

    if ( post_type_exists( 'blog' ) ) {
        $blog_ids = wpnfinite_media_inventory_ids( 'blog', 8 );
        if ( count( $blog_ids ) >= 2 ) {
            $reservations['blog'] = array_slice( $blog_ids, 0, min( 4, count( $blog_ids ) ) );
        }
    }

    if ( post_type_exists( 'collection' ) ) {
        $collection_ids = wpnfinite_media_inventory_ids( 'collection', 8 );
        if ( ! empty( $collection_ids ) ) {
            $reservations['collection'] = array_slice( $collection_ids, 0, min( 4, count( $collection_ids ) ) );
        }
    }

    return apply_filters( 'wpnfinite_media_reservations', $reservations );
}

function wpnfinite_media_reserved_ids( $reservations ) {
    $ids = array();

    foreach ( $reservations as $group ) {
        $ids = array_merge( $ids, array_map( 'absint', (array) $group ) );
    }

    return array_values( array_unique( $ids ) );
}

/**
 * Fetch the actual posts for a reservation while honoring anything already used.
 */
function wpnfinite_media_reserved_query( $post_type, $reserved_ids, $used_ids = array() ) {
    $ids = array_values( array_diff( array_map( 'absint', $reserved_ids ), array_map( 'absint', $used_ids ) ) );

    if ( empty( $ids ) ) {
        return null;
    }

    return wpnfinite_media_query(
        array(
            'post_type'      => $post_type,
            'post__in'       => $ids,
            'posts_per_page' => count( $ids ),
            'orderby'        => 'post__in',
        )
    );
}

/**
 * Responsive/adaptive grid class for sparse and full sections.
 */
function wpnfinite_media_grid_count_class( $count ) {
    $count = max( 1, min( 4, absint( $count ) ) );
    return 'wpnfinite-media-grid--count-' . $count;
}

/**
 * Finds populated native categories. Terms are ranked by content volume.
 */
function wpnfinite_media_category_terms( $limit = 3, $exclude_term_ids = array() ) {
    if ( ! taxonomy_exists( 'category' ) ) {
        return array();
    }

    $terms = get_terms(
        array(
            'taxonomy'   => 'category',
            'hide_empty' => true,
            'number'     => max( 8, absint( $limit ) * 3 ),
            'orderby'    => 'count',
            'order'      => 'DESC',
            'exclude'    => array_map( 'absint', $exclude_term_ids ),
        )
    );

    return is_wp_error( $terms ) ? array() : array_slice( $terms, 0, absint( $limit ) );
}

function wpnfinite_media_category_query( $term_id, $limit = 4, $exclude = array() ) {
    return wpnfinite_media_query(
        array(
            'post_type'      => array_values( array_intersect( wpnfinite_media_post_types(), array( 'post', 'blog' ) ) ),
            'posts_per_page' => absint( $limit ),
            'post__not_in'   => array_map( 'absint', $exclude ),
            'tax_query'      => array(
                array(
                    'taxonomy' => 'category',
                    'field'    => 'term_id',
                    'terms'    => absint( $term_id ),
                ),
            ),
        )
    );
}


/**
 * Creator Spotlight inventory for Nfinite Creators.
 */
function wpnfinite_media_creator_query( $limit = 4 ) {
    if ( ! post_type_exists( 'nfinite_creator' ) ) {
        return null;
    }

    return new WP_Query(
        array(
            'post_type'           => 'nfinite_creator',
            'post_status'         => 'publish',
            'posts_per_page'      => max( 1, absint( $limit ) ),
            'orderby'             => 'date',
            'order'               => 'DESC',
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
        )
    );
}

function wpnfinite_media_creator_types( $creator_id ) {
    $terms = get_the_terms( $creator_id, 'nfinite_creator_type' );

    if ( ! $terms || is_wp_error( $terms ) ) {
        return __( 'Creator', 'wpnfinite' );
    }

    return implode( ' • ', wp_list_pluck( $terms, 'name' ) );
}

function wpnfinite_media_home_setting( $key, $default = null ) {
    return get_theme_mod( 'wpnfinite_media_' . $key, $default );
}

/**
 * Newsletter/CTA integration point.
 *
 * Plugins can return a form, shortcode output, or another action UI:
 * add_filter( 'wpnfinite_media_newsletter_markup', ... );
 */
function wpnfinite_media_newsletter_markup() {
    return apply_filters( 'wpnfinite_media_newsletter_markup', '' );
}

function wpnfinite_media_home_customize_register( $wp_customize ) {
    $wp_customize->add_section(
        'wpnfinite_media_home',
        array(
            'title'       => __( 'WPNfinite Media Homepage', 'wpnfinite' ),
            'priority'    => 35,
            'description' => __( 'Optional controls for the automatic media homepage. The defaults work without setup.', 'wpnfinite' ),
        )
    );

    $settings = array(
        'show_intro'       => array( 'label' => __( 'Show publication title and tagline', 'wpnfinite' ), 'default' => true, 'type' => 'checkbox' ),
        'show_categories'  => array( 'label' => __( 'Show automatic category rails', 'wpnfinite' ), 'default' => true, 'type' => 'checkbox' ),
        'show_blogs'       => array( 'label' => __( 'Show dedicated Blog section when available', 'wpnfinite' ), 'default' => true, 'type' => 'checkbox' ),
        'show_collections' => array( 'label' => __( 'Show Collections section when available', 'wpnfinite' ), 'default' => true, 'type' => 'checkbox' ),
        'show_newsletter'  => array( 'label' => __( 'Show homepage newsletter / CTA band', 'wpnfinite' ), 'default' => true, 'type' => 'checkbox' ),
        'show_creators'    => array( 'label' => __( 'Show Creator Spotlight when available', 'wpnfinite' ), 'default' => true, 'type' => 'checkbox' ),
    );

    foreach ( $settings as $key => $config ) {
        $wp_customize->add_setting(
            'wpnfinite_media_' . $key,
            array(
                'default'           => $config['default'],
                'sanitize_callback' => 'wp_validate_boolean',
            )
        );

        $wp_customize->add_control(
            'wpnfinite_media_' . $key,
            array(
                'section' => 'wpnfinite_media_home',
                'label'   => $config['label'],
                'type'    => $config['type'],
            )
        );
    }

    $wp_customize->add_setting(
        'wpnfinite_media_newsletter_title',
        array(
            'default'           => __( 'Stay in the loop', 'wpnfinite' ),
            'sanitize_callback' => 'sanitize_text_field',
        )
    );
    $wp_customize->add_control(
        'wpnfinite_media_newsletter_title',
        array(
            'section' => 'wpnfinite_media_home',
            'label'   => __( 'CTA heading', 'wpnfinite' ),
            'type'    => 'text',
        )
    );

    $wp_customize->add_setting(
        'wpnfinite_media_newsletter_text',
        array(
            'default'           => __( 'Get the latest stories, blogs, and Collections from our newsroom.', 'wpnfinite' ),
            'sanitize_callback' => 'sanitize_text_field',
        )
    );
    $wp_customize->add_control(
        'wpnfinite_media_newsletter_text',
        array(
            'section' => 'wpnfinite_media_home',
            'label'   => __( 'CTA text', 'wpnfinite' ),
            'type'    => 'text',
        )
    );

    $wp_customize->add_setting(
        'wpnfinite_media_newsletter_label',
        array(
            'default'           => __( 'Explore more', 'wpnfinite' ),
            'sanitize_callback' => 'sanitize_text_field',
        )
    );
    $wp_customize->add_control(
        'wpnfinite_media_newsletter_label',
        array(
            'section' => 'wpnfinite_media_home',
            'label'   => __( 'CTA button label', 'wpnfinite' ),
            'type'    => 'text',
        )
    );

    $wp_customize->add_setting(
        'wpnfinite_media_newsletter_url',
        array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        )
    );
    $wp_customize->add_control(
        'wpnfinite_media_newsletter_url',
        array(
            'section' => 'wpnfinite_media_home',
            'label'   => __( 'CTA URL', 'wpnfinite' ),
            'type'    => 'url',
        )
    );
}
add_action( 'customize_register', 'wpnfinite_media_home_customize_register' );
