<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$post_id = get_the_ID();
?>
<article <?php post_class( 'wpnfinite-media-card' ); ?>>
    <a class="wpnfinite-media-card__media" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
        <?php if ( has_post_thumbnail() ) : ?>
            <?php the_post_thumbnail( 'large', array( 'loading' => 'lazy' ) ); ?>
        <?php else : ?>
            <span class="wpnfinite-media-card__placeholder" aria-hidden="true"></span>
        <?php endif; ?>
        <span class="wpnfinite-media-badge"><?php echo esc_html( wpnfinite_media_type_label( $post_id ) ); ?></span>
    </a>
    <div class="wpnfinite-media-card__body">
        <div class="wpnfinite-media-meta"><span><?php echo esc_html( get_the_date() ); ?></span></div>
        <h3 class="wpnfinite-media-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <p><?php echo esc_html( wpnfinite_media_get_excerpt( $post_id, 20 ) ); ?></p>
    </div>
</article>
