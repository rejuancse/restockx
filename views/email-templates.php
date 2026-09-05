<?php
/**
 * Admin Templates template
 *
 * @version 1.0.0
 */
defined( 'ABSPATH' ) || exit;
?>

<div class="alertx wrap alartx-sections">
    <?php include_once ALERTX_PATH .'/views/global/header.php'; ?>

    <section class="page">
        <div class="page-header">
			<div>
				<div class="page-h1">
                    <?php esc_html_e( 'Email Template', 'alertx' ); ?>
                </div>
				<div class="page-desc">
					<?php esc_html_e( 'Configure the email your customers receive when a product comes back in stock.', 'alertx' ); ?>
				</div>
			</div>

			</div>

        <form method="post">
            <?php wp_nonce_field( 'save_settings_action', 'settings_nonce' ); ?>

            <div class="grid-2">
                <!-- Email Template Section -->
                <div class="template-guide-line">
                    <div class="page-h1">
                        <?php esc_html_e( 'Configure Email Template', 'alertx' ); ?>
                    </div>
                    <div class="page-desc">
                        <?php esc_html_e( 'Customize the email sent to customers when a product is back in stock. You can use the following placeholders:', 'alertx' ); ?>
                    </div>

                    <!-- Email template customization guidelines -->
                    <div class="guidelines">
                        <ul>
                            <li><code class="tag-chip">{product_name}</code> - <?php esc_html_e( 'The name of the product', 'alertx' ); ?></li>
                            <li><code class="tag-chip">{product_url}</code> - <?php esc_html_e( 'The URL of the product page', 'alertx' ); ?></li>
                            <li><code class="tag-chip">{site_name}</code> - <?php esc_html_e( 'The name of your website', 'alertx' ); ?></li>
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
                            <?php esc_html_e( 'Notification Threshold', 'alertx' ); ?>
                        </div>

                        <!-- Input field for notification threshold -->
                        <div class="field">
                            <label>
                                <?php esc_html_e( 'Notifications go out once stock reaches or exceeds this number.', 'alertx' ); ?>
                                <span class="required">*</span>
                            </label>

                            <input
                                type="number"
                                id="notification_threshold"
                                name="notification_threshold"
                                value="<?php echo esc_attr( $threshold ); ?>"
                                min="1">

                            <p class="description">
                                <?php esc_html_e( 'When stock reaches this many units, everyone on the waitlist gets emailed at once.', 'alertx' ); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Email Settings -->
                    <div class="template-grid">
                        <div class="section-title">
                            <?php esc_html_e( 'Email Settings', 'alertx' ); ?>
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
                                    <?php esc_html_e( 'Subscription Confirmation', 'alertx' ); ?>
                                </div>
                                <div class="tdesc">
                                    <?php esc_html_e( 'Confirms a customer has joined the waitlist', 'alertx' ); ?>
                                </div>
                            </div>

                            <label class="switch-toggle">
                                <input type="checkbox" name="require_confirmation" value="1" <?php checked( get_option( 'alertx_require_confirmation', '1' ), '1' ); ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit button to save settings -->
            <p class="submit">
                <input type="submit" name="submit_settings" class="btn btn-primary" value="<?php esc_attr_e( 'Save Settings', 'alertx' ); ?>">
            </p>
        </form>
    </section>
</div>
