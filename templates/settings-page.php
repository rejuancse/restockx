<?php defined('ABSPATH') || exit; ?>

<div class="wrap stock-notification-settings">
    <h1><?php esc_html_e( 'AlertX', 'alertx' ); ?></h1>

    <!-- Settings form -->
    <form method="post">
        <?php wp_nonce_field( 'save_settings_action', 'settings_nonce' ); ?>
        <!-- Notification Threshold Section -->
        <div class="notification-threshold">
            <h2><?php esc_html_e( 'Notification Threshold', 'alertx' ); ?></h2>

            <!-- Input field for notification threshold -->
            <label for="notification_threshold">
                <?php esc_html_e( 'Notification Threshold:', 'alertx' ); ?>
            </label>
            <input type="number" id="notification_threshold" name="notification_threshold" value="<?php echo esc_attr( $threshold ); ?>" min="1">
            <p class="description">
                <?php esc_html_e( 'Send notifications when stock reaches or exceeds this number.', 'alertx' ); ?>
            </p>
        </div>

        <!-- Email Template Section -->
        <div class="template-guide-line">
            <h2><?php esc_html_e( 'Email Template', 'alertx' ); ?></h2>

            <!-- Email template customization guidelines -->
            <div class="guidelines">
                <p><?php esc_html_e( 'Customize the email sent to customers when a product is back in stock. You can use the following placeholders:', 'alertx' ); ?></p>
                <ul>
                    <li><code>{product_name}</code> - <?php esc_html_e( 'The name of the product', 'alertx' ); ?></li>
                    <li><code>{product_url}</code> - <?php esc_html_e( 'The URL of the product page', 'alertx' ); ?></li>
                    <li><code>{site_name}</code> - <?php esc_html_e( 'The name of your website', 'alertx' ); ?></li>
                </ul>
            </div>

            <!-- TinyMCE Editor for email template -->
            <div class="email-template">
                <?php
                    $editor_settings = array( // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                        'textarea_name' => 'email_templates',
                        'textarea_rows' => 20,
                        'media_buttons' => true,
                        'teeny'         => true,
                        'quicktags'     => true,
                    );
                    wp_editor($email_templates, 'email_templates_editor', $editor_settings);
                ?>
            </div>
        </div>

        <!-- Submit button to save settings -->
        <p class="submit">
            <input type="submit" name="submit_settings" class="button button-primary" value="<?php esc_attr_e( 'Save Settings', 'alertx' ); ?>">
        </p>
    </form>
</div>
