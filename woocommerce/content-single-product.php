<?php
/**
 * WPNfinite single product presentation.
 *
 * Music products use a native music-commerce PDP. Standard products preserve
 * WooCommerce's normal hook-based content-single-product structure.
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

$is_music = function_exists( 'wpnfinite_is_music_product' )
	&& wpnfinite_is_music_product( get_the_ID() );

if ( ! $is_music ) :
	?>
	<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>
		<?php do_action( 'woocommerce_before_single_product_summary' ); ?>

		<div class="summary entry-summary">
			<?php do_action( 'woocommerce_single_product_summary' ); ?>
		</div>

		<?php do_action( 'woocommerce_after_single_product_summary' ); ?>
	</div>
	<?php
	return;
endif;

$data        = wpnfinite_music_product_display_data( $product->get_id() );
$preview_url = wpnfinite_get_product_preview_audio( $product->get_id() );
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'wpnfinite-music-pdp', $product ); ?>>
	<div class="wpnfinite-music-pdp__top">
		<section class="wpnfinite-music-pdp__media">
			<div class="wpnfinite-music-pdp__artwork">
				<?php
				if ( has_post_thumbnail() ) {
					echo get_the_post_thumbnail(
						get_the_ID(),
						'full',
						array(
							'class' => 'wpnfinite-music-pdp__image',
							'alt'   => esc_attr( $data['title'] ),
						)
					);
				} else {
					echo wc_placeholder_img(
						'woocommerce_single',
						array( 'class' => 'wpnfinite-music-pdp__image' )
					);
				}
				?>

				<?php if ( $preview_url ) : ?>
					<button
						type="button"
						class="wpnfinite-product-preview wpnfinite-music-pdp__preview-fab"
						data-wpnfinite-audio-preview="<?php echo esc_url( $preview_url ); ?>"
						data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
						aria-label="<?php echo esc_attr( sprintf( __( 'Play preview for %s', 'wpnfinite' ), $data['title'] ) ); ?>"
					>
						<span class="wpnfinite-product-preview__icon" aria-hidden="true">▶</span>
						<span class="screen-reader-text"><?php esc_html_e( 'Preview Beat', 'wpnfinite' ); ?></span>
					</button>
				<?php endif; ?>
			</div>
		</section>

		<aside class="wpnfinite-music-pdp__purchase">
			<span class="wpnfinite-music-pdp__eyebrow"><?php esc_html_e( 'Beat', 'wpnfinite' ); ?></span>

			<h1 class="wpnfinite-music-pdp__title"><?php echo esc_html( $data['title'] ); ?></h1>

			<?php if ( $data['artist'] ) : ?>
				<p class="wpnfinite-music-pdp__artist">
					<?php
					printf(
						/* translators: %s creator/producer name. */
						esc_html__( 'by %s', 'wpnfinite' ),
						'<strong>' . esc_html( $data['artist'] ) . '</strong>'
					);
					?>
				</p>
			<?php endif; ?>

			<div class="wpnfinite-music-pdp__price">
				<?php echo wp_kses_post( $product->get_price_html() ); ?>
			</div>

			<?php if ( $product->get_short_description() ) : ?>
				<div class="wpnfinite-music-pdp__short">
					<?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $preview_url ) : ?>
				<button
					type="button"
					class="wpnfinite-single-preview wpnfinite-product-preview"
					data-wpnfinite-audio-preview="<?php echo esc_url( $preview_url ); ?>"
					data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
				>
					<span class="wpnfinite-product-preview__icon" aria-hidden="true">▶</span>
					<span class="wpnfinite-product-preview__label"><?php esc_html_e( 'Preview Beat', 'wpnfinite' ); ?></span>
				</button>
			<?php endif; ?>

			<div class="wpnfinite-music-pdp__cart">
				<?php woocommerce_template_single_add_to_cart(); ?>
			</div>

			<div class="wpnfinite-music-purchase-note">
				<span><?php esc_html_e( 'Secure checkout', 'wpnfinite' ); ?></span>
				<span><?php esc_html_e( 'Instant digital delivery when applicable', 'wpnfinite' ); ?></span>
			</div>

			<div class="wpnfinite-music-pdp__meta">
				<?php woocommerce_template_single_meta(); ?>
			</div>
		</aside>
	</div>

	<div class="wpnfinite-music-pdp__details">
		<?php
		woocommerce_output_product_data_tabs();
		woocommerce_upsell_display();
		woocommerce_output_related_products();
		?>
	</div>
</div>
