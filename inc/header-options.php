<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wpnfinite_header_cta_label() {
    return get_theme_mod(
        'wpnfinite_header_cta_label',
        __( 'Get Started', 'wpnfinite' )
    );
}

function wpnfinite_header_cta_url() {
    $url = get_theme_mod( 'wpnfinite_header_cta_url', '' );

    if ( ! $url ) {
        $url = home_url( '/contact/' );
    }

    return $url;
}

function wpnfinite_header_customize_register( $wp_customize ) {
    $wp_customize->add_section(
        'wpnfinite_header_settings',
        array(
            'title'       => __( 'WPNfinite Header', 'wpnfinite' ),
            'priority'    => 30,
            'description' => __( 'Configure the primary call-to-action displayed in the site header.', 'wpnfinite' ),
        )
    );

    $wp_customize->add_setting(
        'wpnfinite_header_cta_label',
        array(
            'default'           => __( 'Get Started', 'wpnfinite' ),
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        )
    );

    $wp_customize->add_control(
        'wpnfinite_header_cta_label',
        array(
            'section'     => 'wpnfinite_header_settings',
            'label'       => __( 'Header CTA Label', 'wpnfinite' ),
            'description' => __( 'Example: Join PairOfDice, Get Started, Contact Us.', 'wpnfinite' ),
            'type'        => 'text',
        )
    );

    $wp_customize->add_setting(
        'wpnfinite_header_cta_url',
        array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
            'transport'         => 'refresh',
        )
    );

    $wp_customize->add_control(
        'wpnfinite_header_cta_url',
        array(
            'section'     => 'wpnfinite_header_settings',
            'label'       => __( 'Header CTA URL', 'wpnfinite' ),
            'description' => sprintf(
                __( 'Leave blank to use the default: %s', 'wpnfinite' ),
                home_url( '/contact/' )
            ),
            'type'        => 'url',
        )
    );
}
add_action( 'customize_register', 'wpnfinite_header_customize_register' );

/**
 * Account-aware header actions.
 *
 * The public header should reflect session state instead of presenting a
 * generic sales CTA. Nfinite Creators owns the creator auth destinations when
 * available; WordPress provides safe fallbacks.
 */
function wpnfinite_account_login_url() {
    return 'https://ci.pairofdice.media/';
}

function wpnfinite_account_join_url() {
    return 'https://ci.pairofdice.media/?signup=1';
}

function wpnfinite_account_url() {
    return 'https://ci.pairofdice.media/';
}

function wpnfinite_render_account_header_action() {
    if ( is_user_logged_in() ) {
        $user       = wp_get_current_user();
        $account    = wpnfinite_account_url();
        $logout_url = wp_logout_url( home_url( '/' ) );
        ?>
        <details class="wpnfinite-account-menu">
            <summary class="wpnfinite-btn wpnfinite-btn-primary wpnfinite-account-trigger">
                <span><?php esc_html_e( 'Account', 'wpnfinite' ); ?></span>
                <span class="wpnfinite-account-chevron" aria-hidden="true">⌄</span>
            </summary>
            <div class="wpnfinite-account-dropdown">
                <div class="wpnfinite-account-user">
                    <strong><?php echo esc_html( $user->display_name ?: $user->user_login ); ?></strong>
                    <?php if ( $user->user_email ) : ?><span><?php echo esc_html( $user->user_email ); ?></span><?php endif; ?>
                </div>
                <a href="<?php echo esc_url( $account ); ?>"><?php esc_html_e( 'Creator Intelligence', 'wpnfinite' ); ?></a>
                <a class="wpnfinite-account-signout" href="<?php echo esc_url( $logout_url ); ?>"><?php esc_html_e( 'Sign Out', 'wpnfinite' ); ?></a>
            </div>
        </details>
        <?php
        return;
    }

    $join_url  = wpnfinite_account_join_url();
    $login_url = wpnfinite_account_login_url();
    ?>
    <details class="wpnfinite-account-menu wpnfinite-account-menu--guest">
        <summary class="wpnfinite-btn wpnfinite-btn-primary wpnfinite-account-trigger">
            <span><?php esc_html_e( 'Join / Sign In', 'wpnfinite' ); ?></span>
            <span class="wpnfinite-account-chevron" aria-hidden="true">⌄</span>
        </summary>
        <div class="wpnfinite-account-dropdown">
            <a class="wpnfinite-account-join" href="<?php echo esc_url( $join_url ); ?>"><?php esc_html_e( 'Join PairOfDice', 'wpnfinite' ); ?></a>
            <a href="<?php echo esc_url( $login_url ); ?>"><?php esc_html_e( 'Sign In', 'wpnfinite' ); ?></a>
        </div>
    </details>
    <?php
}
