<?php
/**
 * Admin Header template
 *
 * @version 1.0.0
 */
defined( 'ABSPATH' ) || exit;
?>

<header class="alertx-settings-header">
    <div class="alertx-header-left">
        <div class="alertx-admin-header">
            <img src="<?php echo esc_url( ALERTX_URL . 'assets/images/logo.png' ); ?>" alt="">
            <sub><?php esc_html_e('Pro', 'alertx-pro'); ?></sub>
        </div>
    </div>

    <div class="alertx-header-right">
        <button class="icon-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
                <path d="M13.7 21a2 2 0 0 1-3.4 0" />
            </svg>
            <span class="dot"></span>
        </button>

        <span>
            <?php
                // translators: %s: plugin version number
                $alertx_version_text = esc_html__( 'Current Version: %s', 'alertx-pro' );
                echo wp_kses_post( sprintf( $alertx_version_text, '<strong>' . esc_html( ALERTX_VERSION ) . '</strong>' ) );
            ?>
        </span>
    </div>
</header>
