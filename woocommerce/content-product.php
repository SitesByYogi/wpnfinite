<?php
/**
 * WPNfinite product card.
 *
 * Music products use native WPNfinite markup. Standard products retain the
 * normal WooCommerce hook-based loop markup.
 */

defined( 'ABSPATH' ) || exit;

global $product;

if (
	! $product ||
	! $product->is_visible()
) {
	return;
}

$is_music = function_exists( 'wpnfinite_is_music_product' )
	&& wpnfinite_is_music_product( $product->get_id() );

if ( ! $is_music ) :
	?>
	<li <?php wc_product_class( '', $product ); ?>>
		<?php
		do_action( 'woocommerce_before_shop_loop_item' );
		do_action( 'woocommerce_before_shop_loop_item_title' );
		do_action( 'woocommerce_shop_loop_item_title' );
		do_action( 'woocommerce_after_shop_loop_item_title' );
		do_action( 'woocommerce_after_shop_loop_item' );
		?>
	</li>
	<?php
	return;
endif;

$data        = wpnfinite_music_product_display_data( $product->get_id() );
$preview_url = wpnfinite_get_product_preview_audio( $product->get_id() );
$cta         = wpnfinite_music_product_archive_cta( $product );
$image_id    = $product->get_image_id();
$image_html  = $image_id
	? wp_get_attachment_image(
		$image_id,
		'woocommerce_thumbnail',
		false,
		array(
			'class'   => 'wpnfinite-product-card__image',
			'loading' => 'lazy',
		)
	)
	: wc_placeholder_img( 'woocommerce_thumbnail', array( 'class' => 'wpnfinite-product-card__image' ) );
?>
<li <?php wc_product_class( 'wpnfinite-product-card', $product ); ?>>
	<div class="wpnfinite-product-card__media">
		<a class="wpnfinite-product-card__media-link" href="<?php echo esc_url( $product->get_permalink() ); ?>">
			<?php echo wp_kses_post( $image_html ); ?>
		</a>

		<span class="wpnfinite-product-card__type"><?php esc_html_e( 'Beat', 'wpnfinite' ); ?></span>

		<?php if ( $product->is_on_sale() ) : ?>
			<span class="wpnfinite-product-card__sale"><?php esc_html_e( 'Sale', 'wpnfinite' ); ?></span>
		<?php endif; ?>

		<?php if ( $preview_url ) : ?>
			<button
				type="button"
				class="wpnfinite-product-preview wpnfinite-product-card__preview"
				data-wpnfinite-audio-preview="<?php echo esc_url( $preview_url ); ?>"
				data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
				aria-label="<?php echo esc_attr( sprintf( __( 'Play preview for %s', 'wpnfinite' ), $data['title'] ) ); ?>"
			>
				<span class="wpnfinite-product-preview__icon" aria-hidden="true">▶</span>
				<span class="screen-reader-text"><?php esc_html_e( 'Preview', 'wpnfinite' ); ?></span>
			</button>
		<?php endif; ?>
	</div>

	<div class="wpnfinite-product-card__content">
		<div class="wpnfinite-product-card__meta"><?php esc_html_e( 'Beat', 'wpnfinite' ); ?></div>

		<h2 class="wpnfinite-product-card__title">
			<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
				<?php echo esc_html( $data['title'] ); ?>
			</a>
		</h2>

		<?php if ( $data['artist'] ) : ?>
			<div class="wpnfinite-product-card__artist"><?php echo esc_html( $data['artist'] ); ?></div>
		<?php endif; ?>

		<div class="wpnfinite-product-card__footer">
			<div class="wpnfinite-product-card__price">
				<?php echo wp_kses_post( $product->get_price_html() ); ?>
			</div>

			<a
				href="<?php echo esc_url( $cta['url'] ); ?>"
				class="<?php echo esc_attr( $cta['class'] ); ?>"
				<?php if ( $product->supports( 'ajax_add_to_cart' ) && false !== strpos( $cta['class'], 'ajax_add_to_cart' ) ) : ?>
					data-quantity="1"
					data-product_id="<?php echo esc_attr( $product->get_id() ); ?>"
					data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
					aria-label="<?php echo esc_attr( $product->add_to_cart_description() ); ?>"
				<?php endif; ?>
			>
				<span><?php echo esc_html( $cta['label'] ); ?></span>
				<span aria-hidden="true">→</span>
			</a>
		</div>
	</div>
</li>
