<?php
/**
 * WPNfinite editorial presentation helpers.
 *
 * Keeps article/archive presentation in the theme while allowing Nfinite to
 * remain the owner of creator identity and creator-authored content data.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Estimated reading time for a post.
 */
function wpnfinite_reading_time( $post_id = 0 ) {
    $post_id = $post_id ? absint( $post_id ) : get_the_ID();
    $content = (string) get_post_field( 'post_content', $post_id );
    $words   = str_word_count( wp_strip_all_tags( strip_shortcodes( $content ) ) );
    $minutes = max( 1, (int) ceil( $words / 225 ) );

    return sprintf(
        /* translators: %d: estimated reading time in minutes. */
        _n( '%d min read', '%d min read', $minutes, 'wpnfinite' ),
        $minutes
    );
}

/**
 * Primary category with a safe core fallback when the media helper is absent.
 */
function wpnfinite_editorial_primary_category( $post_id = 0 ) {
    $post_id = $post_id ? absint( $post_id ) : get_the_ID();

    if ( function_exists( 'wpnfinite_media_primary_category' ) ) {
        $term = wpnfinite_media_primary_category( $post_id );
        if ( $term instanceof WP_Term ) {
            return $term;
        }
    }

    $categories = get_the_category( $post_id );
    return ! empty( $categories ) && ! is_wp_error( $categories ) ? reset( $categories ) : null;
}

/**
 * Render the primary category as a reusable editorial eyebrow.
 */
function wpnfinite_editorial_category_link( $post_id = 0 ) {
    $term = wpnfinite_editorial_primary_category( $post_id );
    if ( ! $term instanceof WP_Term ) {
        return;
    }

    $url = get_term_link( $term );
    if ( is_wp_error( $url ) ) {
        return;
    }

    printf(
        '<a class="wpnfinite-story-category" href="%1$s">%2$s</a>',
        esc_url( $url ),
        esc_html( $term->name )
    );
}

/**
 * Find the Nfinite creator profile owned by a WordPress author.
 */
function wpnfinite_creator_for_user( $user_id ) {
    $user_id = absint( $user_id );
    if ( ! $user_id || ! post_type_exists( 'nfinite_creator' ) ) {
        return null;
    }

    $creators = get_posts(
        array(
            'post_type'      => 'nfinite_creator',
            'post_status'    => 'publish',
            'author'         => $user_id,
            'posts_per_page' => 1,
            'no_found_rows'  => true,
        )
    );

    return ! empty( $creators ) ? $creators[0] : null;
}

/**
 * Editorial author URL. Prefer a linked Nfinite creator profile when present.
 */
function wpnfinite_editorial_author_url( $post_id = 0 ) {
    $post_id = $post_id ? absint( $post_id ) : get_the_ID();
    $user_id = (int) get_post_field( 'post_author', $post_id );
    $creator = wpnfinite_creator_for_user( $user_id );

    if ( $creator instanceof WP_Post ) {
        return get_permalink( $creator );
    }

    return get_author_posts_url( $user_id );
}

/**
 * Compact byline used on cards and stories.
 */
function wpnfinite_editorial_byline( $post_id = 0, $with_avatar = false ) {
    $post_id = $post_id ? absint( $post_id ) : get_the_ID();
    $user_id = (int) get_post_field( 'post_author', $post_id );
    $name    = get_the_author_meta( 'display_name', $user_id );
    $url     = wpnfinite_editorial_author_url( $post_id );

    echo '<div class="wpnfinite-story-byline">';
    if ( $with_avatar ) {
        echo '<span class="wpnfinite-story-avatar">' . get_avatar( $user_id, 48 ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
    printf( '<span>%1$s <a href="%2$s">%3$s</a></span>', esc_html__( 'By', 'wpnfinite' ), esc_url( $url ), esc_html( $name ) );
    echo '</div>';
}

/**
 * Reusable archive heading.
 */
function wpnfinite_editorial_archive_header() {
    $title       = '';
    $description = '';
    $eyebrow     = __( 'PairOfDice Editorial', 'wpnfinite' );

    if ( is_search() ) {
        $title = sprintf( __( 'Search results for “%s”', 'wpnfinite' ), get_search_query() );
        $description = __( 'Stories, culture, creators and resources matching your search.', 'wpnfinite' );
        $eyebrow = __( 'Search', 'wpnfinite' );
    } elseif ( is_home() ) {
        $posts_page = (int) get_option( 'page_for_posts' );
        $title = $posts_page ? get_the_title( $posts_page ) : __( 'Stories', 'wpnfinite' );
        $description = $posts_page ? get_post_field( 'post_excerpt', $posts_page ) : '';
        if ( ! $description ) {
            $description = __( 'Culture, music, creator stories, ideas and everything happening across PairOfDice.', 'wpnfinite' );
        }
    } elseif ( is_archive() ) {
        $title       = get_the_archive_title();
        $description = get_the_archive_description();
        $eyebrow     = is_category() ? __( 'Topic', 'wpnfinite' ) : __( 'Archive', 'wpnfinite' );
    }

    if ( ! $title ) {
        return;
    }
    ?>
    <header class="wpnfinite-editorial-archive-hero">
        <div class="wpnfinite-editorial-archive-copy">
            <span class="wpnfinite-editorial-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
            <h1><?php echo wp_kses_post( $title ); ?></h1>
            <?php if ( $description ) : ?><div class="wpnfinite-editorial-dek"><?php echo wp_kses_post( wpautop( $description ) ); ?></div><?php endif; ?>
        </div>
        <div class="wpnfinite-editorial-search"><?php get_search_form(); ?></div>
    </header>
    <?php
}

/**
 * Sidebar for single editorial posts.
 */
function wpnfinite_editorial_sidebar() {
    $categories = get_categories(
        array(
            'orderby' => 'count',
            'order'   => 'DESC',
            'number'  => 6,
        )
    );
    $recent = get_posts(
        array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 4,
            'post__not_in'   => array( get_the_ID() ),
            'no_found_rows'  => true,
        )
    );
    ?>
    <aside class="wpnfinite-editorial-sidebar" aria-label="<?php esc_attr_e( 'Article sidebar', 'wpnfinite' ); ?>">
        <div class="wpnfinite-sidebar-card wpnfinite-sidebar-search">
            <h3><?php esc_html_e( 'Search PairOfDice', 'wpnfinite' ); ?></h3>
            <?php get_search_form(); ?>
        </div>
        <?php if ( $categories ) : ?>
        <div class="wpnfinite-sidebar-card">
            <span class="wpnfinite-editorial-eyebrow"><?php esc_html_e( 'Explore', 'wpnfinite' ); ?></span>
            <h3><?php esc_html_e( 'Topics', 'wpnfinite' ); ?></h3>
            <div class="wpnfinite-topic-list">
                <?php foreach ( $categories as $category ) : ?>
                    <a href="<?php echo esc_url( get_category_link( $category ) ); ?>"><span><?php echo esc_html( $category->name ); ?></span><small><?php echo esc_html( $category->count ); ?></small></a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php if ( $recent ) : ?>
        <div class="wpnfinite-sidebar-card">
            <span class="wpnfinite-editorial-eyebrow"><?php esc_html_e( 'Keep reading', 'wpnfinite' ); ?></span>
            <h3><?php esc_html_e( 'Latest Stories', 'wpnfinite' ); ?></h3>
            <div class="wpnfinite-sidebar-stories">
                <?php foreach ( $recent as $story ) : ?>
                    <a href="<?php echo esc_url( get_permalink( $story ) ); ?>">
                        <span><?php echo esc_html( get_the_title( $story ) ); ?></span>
                        <small><?php echo esc_html( get_the_date( '', $story ) ); ?></small>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </aside>
    <?php
}

/**
 * Author/creator card beneath an article.
 */
function wpnfinite_editorial_author_card( $post_id = 0 ) {
    $post_id = $post_id ? absint( $post_id ) : get_the_ID();
    $user_id = (int) get_post_field( 'post_author', $post_id );
    $creator = wpnfinite_creator_for_user( $user_id );
    $url     = $creator instanceof WP_Post ? get_permalink( $creator ) : get_author_posts_url( $user_id );
    $name    = $creator instanceof WP_Post ? get_the_title( $creator ) : get_the_author_meta( 'display_name', $user_id );
    $bio     = $creator instanceof WP_Post ? wp_strip_all_tags( get_post_field( 'post_content', $creator->ID ) ) : get_the_author_meta( 'description', $user_id );
    ?>
    <section class="wpnfinite-author-card">
        <div class="wpnfinite-author-avatar"><?php echo get_avatar( $user_id, 96 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
        <div class="wpnfinite-author-copy">
            <span class="wpnfinite-editorial-eyebrow"><?php echo esc_html( $creator instanceof WP_Post ? __( 'PairOfDice Creator', 'wpnfinite' ) : __( 'About the author', 'wpnfinite' ) ); ?></span>
            <h3><?php echo esc_html( $name ); ?></h3>
            <?php if ( $bio ) : ?><p><?php echo esc_html( wp_trim_words( $bio, 34, '…' ) ); ?></p><?php endif; ?>
            <a class="wpnfinite-author-link" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $creator instanceof WP_Post ? __( 'View creator profile', 'wpnfinite' ) : __( 'More from this author', 'wpnfinite' ) ); ?> →</a>
        </div>
    </section>
    <?php
}

/**
 * Related posts, biased toward the story's primary category.
 */
function wpnfinite_editorial_related_posts( $post_id = 0, $limit = 3 ) {
    $post_id = $post_id ? absint( $post_id ) : get_the_ID();
    $term    = wpnfinite_editorial_primary_category( $post_id );
    $args    = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => absint( $limit ),
        'post__not_in'   => array( $post_id ),
        'no_found_rows'  => true,
    );
    if ( $term instanceof WP_Term ) {
        $args['cat'] = $term->term_id;
    }
    return get_posts( $args );
}
