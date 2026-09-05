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
        </div>
    </div>

    <div class="alertx-header-right">
        <span>
            <?php
                // translators: %s: plugin version number
                $alertx_version_text = esc_html__( 'Current Version: %s', 'alertx' );
                echo wp_kses_post( sprintf( $alertx_version_text, '<strong>' . esc_html( ALERTX_VERSION ) . '</strong>' ) );
            ?>
        </span>
    </div>
</header>
