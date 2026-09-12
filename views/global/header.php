<?php
/**
 * Admin Header template
 *
 * @version 1.0.0
 */
defined( 'ABSPATH' ) || exit;
?>

<header class="restockx-settings-header">
    <div class="restockx-header-left">
        <div class="restockx-admin-header">
            <img src="<?php echo esc_url( RESTOCKX_URL . 'assets/images/logo.png' ); ?>" alt="">
        </div>
    </div>

    <div class="restockx-header-right">
        <span>
            <?php
                // translators: %s: plugin version number
                $restockx_version_text = esc_html__( 'Current Version: %s', 'restockx' );
                echo wp_kses_post( sprintf( $restockx_version_text, '<strong>' . esc_html( RESTOCKX_VERSION ) . '</strong>' ) );
            ?>
        </span>
    </div>
</header>
