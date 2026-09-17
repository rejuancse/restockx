<?php
/**
 * Admin Templates template
 *
 * @version 1.0.0
 */
defined( 'ABSPATH' ) || exit;
?>

<div class="restockx wrap alartx-sections">
    <?php include_once RESTOCKX_PATH .'/views/global/header.php'; ?>

    <section class="page">
        <div class="page-header">
			<div>
				<div class="page-h1">
                    <?php esc_html_e( 'Email Template', 'restockx' ); ?>
                </div>
				<div class="page-desc">
					<?php esc_html_e( 'Configure the email your customers receive when a product comes back in stock.', 'restockx' ); ?>
				</div>
			</div>

			</div>

        <form method="post">
            <?php wp_nonce_field( 'restockx_save_settings', 'settings_nonce' ); ?>

            <div class="grid-2">
                <!-- Email Template Section -->
                <div class="template-guide-line">
                    <div class="page-h1">
                        <?php esc_html_e( 'Configure Email Template', 'restockx' ); ?>
                    </div>
                    <div class="page-desc">
                        <?php esc_html_e( 'Customize the email sent to customers when a product is back in stock. You can use the following placeholders:', 'restockx' ); ?>
                    </div>

                    <!-- Email template customization guidelines -->
                    <div class="guidelines">
                        <ul>
                            <li><code class="tag-chip">{product_name}</code> - <?php esc_html_e( 'The name of the product', 'restockx' ); ?></li>
                            <li><code class="tag-chip">{product_url}</code> - <?php esc_html_e( 'The URL of the product page', 'restockx' ); ?></li>
                            <li><code class="tag-chip">{site_name}</code> - <?php esc_html_e( 'The name of your website', 'restockx' ); ?></li>
                        </ul>
                    </div>

                    <!-- TinyMCE Editor for email template -->
                    <div class="email-template">
                        <?php
                            $editor_settings = array( // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                                'textarea_name' => 'email_templates',
                                'textarea_rows' => 27,
                                'media_buttons' => true,
                                'teeny'         => true,
                                'quicktags'     => true,
                            );
                            wp_editor($email_templates, 'email_templates_editor', $editor_settings);
                        ?>
                    </div>
                </div>

                <!-- Notification Threshold Section -->
                <div class="notification-threshold">
                    <div class="template-grid">
                        <div class="page-h1">
                            <?php esc_html_e( 'Notification Threshold', 'restockx' ); ?>
                        </div>

                        <!-- Input field for notification threshold -->
                        <div class="field">
                            <label>
                                <?php esc_html_e( 'Notifications go out once stock reaches or exceeds this number.', 'restockx' ); ?>
                                <span class="required">*</span>
                            </label>

                            <input
                                type="number"
                                id="notification_threshold"
                                name="notification_threshold"
                                value="<?php echo esc_attr( $threshold ); ?>"
                                min="1">

                            <p class="description">
                                <?php esc_html_e( 'When stock reaches this many units, everyone on the waitlist gets emailed at once.', 'restockx' ); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Email Settings -->
                    <div class="template-grid">
                        <div class="section-title">
                            <?php esc_html_e( 'Email Settings', 'restockx' ); ?>
                        </div>

                        <div class="template-card">
                            <div class="template-thumb">
                                <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                                    <path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Z" />
                                    <path d="m22 6-10 7L2 6" />
                                </svg>
                            </div>

                            <div class="template-meta">
                                <div class="tname">
                                    <?php esc_html_e( 'Subscription Confirmation', 'restockx' ); ?>
                                </div>
                                <div class="tdesc">
                                    <?php esc_html_e( 'Confirms a customer has joined the waitlist', 'restockx' ); ?>
                                </div>
                            </div>

                            <div class="pro-toggle-wrap">
                                <label class="switch-toggle">
                                    <?php $restockx_lock_premium = restockx_show_upgrade_cta(); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
                                    <input
                                        type="checkbox"
                                        name="require_confirmation"
                                        value="1"
                                        <?php checked( get_option( 'restockx_require_confirmation', '0' ), '1' ); ?>
                                        <?php disabled( $restockx_lock_premium ); ?>>
                                    <span class="slider round"></span>
                                </label>
                                <?php if ( $restockx_lock_premium ) : ?>
                                    <span class="pro-lock">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="11" width="18" height="11" rx="2" />
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                        </svg>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if ( restockx_show_upgrade_cta() ) : ?>
                        <a class="go-premium" href="https://example.com/upgrade" target="_blank" rel="noopener">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                            <?php esc_html_e( 'Go Premium', 'restockx' ); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Submit button to save settings -->
            <p class="submit">
                <input type="submit" name="submit_settings" class="btn btn-primary" value="<?php esc_attr_e( 'Save Settings', 'restockx' ); ?>">
            </p>
        </form>
    </section>
</div>
