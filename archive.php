<?php get_header(); ?>
<section class="wpnfinite-container wpnfinite-content-area">
<?php if ( have_posts() ) : ?><div class="wpnfinite-post-grid"><?php while ( have_posts() ) : the_post(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'wpnfinite-card' ); ?>>
<?php if ( has_post_thumbnail() ) : ?><a class="wpnfinite-card-image" href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'large' ); ?></a><?php endif; ?>
<div class="wpnfinite-card-body"><div class="wpnfinite-entry-meta"><?php wpnfinite_posted_on(); ?></div><?php the_title( '<h2 class="wpnfinite-entry-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' ); ?><div class="wpnfinite-entry-excerpt"><?php the_excerpt(); ?></div><?php echo wpnfinite_read_more_link(); ?></div>
</article>
<?php endwhile; ?></div><?php the_posts_pagination(); else : ?><article class="wpnfinite-card"><div class="wpnfinite-card-body"><?php esc_html_e( 'No posts found.', 'wpnfinite' ); ?></div></article><?php endif; ?>
</section>
<?php get_footer(); ?>
