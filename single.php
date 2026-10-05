<?php get_header(); ?>
<?php while ( have_posts() ) : the_post(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'wpnfinite-editorial-single' ); ?>>
    <header class="wpnfinite-editorial-hero">
        <div class="wpnfinite-container wpnfinite-editorial-hero__inner">
            <div class="wpnfinite-editorial-hero__copy">
                <?php wpnfinite_editorial_category_link(); ?>
                <?php the_title( '<h1 class="wpnfinite-editorial-title">', '</h1>' ); ?>
                <?php if ( has_excerpt() ) : ?><div class="wpnfinite-editorial-dek"><?php echo esc_html( get_the_excerpt() ); ?></div><?php endif; ?>
                <div class="wpnfinite-editorial-meta">
                    <?php wpnfinite_editorial_byline( get_the_ID(), true ); ?>
                    <span class="wpnfinite-editorial-meta__item"><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></span>
                    <span class="wpnfinite-editorial-meta__item"><?php echo esc_html( wpnfinite_reading_time() ); ?></span>
                </div>
            </div>
        </div>
    </header>

    <?php if ( has_post_thumbnail() ) : ?>
        <div class="wpnfinite-container wpnfinite-editorial-featured"><?php the_post_thumbnail( 'full' ); ?></div>
    <?php endif; ?>

    <div class="wpnfinite-container wpnfinite-editorial-layout">
        <div class="wpnfinite-editorial-main">
            <div class="wpnfinite-entry-content wpnfinite-editorial-content"><?php the_content(); ?></div>
            <?php
            wp_link_pages(
                array(
                    'before' => '<nav class="wpnfinite-page-links">' . esc_html__( 'Pages:', 'wpnfinite' ),
                    'after'  => '</nav>',
                )
            );
            ?>
            <?php wpnfinite_editorial_author_card(); ?>
        </div>
        <?php wpnfinite_editorial_sidebar(); ?>
    </div>

    <?php $related = wpnfinite_editorial_related_posts(); if ( $related ) : ?>
    <section class="wpnfinite-related-stories">
        <div class="wpnfinite-container">
            <header class="wpnfinite-related-heading"><span class="wpnfinite-editorial-eyebrow"><?php esc_html_e( 'Discover more', 'wpnfinite' ); ?></span><h2><?php esc_html_e( 'Related Stories', 'wpnfinite' ); ?></h2></header>
            <div class="wpnfinite-editorial-grid">
                <?php global $post; $original_post = $post; foreach ( $related as $post ) : setup_postdata( $post ); get_template_part( 'template-parts/editorial-card' ); endforeach; $post = $original_post; wp_reset_postdata(); ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ( comments_open() || get_comments_number() ) : ?><div class="wpnfinite-container wpnfinite-editorial-comments"><?php comments_template(); ?></div><?php endif; ?>
</article>
<?php endwhile; ?>
<?php get_footer(); ?>
