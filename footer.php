<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main></div>

<?php if ( ! wpnfinite_is_elementor_canvas() ) : ?>
	<?php
	$show_navigation = wpnfinite_footer_show_navigation();
	$show_info       = wpnfinite_footer_show_info();
	$show_credit     = wpnfinite_footer_show_credit();

	$column_count = 1 + ( $show_navigation ? 1 : 0 ) + ( $show_info ? 1 : 0 );
	?>
	<footer class="wpnfinite-footer" id="site-footer">
		<div class="wpnfinite-container wpnfinite-footer-grid wpnfinite-footer-grid--<?php echo esc_attr( $column_count ); ?>">
			<div class="wpnfinite-footer-column wpnfinite-footer-column--brand">
				<div class="wpnfinite-footer-brand">
					<span class="wpnfinite-footer-mark">
						<?php echo esc_html( strtoupper( substr( get_bloginfo( 'name' ), 0, 1 ) ) ); ?>
					</span>

					<div>
						<h2 class="wpnfinite-footer-title"><?php bloginfo( 'name' ); ?></h2>

						<?php if ( wpnfinite_footer_brand_description() ) : ?>
							<p class="wpnfinite-footer-text">
								<?php echo esc_html( wpnfinite_footer_brand_description() ); ?>
							</p>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<?php if ( $show_navigation ) : ?>
				<div class="wpnfinite-footer-column wpnfinite-footer-column--navigation">
					<?php if ( wpnfinite_footer_navigation_heading() ) : ?>
						<h3><?php echo esc_html( wpnfinite_footer_navigation_heading() ); ?></h3>
					<?php endif; ?>

					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'container'      => false,
							'menu_class'     => 'wpnfinite-footer-menu',
							'fallback_cb'    => 'wpnfinite_footer_menu_fallback',
							'depth'          => 1,
						)
					);
					?>
				</div>
			<?php endif; ?>

			<?php if ( $show_info ) : ?>
				<div class="wpnfinite-footer-column wpnfinite-footer-column--info">
					<?php if ( wpnfinite_footer_info_heading() ) : ?>
						<h3><?php echo esc_html( wpnfinite_footer_info_heading() ); ?></h3>
					<?php endif; ?>

					<?php if ( wpnfinite_footer_info_text() ) : ?>
						<p class="wpnfinite-footer-text">
							<?php echo nl2br( esc_html( wpnfinite_footer_info_text() ) ); ?>
						</p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="wpnfinite-container wpnfinite-footer-bottom<?php echo $show_credit ? '' : ' wpnfinite-footer-bottom--single'; ?>">
			<span><?php echo esc_html( wpnfinite_footer_copyright() ); ?></span>

			<?php if ( $show_credit && wpnfinite_footer_credit_text() ) : ?>
				<span><?php echo esc_html( wpnfinite_footer_credit_text() ); ?></span>
			<?php endif; ?>
		</div>
	</footer>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
