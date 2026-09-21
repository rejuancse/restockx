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
        <?php if ( restockx_show_upgrade_cta() ) : ?>
            <a class="go-premium go-premium-sm" href="https://contra.com/products/hgwSzl7o-restock-x-for-woo-commerce" target="_blank" rel="noopener">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <?php esc_html_e('Go Premium', 'restockx'); ?>
            </a>
        <?php endif; ?>

        <span>
            <?php
                // translators: %s: plugin version number
                $restockx_version_text = esc_html__( 'Current Version: %s', 'restockx' );
                echo wp_kses_post( sprintf( $restockx_version_text, '<strong>' . esc_html( RESTOCKX_VERSION ) . '</strong>' ) );
            ?>
        </span>
    </div>
</header>
