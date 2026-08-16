<?php
get_header();

$used_ids     = array();
$reservations = wpnfinite_media_reservations();
$reserved_ids = wpnfinite_media_reserved_ids( $reservations );

/**
 * HERO
 * Reserve specialist Blog/Collection inventory first. The hero primarily
 * composes from broad editorial content, but can fall back to anything when
 * the site is still new and inventory is sparse.
 */
$hero_query = wpnfinite_media_query(
    array(
        'posts_per_page' => 4,
        'post__not_in'    => $reserved_ids,
    )
);

$hero_posts = $hero_query->posts;

if ( count( $hero_posts ) < 4 ) {
    $fallback_exclude = array_merge( $used_ids, wp_list_pluck( $hero_posts, 'ID' ) );
    $fallback_query   = wpnfinite_media_query(
        array(
            'posts_per_page' => 4 - count( $hero_posts ),
            'post__not_in'    => $fallback_exclude,
        )
    );
    $hero_posts = array_merge( $hero_posts, $fallback_query->posts );
}

$lead = ! empty( $hero_posts ) ? array_shift( $hero_posts ) : null;

if ( $lead ) {
    $used_ids[] = $lead->ID;
}
wpnfinite_media_add_used_ids( $used_ids, $hero_posts );
?>
<div class="wpnfinite-media-home">
    <?php if ( wpnfinite_media_home_setting( 'show_intro', true ) ) : ?>
        <section class="wpnfinite-media-masthead">
            <div class="wpnfinite-container">
                <div class="wpnfinite-media-masthead__brand">
                    <span class="wpnfinite-media-masthead__eyebrow"><?php esc_html_e( 'Latest', 'wpnfinite' ); ?></span>
                    <h1><?php bloginfo( 'name' ); ?></h1>
                    <?php if ( get_bloginfo( 'description' ) ) : ?>
                        <p><?php bloginfo( 'description' ); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ( $lead ) : ?>
        <section class="wpnfinite-container wpnfinite-media-lead-grid">
            <?php setup_postdata( $lead ); ?>
            <article class="wpnfinite-media-lead">
                <a class="wpnfinite-media-lead__media" href="<?php the_permalink(); ?>">
                    <?php
                    if ( has_post_thumbnail() ) {
                        the_post_thumbnail( 'full' );
                    } else {
                        echo '<span class="wpnfinite-media-card__placeholder"></span>';
                    }
                    ?>
                    <span class="wpnfinite-media-badge"><?php echo esc_html( wpnfinite_media_type_label( get_the_ID() ) ); ?></span>
                </a>

                <div class="wpnfinite-media-lead__body">
                    <div class="wpnfinite-media-meta"><?php echo esc_html( get_the_date() ); ?></div>
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <p><?php echo esc_html( wpnfinite_media_get_excerpt( get_the_ID(), 34 ) ); ?></p>
                </div>
            </article>
            <?php wp_reset_postdata(); ?>

            <?php if ( $hero_posts ) : ?>
                <div class="wpnfinite-media-supporting">
                    <?php foreach ( $hero_posts as $post ) : setup_postdata( $post ); ?>
                        <article class="wpnfinite-media-mini">
                            <a class="wpnfinite-media-mini__media" href="<?php the_permalink(); ?>">
                                <?php
                                if ( has_post_thumbnail() ) {
                                    the_post_thumbnail( 'medium_large' );
                                } else {
                                    echo '<span class="wpnfinite-media-card__placeholder"></span>';
                                }
                                ?>
                            </a>
                            <div>
                                <span class="wpnfinite-media-kicker"><?php echo esc_html( wpnfinite_media_type_label( get_the_ID() ) ); ?></span>
                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            </div>
                        </article>
                    <?php endforeach; wp_reset_postdata(); ?>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <?php
    /**
     * LATEST
     * Do not consume content reserved for dedicated Blog/Collection modules.
     */
    $latest_exclude = array_values( array_unique( array_merge( $used_ids, $reserved_ids ) ) );
    $latest         = wpnfinite_media_query(
        array(
            'posts_per_page' => 6,
            'post__not_in'    => $latest_exclude,
        )
    );

    if ( $latest->have_posts() ) :
        wpnfinite_media_add_used_ids( $used_ids, $latest->posts );
        $latest_count = count( $latest->posts );
        ?>
        <section class="wpnfinite-container wpnfinite-media-section">
            <div class="wpnfinite-media-section__head">
                <div>
                    <span><?php esc_html_e( 'Fresh', 'wpnfinite' ); ?></span>
                    <h2><?php esc_html_e( 'Latest Stories', 'wpnfinite' ); ?></h2>
                </div>
            </div>

            <div class="wpnfinite-media-grid <?php echo esc_attr( wpnfinite_media_grid_count_class( $latest_count ) ); ?>">
                <?php
                while ( $latest->have_posts() ) :
                    $latest->the_post();
                    get_template_part( 'template-parts/media/card' );
                endwhile;
                wp_reset_postdata();
                ?>
            </div>
        </section>
    <?php endif; ?>

    <?php
    /**
     * BLOGS
     */
    if (
        wpnfinite_media_home_setting( 'show_blogs', true ) &&
        post_type_exists( 'blog' ) &&
        ! empty( $reservations['blog'] )
    ) :
        $blog_query = wpnfinite_media_reserved_query( 'blog', $reservations['blog'], $used_ids );

        if ( $blog_query && $blog_query->have_posts() ) :
            wpnfinite_media_add_used_ids( $used_ids, $blog_query->posts );
            $blog_count = count( $blog_query->posts );
            ?>
            <section class="wpnfinite-container wpnfinite-media-section wpnfinite-media-section--blogs">
                <div class="wpnfinite-media-section__head">
                    <div>
                        <span><?php esc_html_e( 'Editorial', 'wpnfinite' ); ?></span>
                        <h2><?php esc_html_e( 'Latest From the Blog', 'wpnfinite' ); ?></h2>
                    </div>
                    <a href="<?php echo esc_url( wpnfinite_media_archive_link( 'blog' ) ); ?>">
                        <?php esc_html_e( 'View all', 'wpnfinite' ); ?> <span aria-hidden="true">→</span>
                    </a>
                </div>

                <div class="wpnfinite-media-grid wpnfinite-media-grid--rail <?php echo esc_attr( wpnfinite_media_grid_count_class( $blog_count ) ); ?>">
                    <?php
                    while ( $blog_query->have_posts() ) :
                        $blog_query->the_post();
                        get_template_part( 'template-parts/media/card' );
                    endwhile;
                    wp_reset_postdata();
                    ?>
                </div>
            </section>
        <?php endif; ?>
    <?php endif; ?>

    <?php
    /**
     * COLLECTIONS
     * "Collections" is the live public name for ShopBlocks shoppable content.
     */
    if (
        wpnfinite_media_home_setting( 'show_collections', true ) &&
        post_type_exists( 'collection' ) &&
        ! empty( $reservations['collection'] )
    ) :
        $collection_query = wpnfinite_media_reserved_query( 'collection', $reservations['collection'], $used_ids );

        if ( $collection_query && $collection_query->have_posts() ) :
            wpnfinite_media_add_used_ids( $used_ids, $collection_query->posts );
            $collection_count = count( $collection_query->posts );
            ?>
            <section class="wpnfinite-container wpnfinite-media-section wpnfinite-media-section--collection">
                <div class="wpnfinite-media-section__head">
                    <div>
                        <span><?php esc_html_e( 'Shop', 'wpnfinite' ); ?></span>
                        <h2><?php esc_html_e( 'Collections', 'wpnfinite' ); ?></h2>
                    </div>
                    <a href="<?php echo esc_url( wpnfinite_media_archive_link( 'collection' ) ); ?>">
                        <?php esc_html_e( 'View all', 'wpnfinite' ); ?> <span aria-hidden="true">→</span>
                    </a>
                </div>

                <div class="wpnfinite-media-grid wpnfinite-media-grid--rail <?php echo esc_attr( wpnfinite_media_grid_count_class( $collection_count ) ); ?>">
                    <?php
                    while ( $collection_query->have_posts() ) :
                        $collection_query->the_post();
                        get_template_part( 'template-parts/media/card' );
                    endwhile;
                    wp_reset_postdata();
                    ?>
                </div>
            </section>
        <?php endif; ?>
    <?php endif; ?>

    <?php
    /**
     * CATEGORY RAILS
     * Run after specialist content so category queries cannot consume content
     * needed by Blog/Collection modules.
     */
    if ( wpnfinite_media_home_setting( 'show_categories', true ) ) :
        $category_terms = wpnfinite_media_category_terms( 2 );

        foreach ( $category_terms as $term ) :
            $category_query = wpnfinite_media_category_query( $term->term_id, 4, $used_ids );

            if ( ! $category_query->have_posts() ) {
                continue;
            }

            wpnfinite_media_add_used_ids( $used_ids, $category_query->posts );
            $category_count = count( $category_query->posts );
            ?>
            <section class="wpnfinite-container wpnfinite-media-section wpnfinite-media-section--rail">
                <div class="wpnfinite-media-section__head">
                    <div>
                        <span><?php esc_html_e( 'Explore', 'wpnfinite' ); ?></span>
                        <h2><?php echo esc_html( $term->name ); ?></h2>
                    </div>
                    <a href="<?php echo esc_url( get_term_link( $term ) ); ?>">
                        <?php esc_html_e( 'View all', 'wpnfinite' ); ?> <span aria-hidden="true">→</span>
                    </a>
                </div>

                <div class="wpnfinite-media-grid wpnfinite-media-grid--rail <?php echo esc_attr( wpnfinite_media_grid_count_class( $category_count ) ); ?>">
                    <?php
                    while ( $category_query->have_posts() ) :
                        $category_query->the_post();
                        get_template_part( 'template-parts/media/card' );
                    endwhile;
                    wp_reset_postdata();
                    ?>
                </div>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php
    /**
     * CREATOR SPOTLIGHT
     */
    if ( wpnfinite_media_home_setting( 'show_creators', true ) && function_exists( 'wpnfinite_has_nfinite_creators' ) && wpnfinite_has_nfinite_creators() ) :
        $creator_query = wpnfinite_media_creator_query( 4 );

        if ( $creator_query && $creator_query->have_posts() ) :
            $creator_count = count( $creator_query->posts );
            ?>
            <section class="wpnfinite-container wpnfinite-media-section wpnfinite-creator-spotlight">
                <div class="wpnfinite-media-section__head">
                    <div>
                        <span><?php esc_html_e( 'Spotlight', 'wpnfinite' ); ?></span>
                        <h2><?php esc_html_e( 'Featured Creators', 'wpnfinite' ); ?></h2>
                    </div>
                    <a href="<?php echo esc_url( get_post_type_archive_link( 'nfinite_creator' ) ); ?>">
                        <?php esc_html_e( 'View all', 'wpnfinite' ); ?> <span aria-hidden="true">→</span>
                    </a>
                </div>

                <div class="wpnfinite-creator-spotlight__grid <?php echo esc_attr( wpnfinite_media_grid_count_class( $creator_count ) ); ?>">
                    <?php while ( $creator_query->have_posts() ) : $creator_query->the_post(); ?>
                        <article class="wpnfinite-creator-spotlight__card">
                            <a class="wpnfinite-creator-spotlight__media" href="<?php the_permalink(); ?>">
                                <?php
                                if ( has_post_thumbnail() ) {
                                    the_post_thumbnail( 'large' );
                                } else {
                                    echo '<span class="wpnfinite-media-card__placeholder"></span>';
                                }
                                ?>
                            </a>
                            <div class="wpnfinite-creator-spotlight__body">
                                <span class="wpnfinite-media-kicker"><?php echo esc_html( wpnfinite_media_creator_types( get_the_ID() ) ); ?></span>
                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                <?php
                                $location = get_post_meta( get_the_ID(), '_nfinite_creator_location', true );
                                if ( $location ) :
                                ?>
                                    <p><?php echo esc_html( $location ); ?></p>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </section>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ( wpnfinite_media_home_setting( 'show_newsletter', true ) ) : ?>
        <?php
        $integration_markup = wpnfinite_media_newsletter_markup();
        $cta_url            = wpnfinite_media_home_setting( 'newsletter_url', '' );
        $cta_label          = wpnfinite_media_home_setting( 'newsletter_label', __( 'Explore more', 'wpnfinite' ) );

        if ( ! $cta_url ) {
            $cta_url = post_type_exists( 'blog' ) ? wpnfinite_media_archive_link( 'blog' ) : wpnfinite_media_archive_link( 'post' );
        }
        ?>
        <section class="wpnfinite-container wpnfinite-media-cta">
            <div class="wpnfinite-media-cta__copy">
                <span class="wpnfinite-media-kicker"><?php esc_html_e( 'Stay connected', 'wpnfinite' ); ?></span>
                <h2><?php echo esc_html( wpnfinite_media_home_setting( 'newsletter_title', __( 'Stay in the loop', 'wpnfinite' ) ) ); ?></h2>
                <p><?php echo esc_html( wpnfinite_media_home_setting( 'newsletter_text', __( 'Get the latest stories, blogs, and Collections from our newsroom.', 'wpnfinite' ) ) ); ?></p>
            </div>

            <div class="wpnfinite-media-cta__action">
                <?php if ( $integration_markup ) : ?>
                    <?php echo wp_kses_post( $integration_markup ); ?>
                <?php else : ?>
                    <a class="wpnfinite-btn wpnfinite-btn-primary" href="<?php echo esc_url( $cta_url ); ?>">
                        <?php echo esc_html( $cta_label ); ?>
                    </a>
                <?php endif; ?>

                <?php do_action( 'wpnfinite_media_newsletter_action' ); ?>
            </div>
        </section>
    <?php endif; ?>

    <?php
    /**
     * MORE STORIES
     * Final chronological fallback can use anything that has not appeared yet.
     */
    $more = wpnfinite_media_query(
        array(
            'posts_per_page' => 6,
            'post__not_in'    => $used_ids,
        )
    );

    if ( $more->have_posts() ) :
        $more_count = count( $more->posts );
        ?>
        <section class="wpnfinite-container wpnfinite-media-section">
            <div class="wpnfinite-media-section__head">
                <div>
                    <span><?php esc_html_e( 'More', 'wpnfinite' ); ?></span>
                    <h2><?php esc_html_e( 'More Stories', 'wpnfinite' ); ?></h2>
                </div>
            </div>

            <div class="wpnfinite-media-grid <?php echo esc_attr( wpnfinite_media_grid_count_class( $more_count ) ); ?>">
                <?php
                while ( $more->have_posts() ) :
                    $more->the_post();
                    get_template_part( 'template-parts/media/card' );
                endwhile;
                wp_reset_postdata();
                ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ( ! $lead ) : ?>
        <section class="wpnfinite-container wpnfinite-media-empty">
            <span class="wpnfinite-media-kicker"><?php esc_html_e( 'Ready to publish', 'wpnfinite' ); ?></span>
            <h2><?php esc_html_e( 'Your media homepage builds itself as you publish content.', 'wpnfinite' ); ?></h2>
            <p><?php esc_html_e( 'Publish a WordPress Post or activate ShopBlocks and add Blogs or Collections. WPNfinite will automatically surface them here.', 'wpnfinite' ); ?></p>
        </section>
    <?php endif; ?>
</div>
<?php get_footer(); ?>
