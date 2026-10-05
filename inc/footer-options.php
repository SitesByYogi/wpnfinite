<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checkbox sanitization for Customizer toggles.
 */
function wpnfinite_sanitize_checkbox( $value ) {
	return (bool) $value;
}

/**
 * Footer value helpers.
 */
function wpnfinite_footer_brand_description() {
	$value = trim( (string) get_theme_mod( 'wpnfinite_footer_brand_description', '' ) );

	return $value ?: get_bloginfo( 'description' );
}

function wpnfinite_footer_navigation_heading() {
	return get_theme_mod(
		'wpnfinite_footer_navigation_heading',
		__( 'Navigation', 'wpnfinite' )
	);
}

function wpnfinite_footer_show_navigation() {
	return (bool) get_theme_mod( 'wpnfinite_footer_show_navigation', true );
}

function wpnfinite_footer_info_heading() {
	return get_theme_mod(
		'wpnfinite_footer_info_heading',
		__( 'Built for publishing', 'wpnfinite' )
	);
}

function wpnfinite_footer_info_text() {
	return get_theme_mod(
		'wpnfinite_footer_info_text',
		__( 'WPNfinite is a content-first WordPress media framework designed to work natively with the Block Editor, ShopBlocks, Nfinite Dashboard, and WooCommerce.', 'wpnfinite' )
	);
}

function wpnfinite_footer_show_info() {
	return (bool) get_theme_mod( 'wpnfinite_footer_show_info', true );
}

function wpnfinite_footer_copyright() {
	$custom = trim( (string) get_theme_mod( 'wpnfinite_footer_copyright', '' ) );

	if ( $custom ) {
		return $custom;
	}

	return sprintf(
		'© %1$s %2$s',
		gmdate( 'Y' ),
		get_bloginfo( 'name' )
	);
}

function wpnfinite_footer_credit_text() {
	return get_theme_mod(
		'wpnfinite_footer_credit_text',
		__( 'Built with WPNfinite', 'wpnfinite' )
	);
}

function wpnfinite_footer_show_credit() {
	return (bool) get_theme_mod( 'wpnfinite_footer_show_credit', true );
}

/**
 * Register footer controls.
 */
function wpnfinite_footer_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'wpnfinite_footer_settings',
		array(
			'title'       => __( 'WPNfinite Footer', 'wpnfinite' ),
			'priority'    => 31,
			'description' => __( 'Customize the WPNfinite footer content and visibility without editing theme files. The footer menu itself is managed through Appearance → Menus.', 'wpnfinite' ),
		)
	);

	$wp_customize->add_setting(
		'wpnfinite_footer_brand_description',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'wpnfinite_footer_brand_description',
		array(
			'section'     => 'wpnfinite_footer_settings',
			'label'       => __( 'Brand Description', 'wpnfinite' ),
			'description' => __( 'Leave blank to use Settings → General → Tagline.', 'wpnfinite' ),
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting(
		'wpnfinite_footer_show_navigation',
		array(
			'default'           => true,
			'sanitize_callback' => 'wpnfinite_sanitize_checkbox',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'wpnfinite_footer_show_navigation',
		array(
			'section' => 'wpnfinite_footer_settings',
			'label'   => __( 'Show Footer Navigation', 'wpnfinite' ),
			'type'    => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'wpnfinite_footer_navigation_heading',
		array(
			'default'           => __( 'Navigation', 'wpnfinite' ),
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'wpnfinite_footer_navigation_heading',
		array(
			'section' => 'wpnfinite_footer_settings',
			'label'   => __( 'Navigation Heading', 'wpnfinite' ),
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'wpnfinite_footer_show_info',
		array(
			'default'           => true,
			'sanitize_callback' => 'wpnfinite_sanitize_checkbox',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'wpnfinite_footer_show_info',
		array(
			'section' => 'wpnfinite_footer_settings',
			'label'   => __( 'Show Information Column', 'wpnfinite' ),
			'type'    => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'wpnfinite_footer_info_heading',
		array(
			'default'           => __( 'Built for publishing', 'wpnfinite' ),
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'wpnfinite_footer_info_heading',
		array(
			'section'     => 'wpnfinite_footer_settings',
			'label'       => __( 'Information Heading', 'wpnfinite' ),
			'description' => __( 'Example: PairOfDice Media, About Us, Built for Publishing.', 'wpnfinite' ),
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting(
		'wpnfinite_footer_info_text',
		array(
			'default'           => __( 'WPNfinite is a content-first WordPress media framework designed to work natively with the Block Editor, ShopBlocks, Nfinite Dashboard, and WooCommerce.', 'wpnfinite' ),
			'sanitize_callback' => 'sanitize_textarea_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'wpnfinite_footer_info_text',
		array(
			'section' => 'wpnfinite_footer_settings',
			'label'   => __( 'Information Text', 'wpnfinite' ),
			'type'    => 'textarea',
		)
	);

	$wp_customize->add_setting(
		'wpnfinite_footer_copyright',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'wpnfinite_footer_copyright',
		array(
			'section'     => 'wpnfinite_footer_settings',
			'label'       => __( 'Copyright Text', 'wpnfinite' ),
			'description' => __( 'Leave blank to automatically display the current year and Site Title.', 'wpnfinite' ),
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting(
		'wpnfinite_footer_show_credit',
		array(
			'default'           => true,
			'sanitize_callback' => 'wpnfinite_sanitize_checkbox',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'wpnfinite_footer_show_credit',
		array(
			'section' => 'wpnfinite_footer_settings',
			'label'   => __( 'Show WPNfinite Credit', 'wpnfinite' ),
			'type'    => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'wpnfinite_footer_credit_text',
		array(
			'default'           => __( 'Built with WPNfinite', 'wpnfinite' ),
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'wpnfinite_footer_credit_text',
		array(
			'section'     => 'wpnfinite_footer_settings',
			'label'       => __( 'Footer Credit Text', 'wpnfinite' ),
			'description' => __( 'Used only when Show WPNfinite Credit is enabled.', 'wpnfinite' ),
			'type'        => 'text',
		)
	);
}
add_action( 'customize_register', 'wpnfinite_footer_customize_register' );
