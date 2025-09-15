<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Simple breadcrumbs (WooCommerce-aware).
 */
function wpnfinite_breadcrumbs() {
    if ( function_exists( 'woocommerce_breadcrumb' ) && ( is_shop() || is_product() || is_product_taxonomy() ) ) {
        ob_start();
        woocommerce_breadcrumb( [ 'delimiter' => ' › ' ] );
        return '<nav class="wpn-bc" aria-label="Breadcrumbs">' . ob_get_clean() . '</nav>';
    }

    $parts = [];
    $parts[] = '<a href="' . esc_url( home_url('/') ) . '">Home</a>';

    if ( is_singular() ) {
        $ancestors = array_reverse( get_post_ancestors( get_queried_object_id() ) );
        foreach ( $ancestors as $pid ) {
            $parts[] = '<a href="' . esc_url( get_permalink( $pid ) ) . '">' . esc_html( get_the_title( $pid ) ) . '</a>';
        }
        $parts[] = '<span class="current">' . esc_html( get_the_title() ) . '</span>';
    } elseif ( is_category() || is_tag() || is_tax() ) {
        $term = get_queried_object();
        if ( $term && ! is_wp_error( $term ) ) {
            // Output ancestors if any
            $ancs = get_ancestors( $term->term_id, $term->taxonomy );
            $ancs = array_reverse( $ancs );
            foreach ( $ancs as $tid ) {
                $t = get_term( $tid, $term->taxonomy );
                if ( $t && ! is_wp_error( $t ) ) {
                    $parts[] = '<a href="' . esc_url( get_term_link( $t ) ) . '">' . esc_html( $t->name ) . '</a>';
                }
            }
            $parts[] = '<span class="current">' . esc_html( $term->name ) . '</span>';
        }
    } elseif ( is_search() ) {
        $parts[] = '<span class="current">Search</span>';
    } elseif ( is_home() ) {
        $parts[] = '<span class="current">Blog</span>';
    } elseif ( is_archive() ) {
        $parts[] = '<span class="current">' . esc_html( get_the_archive_title() ) . '</span>';
    } elseif ( is_404() ) {
        $parts[] = '<span class="current">Not found</span>';
    }

    return '<nav class="wpn-bc" aria-label="Breadcrumbs">' . implode( ' <span class="sep">›</span> ', $parts ) . '</nav>';
}

/**
 * Resolve title/subtitle/background by context.
 */
function wpnfinite_page_header_context() {
    $ctx = [
        'title'    => '',
        'subtitle' => '',
        'bg'       => '', // url
    ];

    if ( is_front_page() && is_home() ) {
        $ctx['title'] = get_bloginfo( 'name' );
        $ctx['subtitle'] = get_bloginfo( 'description' );
    } elseif ( is_home() ) {
        $posts_page = get_option( 'page_for_posts' );
        $ctx['title'] = $posts_page ? get_the_title( $posts_page ) : __( 'Latest Posts', 'wpnfinite' );
        $ctx['subtitle'] = $posts_page ? get_post_field( 'post_excerpt', $posts_page ) : '';
        if ( $posts_page ) {
            $ctx['bg'] = get_the_post_thumbnail_url( $posts_page, 'full' );
        }
    } elseif ( is_singular() ) {
        $id = get_queried_object_id();
        $ctx['title'] = get_the_title( $id );
        $ctx['subtitle'] = get_post_field( 'post_excerpt', $id ) ?: '';
        $ctx['bg'] = get_the_post_thumbnail_url( $id, 'full' );

        // Optional per-page overrides via custom fields.
        $meta_title = get_post_meta( $id, 'header_title', true );
        $meta_sub   = get_post_meta( $id, 'header_subtitle', true );
        $meta_img   = get_post_meta( $id, 'header_image', true );
        if ( $meta_title ) $ctx['title'] = $meta_title;
        if ( $meta_sub )   $ctx['subtitle'] = $meta_sub;
        if ( $meta_img )   $ctx['bg'] = esc_url_raw( $meta_img );
    } elseif ( is_search() ) {
        global $wp_query;
        $ctx['title'] = sprintf( __( 'Search results for “%s”', 'wpnfinite' ), get_search_query() );
        $ctx['subtitle'] = sprintf( _n( '%s result', '%s results', (int) $wp_query->found_posts, 'wpnfinite' ), number_format_i18n( (int) $wp_query->found_posts ) );
    } elseif ( is_404() ) {
        $ctx['title'] = __( 'Page not found', 'wpnfinite' );
        $ctx['subtitle'] = __( 'Try searching or go back to the homepage.', 'wpnfinite' );
    } else { // Archives
        $ctx['title'] = get_the_archive_title();
        $ctx['subtitle'] = get_the_archive_description();

        // Woo product category thumbnail
        if ( function_exists('is_product_taxonomy') && is_product_taxonomy() ) {
            $term = get_queried_object();
            $thumb_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
            if ( $thumb_id ) $ctx['bg'] = wp_get_attachment_image_url( $thumb_id, 'full' );
        }
    }

    return $ctx;
}

function wpnfinite_render_page_header( $attributes ) {
    $atts = wp_parse_args( $attributes, [
        'height'          => '40vh',
        'showBreadcrumbs' => true,
        'overlay'         => 'gradient', // none|gradient|dim
        'textAlign'       => 'left',
        'kicker'          => '',
    ] );

    $ctx = wpnfinite_page_header_context();

    $classes = [
        'wpn-hero',
        'align' . ( isset( $attributes['align'] ) ? $attributes['align'] : 'wide' ),
        'overlay-' . $atts['overlay'],
        'text-' . $atts['textAlign'],
    ];

    $style = 'min-height:' . esc_attr( $atts['height'] ) . ';';
    if ( $ctx['bg'] ) {
        $style .= 'background-image:url(' . esc_url( $ctx['bg'] ) . ');';
    }

    ob_start(); ?>
    <header class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" style="<?php echo esc_attr( $style ); ?>" role="banner" aria-label="Page header">
        <div class="wpn-hero__inner">
            <?php if ( ! empty( $atts['kicker'] ) ) : ?>
                <p class="wpn-hero__kicker"><?php echo esc_html( $atts['kicker'] ); ?></p>
            <?php endif; ?>

            <h1 class="wpn-hero__title"><?php echo esc_html( wp_strip_all_tags( $ctx['title'] ) ); ?></h1>

            <?php if ( ! empty( $ctx['subtitle'] ) ) : ?>
                <p class="wpn-hero__subtitle"><?php echo wp_kses_post( wpautop( $ctx['subtitle'] ) ); ?></p>
            <?php endif; ?>

            <?php if ( $atts['showBreadcrumbs'] ) : ?>
                <?php echo wpnfinite_breadcrumbs(); ?>
            <?php endif; ?>
        </div>
    </header>
    <?php
    return ob_get_clean();
}

echo wpnfinite_render_page_header( $attributes );
