<?php get_header(); ?>
<section class="wpnfinite-editorial-archive">
    <div class="wpnfinite-container">
        <?php wpnfinite_editorial_archive_header(); ?>
        <?php if ( have_posts() ) : ?>
            <div class="wpnfinite-editorial-grid">
                <?php while ( have_posts() ) : the_post(); get_template_part( 'template-parts/editorial-card' ); endwhile; ?>
            </div>
            <div class="wpnfinite-editorial-pagination"><?php the_posts_pagination(); ?></div>
        <?php else : ?>
            <div class="wpnfinite-editorial-empty"><h2><?php esc_html_e( 'No stories found.', 'wpnfinite' ); ?></h2><p><?php esc_html_e( 'Try another search or explore a different topic.', 'wpnfinite' ); ?></p><?php get_search_form(); ?></div>
        <?php endif; ?>
    </div>
</section>
<?php get_footer(); ?>
