<?php
/**
 * WPNfinite WooCommerce wrapper.
 *
 * Uses woocommerce_content() so WooCommerce still owns its internal template
 * hierarchy while WPNfinite supplies the site-width/layout shell.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="primary" class="wpnfinite-woocommerce-main">
	<div class="wpnfinite-container wpnfinite-woocommerce-shell">
		<?php woocommerce_content(); ?>
	</div>
</main>
<?php
get_footer();
