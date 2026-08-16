<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
</main></div>
<?php if ( ! wpnfinite_is_elementor_canvas() ) : ?>
<footer class="wpnfinite-footer" id="site-footer">
    <div class="wpnfinite-container wpnfinite-footer-grid">
        <div><div class="wpnfinite-footer-brand"><span class="wpnfinite-footer-mark"><?php echo esc_html( strtoupper( substr( get_bloginfo( 'name' ), 0, 1 ) ) ); ?></span><div><h2 class="wpnfinite-footer-title"><?php bloginfo( 'name' ); ?></h2><p class="wpnfinite-footer-text"><?php bloginfo( 'description' ); ?></p></div></div></div>
        <div><h3><?php esc_html_e( 'Navigation', 'wpnfinite' ); ?></h3><?php wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'menu_class' => 'wpnfinite-footer-menu', 'fallback_cb' => 'wpnfinite_footer_menu_fallback', 'depth' => 1 ) ); ?></div>
        <div><h3><?php esc_html_e( 'Built for publishing', 'wpnfinite' ); ?></h3><p class="wpnfinite-footer-text"><?php esc_html_e( 'WPNfinite is a content-first WordPress media framework designed to work natively with the Block Editor, ShopBlocks, Nfinite Dashboard, and WooCommerce.', 'wpnfinite' ); ?></p></div>
    </div>
    <div class="wpnfinite-container wpnfinite-footer-bottom"><span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></span><span><?php esc_html_e( 'Built with WPNfinite', 'wpnfinite' ); ?></span></div>
</footer>
<?php endif; ?>
<?php wp_footer(); ?>
</body></html>
