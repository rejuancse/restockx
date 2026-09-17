<?php
/**
 * Admin Create Campaign template
 *
 * @version 1.0.0
 */
defined( 'ABSPATH' ) || exit;
?>

<div id="restockx" class="restockx wrap alartx-sections">
	<?php include_once RESTOCKX_PATH .'/views/global/header.php'; ?>

    <section class="page page-campaigns">
        <div class="blur">
            <img src="<?php echo esc_url( RESTOCKX_URL . 'assets/images/create-cam.jpg' ); ?>" alt="">

            <?php if ( restockx_show_upgrade_cta() ) : ?>
                <a class="go-premium" href="https://contra.com/products/hgwSzl7o-restock-x-for-woo-commerce" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    Go Premium
                </a>
            <?php endif; ?>
        </div>
    </section>
</div>
