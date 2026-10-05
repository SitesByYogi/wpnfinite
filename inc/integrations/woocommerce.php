<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPNfinite WooCommerce integration.
 *
 * The theme intentionally relies on WooCommerce hooks and a lightweight
 * woocommerce.php shell instead of copying archive-product.php/content-product.php.
 * This keeps the integration resilient to WooCommerce template updates.
 */

function wpnfinite_woocommerce_setup_hooks() {
	if ( ! wpnfinite_has_woocommerce() ) {
		return;
	}

	// WPNfinite uses a full-width product archive rather than Woo's sidebar.
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

	// Preview control belongs after WooCommerce closes the product permalink
	// and before the standard Add to Cart button.
	add_action( 'woocommerce_after_shop_loop_item', 'wpnfinite_product_audio_preview_button', 7 );
}
add_action( 'wp', 'wpnfinite_woocommerce_setup_hooks' );

/**
 * Supported public preview file extensions.
 */
function wpnfinite_audio_preview_extensions() {
	return apply_filters(
		'wpnfinite_audio_preview_extensions',
		array( 'mp3', 'm4a', 'ogg', 'oga', 'wav', 'aac' )
	);
}

/**
 * Is a URL a supported audio file?
 */
function wpnfinite_is_audio_preview_url( $url ) {
	if ( ! $url ) {
		return false;
	}

	$path = wp_parse_url( $url, PHP_URL_PATH );
	$ext  = strtolower( pathinfo( (string) $path, PATHINFO_EXTENSION ) );

	return in_array( $ext, wpnfinite_audio_preview_extensions(), true );
}

/**
 * Resolve a product preview source.
 *
 * Order:
 * 1. Dedicated WPNfinite preview field.
 * 2. Optional downloadable-audio fallback, only when explicitly enabled.
 * 3. Filter hook for Nfinite/other integrations.
 */
function wpnfinite_get_product_preview_audio( $product_id ) {
	$product_id = absint( $product_id );
	$url        = esc_url_raw( get_post_meta( $product_id, '_wpnfinite_audio_preview', true ) );

	if ( $url && wpnfinite_is_audio_preview_url( $url ) ) {
		return apply_filters( 'wpnfinite_product_preview_audio', $url, $product_id );
	}

	$allow_download_fallback = 'yes' === get_post_meta(
		$product_id,
		'_wpnfinite_audio_download_fallback',
		true
	);

	if ( $allow_download_fallback && function_exists( 'wc_get_product' ) ) {
		$product = wc_get_product( $product_id );

		if ( $product && $product->is_downloadable() ) {
			foreach ( $product->get_downloads() as $download ) {
				$file = $download->get_file();

				if ( wpnfinite_is_audio_preview_url( $file ) ) {
					$url = esc_url_raw( $file );
					break;
				}
			}
		}
	}

	/**
	 * Allow Nfinite or another integration to provide a preview URL.
	 *
	 * @param string $url        Preview URL, possibly empty.
	 * @param int    $product_id WooCommerce product ID.
	 */
	return apply_filters( 'wpnfinite_product_preview_audio', $url, $product_id );
}


/**
 * Whether a WooCommerce product should receive the music-commerce presentation.
 *
 * A dedicated/filtered audio preview is the primary signal. The filter allows
 * integrations to force the music experience for products that are licensed
 * music even when previews are supplied elsewhere.
 */
function wpnfinite_is_music_product( $product_id ) {
	$product_id = absint( $product_id );

	if ( ! $product_id || 'product' !== get_post_type( $product_id ) ) {
		return false;
	}

	// 1. Explicit Product Data override.
	$manual = get_post_meta( $product_id, '_wpnfinite_music_product', true );

	if ( 'yes' === $manual ) {
		return true;
	}

	if ( 'no' === $manual ) {
		return false;
	}

	// 2. A playable WPNfinite/Nfinite preview is a strong signal.
	$is_music = (bool) wpnfinite_get_product_preview_audio( $product_id );

	// 3. Infer from common WooCommerce product categories.
	if ( ! $is_music && taxonomy_exists( 'product_cat' ) ) {
		$terms = wp_get_post_terms(
			$product_id,
			'product_cat',
			array( 'fields' => 'all' )
		);

		if ( ! is_wp_error( $terms ) ) {
			$music_tokens = array(
				'beat',
				'beats',
				'instrumental',
				'instrumentals',
				'music',
				'single',
				'singles',
				'audio',
				'track',
				'tracks',
			);

			foreach ( $terms as $term ) {
				$haystacks = array(
					strtolower( (string) $term->slug ),
					strtolower( (string) $term->name ),
				);

				foreach ( $haystacks as $haystack ) {
					foreach ( $music_tokens as $token ) {
						if (
							$haystack === $token ||
							false !== strpos( $haystack, $token )
						) {
							$is_music = true;
							break 3;
						}
					}
				}
			}
		}
	}

	return (bool) apply_filters(
		'wpnfinite_is_music_product',
		$is_music,
		$product_id
	);
}


/**
 * Return presentation data for a music product.
 *
 * Artist / producer resolution:
 * 1. Dedicated WPNfinite field.
 * 2. Product title suffix after " | " when present.
 * 3. First product tag/category as a light fallback.
 */
function wpnfinite_music_product_display_data( $product_id ) {
	$product_id = absint( $product_id );
	$title      = get_the_title( $product_id );
	$artist     = sanitize_text_field(
		(string) get_post_meta( $product_id, '_wpnfinite_music_artist', true )
	);

	if ( false !== strpos( $title, '|' ) ) {
		$parts = array_map( 'trim', explode( '|', $title, 2 ) );

		if ( ! empty( $parts[0] ) ) {
			$title = $parts[0];
		}

		if ( ! $artist && ! empty( $parts[1] ) ) {
			$artist = $parts[1];
		}
	}

	if ( ! $artist ) {
		$tags = wp_get_post_terms(
			$product_id,
			'product_tag',
			array( 'fields' => 'names' )
		);

		if ( ! is_wp_error( $tags ) && ! empty( $tags ) ) {
			$artist = sanitize_text_field( $tags[0] );
		}
	}

	return apply_filters(
		'wpnfinite_music_product_display_data',
		array(
			'title'  => sanitize_text_field( $title ),
			'artist' => sanitize_text_field( $artist ),
		),
		$product_id
	);
}

/**
 * Archive CTA URL / behavior.
 */
function wpnfinite_music_product_archive_cta( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return array(
			'url'   => '#',
			'label' => __( 'View Product', 'wpnfinite' ),
			'class' => 'wpnfinite-product-card__cta',
		);
	}

	$label = __( 'Get Beat', 'wpnfinite' );
	$url   = $product->get_permalink();
	$class = 'wpnfinite-product-card__cta';

	if ( $product->is_type( 'variable' ) || ! $product->is_purchasable() ) {
		$label = __( 'View License', 'wpnfinite' );
	} elseif ( $product->is_type( 'external' ) ) {
		$url = $product->add_to_cart_url();
	} elseif ( $product->is_in_stock() ) {
		$url = $product->add_to_cart_url();

		if ( $product->supports( 'ajax_add_to_cart' ) ) {
			$class .= ' add_to_cart_button ajax_add_to_cart';
		}
	}

	return array(
		'url'   => $url,
		'label' => $label,
		'class' => $class,
	);
}

/**
 * Contextual CTA label for archive cards.
 */
function wpnfinite_product_archive_cta_label( $label, $product ) {
	if ( ! $product instanceof WC_Product ) {
		return $label;
	}

	if ( wpnfinite_is_music_product( $product->get_id() ) ) {
		if ( $product->is_type( 'external' ) ) {
			return __( 'Get Beat', 'wpnfinite' );
		}

		/*
		 * Products that require selecting options/licenses should encourage the
		 * visitor into the PDP rather than implying an immediate cart add.
		 */
		if (
			$product->is_type( 'variable' ) ||
			! $product->is_purchasable()
		) {
			return __( 'View License', 'wpnfinite' );
		}

		return __( 'Get Beat', 'wpnfinite' );
	}

	return $label;
}
add_filter( 'woocommerce_product_add_to_cart_text', 'wpnfinite_product_archive_cta_label', 20, 2 );

/**
 * Music-product body/card classes.
 */
function wpnfinite_music_product_post_class( $classes, $class = '', $post_id = 0 ) {
	$post_id = absint( $post_id );

	if (
		$post_id &&
		'product' === get_post_type( $post_id ) &&
		wpnfinite_is_music_product( $post_id )
	) {
		$classes[] = 'wpnfinite-music-product';
	}

	return $classes;
}
add_filter( 'post_class', 'wpnfinite_music_product_post_class', 20, 3 );

/**
 * Add music class to the single product body when applicable.
 */
function wpnfinite_music_product_body_class( $classes ) {
	if ( function_exists( 'is_product' ) && is_product() ) {
		$product_id = get_queried_object_id();

		if ( $product_id && wpnfinite_is_music_product( $product_id ) ) {
			$classes[] = 'wpnfinite-music-product-page';
		}
	}

	return $classes;
}
add_filter( 'body_class', 'wpnfinite_music_product_body_class', 30 );

/**
 * Add a dedicated preview control to single music products.
 */
function wpnfinite_single_product_audio_preview() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$url = wpnfinite_get_product_preview_audio( $product->get_id() );

	if ( ! $url ) {
		return;
	}
	?>
	<button
		type="button"
		class="wpnfinite-single-preview wpnfinite-product-preview"
		data-wpnfinite-audio-preview="<?php echo esc_url( $url ); ?>"
		data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
		aria-label="<?php echo esc_attr( sprintf( __( 'Play preview for %s', 'wpnfinite' ), $product->get_name() ) ); ?>"
	>
		<span class="wpnfinite-product-preview__icon" aria-hidden="true">▶</span>
		<span class="wpnfinite-product-preview__label"><?php esc_html_e( 'Preview Beat', 'wpnfinite' ); ?></span>
	</button>
	<?php
}
// Native WPNfinite music PDP renders the single preview directly.

/**
 * Wrap the standard single-product cart form in a commerce-panel marker.
 */
function wpnfinite_single_product_purchase_note() {
	global $product;

	if ( ! $product instanceof WC_Product || ! wpnfinite_is_music_product( $product->get_id() ) ) {
		return;
	}
	?>
	<div class="wpnfinite-music-purchase-note">
		<span><?php esc_html_e( 'Secure checkout', 'wpnfinite' ); ?></span>
		<span><?php esc_html_e( 'Instant digital delivery when applicable', 'wpnfinite' ); ?></span>
	</div>
	<?php
}
// Native WPNfinite music PDP renders purchase reassurance directly.

/**
 * Render Play Preview on product archive cards when audio is available.
 */
function wpnfinite_product_audio_preview_button() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$url = wpnfinite_get_product_preview_audio( $product->get_id() );

	if ( ! $url ) {
		return;
	}
	?>
	<button
		type="button"
		class="wpnfinite-product-preview"
		data-wpnfinite-audio-preview="<?php echo esc_url( $url ); ?>"
		data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
		aria-label="<?php echo esc_attr( sprintf( __( 'Play preview for %s', 'wpnfinite' ), $product->get_name() ) ); ?>"
	>
		<span class="wpnfinite-product-preview__icon" aria-hidden="true">▶</span>
		<span class="wpnfinite-product-preview__label"><?php esc_html_e( 'Preview', 'wpnfinite' ); ?></span>
	</button>
	<?php
}

/**
 * Add music-preview controls to WooCommerce Product data > General.
 */
function wpnfinite_product_audio_admin_fields() {
	if ( ! function_exists( 'woocommerce_wp_text_input' ) ) {
		return;
	}

	echo '<div class="options_group wpnfinite-product-audio-options">';

	if ( function_exists( 'woocommerce_wp_select' ) ) {
		woocommerce_wp_select(
			array(
				'id'          => '_wpnfinite_music_product',
				'label'       => __( 'Music Product', 'wpnfinite' ),
				'description' => __( 'Auto detects from preview/category, or force the WPNfinite music-commerce templates on/off.', 'wpnfinite' ),
				'desc_tip'    => true,
				'options'     => array(
					''    => __( 'Auto Detect', 'wpnfinite' ),
					'yes' => __( 'Yes — Music Product', 'wpnfinite' ),
					'no'  => __( 'No — Standard Product', 'wpnfinite' ),
				),
			)
		);
	}

	woocommerce_wp_text_input(
		array(
			'id'          => '_wpnfinite_music_artist',
			'label'       => __( 'Artist / Producer', 'wpnfinite' ),
			'placeholder' => __( 'ZenCanFly', 'wpnfinite' ),
			'desc_tip'    => true,
			'description' => __( 'Optional creator/producer label for the music card and PDP. If blank, WPNfinite can infer it from a title formatted like "Track Name | Artist".', 'wpnfinite' ),
		)
	);

	woocommerce_wp_text_input(
		array(
			'id'          => '_wpnfinite_audio_preview',
			'label'       => __( 'Audio Preview', 'wpnfinite' ),
			'placeholder' => 'https://example.com/preview.mp3',
			'desc_tip'    => true,
			'description' => __( 'Public preview audio used on WPNfinite product cards. Use a short preview rather than a protected/full purchased master.', 'wpnfinite' ),
			'type'        => 'url',
		)
	);

	echo '<p class="form-field wpnfinite-audio-picker-row">
		<label></label>
		<button type="button" class="button wpnfinite-select-audio">' . esc_html__( 'Select Audio File', 'wpnfinite' ) . '</button>
		<button type="button" class="button-link-delete wpnfinite-clear-audio">' . esc_html__( 'Clear', 'wpnfinite' ) . '</button>
	</p>';

	if ( function_exists( 'woocommerce_wp_checkbox' ) ) {
		woocommerce_wp_checkbox(
			array(
				'id'          => '_wpnfinite_audio_download_fallback',
				'label'       => __( 'Download fallback', 'wpnfinite' ),
				'description' => __( 'If no dedicated preview is selected, allow WPNfinite to use the first downloadable audio file. Only enable this when that download is safe to expose publicly.', 'wpnfinite' ),
			)
		);
	}

	echo '</div>';
}
add_action( 'woocommerce_product_options_general_product_data', 'wpnfinite_product_audio_admin_fields' );

/**
 * Save product preview settings.
 */
function wpnfinite_save_product_audio_fields( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$preview = isset( $_POST['_wpnfinite_audio_preview'] )
		? esc_url_raw( wp_unslash( $_POST['_wpnfinite_audio_preview'] ) )
		: '';

	if ( $preview && ! wpnfinite_is_audio_preview_url( $preview ) ) {
		$preview = '';
	}

	$music_product = isset( $_POST['_wpnfinite_music_product'] )
		? sanitize_key( wp_unslash( $_POST['_wpnfinite_music_product'] ) )
		: '';

	if ( ! in_array( $music_product, array( '', 'yes', 'no' ), true ) ) {
		$music_product = '';
	}

	$music_artist = isset( $_POST['_wpnfinite_music_artist'] )
		? sanitize_text_field( wp_unslash( $_POST['_wpnfinite_music_artist'] ) )
		: '';

	$product->update_meta_data( '_wpnfinite_music_product', $music_product );
	$product->update_meta_data( '_wpnfinite_music_artist', $music_artist );

	$product->update_meta_data( '_wpnfinite_audio_preview', $preview );
	$product->update_meta_data(
		'_wpnfinite_audio_download_fallback',
		isset( $_POST['_wpnfinite_audio_download_fallback'] ) ? 'yes' : 'no'
	);
}
add_action( 'woocommerce_admin_process_product_object', 'wpnfinite_save_product_audio_fields' );

/**
 * Admin media-picker assets on Product edit screens only.
 */
function wpnfinite_product_audio_admin_assets( $hook_suffix ) {
	if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || 'product' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_media();

	wp_enqueue_script(
		'wpnfinite-product-audio-admin',
		WPNFINITE_URI . '/assets/js/woocommerce-product-audio-admin.js',
		array( 'jquery' ),
		WPNFINITE_VERSION,
		true
	);

	wp_localize_script(
		'wpnfinite-product-audio-admin',
		'WPNfiniteProductAudio',
		array(
			'title'  => __( 'Select Product Audio Preview', 'wpnfinite' ),
			'button' => __( 'Use this audio', 'wpnfinite' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'wpnfinite_product_audio_admin_assets' );

/**
 * Public archive-preview script only when WooCommerce pages are relevant.
 */
function wpnfinite_woocommerce_preview_assets() {
	if ( ! wpnfinite_has_woocommerce() ) {
		return;
	}

	if (
		! function_exists( 'is_woocommerce' ) ||
		! ( is_woocommerce() || is_cart() || is_checkout() )
	) {
		return;
	}

	wp_enqueue_script(
		'wpnfinite-woocommerce-preview',
		WPNFINITE_URI . '/assets/js/woocommerce-preview.js',
		array(),
		WPNFINITE_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'wpnfinite_woocommerce_preview_assets', 30 );

/**
 * Add a useful body class when an archive contains preview-capable products.
 */
function wpnfinite_woocommerce_body_classes( $classes ) {
	if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() ) ) {
		$classes[] = 'wpnfinite-product-archive';
	}

	if ( function_exists( 'is_product' ) && is_product() ) {
		$classes[] = 'wpnfinite-single-product';
	}

	return $classes;
}
add_filter( 'body_class', 'wpnfinite_woocommerce_body_classes' );

/**
 * Send customers to the cart after adding a product from a single-product page.
 *
 * WPNfinite product pages are purchase-focused and the next useful step is the
 * cart. Archive AJAX behavior is intentionally left unchanged.
 */
function wpnfinite_single_product_add_to_cart_redirect( $url ) {
	if ( is_admin() || wp_doing_ajax() || ! function_exists( 'wc_get_cart_url' ) ) {
		return $url;
	}

	if ( function_exists( 'is_product' ) && is_product() ) {
		return wc_get_cart_url();
	}

	return $url;
}
add_filter( 'woocommerce_add_to_cart_redirect', 'wpnfinite_single_product_add_to_cart_redirect', 20 );
