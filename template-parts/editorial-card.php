<?php
/**
 * Editorial story card.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'wpnfinite-story-card' ); ?>>
    <a class="wpnfinite-story-card__image" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
        <?php if ( has_post_thumbnail() ) : ?>
            <?php the_post_thumbnail( 'large', array( 'loading' => 'lazy' ) ); ?>
        <?php else : ?>
            <span class="wpnfinite-story-card__placeholder"></span>
        <?php endif; ?>
    </a>
    <div class="wpnfinite-story-card__body">
        <div class="wpnfinite-story-card__topline"><?php wpnfinite_editorial_category_link(); ?><span><?php echo esc_html( wpnfinite_reading_time() ); ?></span></div>
        <?php the_title( '<h2 class="wpnfinite-story-card__title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' ); ?>
        <div class="wpnfinite-story-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24, '…' ) ); ?></div>
        <div class="wpnfinite-story-card__footer">
            <?php wpnfinite_editorial_byline(); ?>
            <time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
        </div>
    </div>
</article>
