<?php
/**
 * Settings Page View
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('alertx_settings', array());
?>

<div class="wrap alertx-settings">
    <h1><?php _e('AlertX Settings', 'alertx'); ?></h1>

    <?php if (isset($_GET['settings-updated'])): ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e('Settings saved successfully!', 'alertx'); ?></p>
    </div>
    <?php endif; ?>

    <form method="post" action="options.php">
        <?php settings_fields('alertx_settings_group'); ?>

        <div class="alertx-settings-grid">

            <!-- System Health Alerts -->
            <div class="alertx-settings-card">
                <h2><?php _e('System Health Alerts', 'alertx'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Memory Usage Alert', 'alertx'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="alertx_settings[memory_alert]" value="yes"
                                    <?php checked(isset($settings['memory_alert']) && $settings['memory_alert'] === 'yes'); ?> />
                                <?php _e('Enable memory usage monitoring (Alert at 80%)', 'alertx'); ?>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Storage Space Alert', 'alertx'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="alertx_settings[storage_alert]" value="yes"
                                    <?php checked(isset($settings['storage_alert']) && $settings['storage_alert'] === 'yes'); ?> />
                                <?php _e('Enable disk space monitoring', 'alertx'); ?>
                            </label>
                            <p class="description">
                                <?php _e('Threshold:', 'alertx'); ?>
                                <input type="number" name="alertx_settings[storage_threshold]"
                                    value="<?php echo isset($settings['storage_threshold']) ? esc_attr($settings['storage_threshold']) : 75; ?>"
                                    min="50" max="95" style="width: 60px;" /> %
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Database Size Alert', 'alertx'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="alertx_settings[database_alert]" value="yes"
                                    <?php checked(isset($settings['database_alert']) && $settings['database_alert'] === 'yes'); ?> />
                                <?php _e('Enable database size monitoring', 'alertx'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Security Alerts -->
            <div class="alertx-settings-card">
                <h2><?php _e('Security Alerts', 'alertx'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Admin Count Alert', 'alertx'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="alertx_settings[admin_alert]" value="yes"
                                    <?php checked(isset($settings['admin_alert']) && $settings['admin_alert'] === 'yes'); ?> />
                                <?php _e('Alert when more than 3 admin accounts exist', 'alertx'); ?>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('WordPress Update Alert', 'alertx'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="alertx_settings[update_alert]" value="yes"
                                    <?php checked(isset($settings['update_alert']) && $settings['update_alert'] === 'yes'); ?> />
                                <?php _e('Alert when WordPress core update is available', 'alertx'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- WooCommerce Alerts -->
            <?php if (class_exists('WooCommerce')): ?>
            <div class="alertx-settings-card">
                <h2><?php _e('WooCommerce Alerts', 'alertx'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Stock Out Alert', 'alertx'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="alertx_settings[stock_alert]" value="yes"
                                    <?php checked(isset($settings['stock_alert']) && $settings['stock_alert'] === 'yes'); ?> />
                                <?php _e('Alert when products are out of stock', 'alertx'); ?>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Low Stock Alert', 'alertx'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="alertx_settings[low_stock_alert]" value="yes"
                                    <?php checked(isset($settings['low_stock_alert']) && $settings['low_stock_alert'] === 'yes'); ?> />
                                <?php _e('Alert when product stock is low', 'alertx'); ?>
                            </label>
                            <p class="description">
                                <?php _e('Threshold:', 'alertx'); ?>
                                <input type="number" name="alertx_settings[low_stock_threshold]"
                                    value="<?php echo isset($settings['low_stock_threshold']) ? esc_attr($settings['low_stock_threshold']) : 5; ?>"
                                    min="1" max="20" style="width: 60px;" />
                                <?php _e('items or less', 'alertx'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            <?php else: ?>
            <div class="alertx-settings-card">
                <h2><?php _e('WooCommerce Alerts', 'alertx'); ?></h2>
                <p class="description">
                    <?php _e('WooCommerce is not installed or activated. Install WooCommerce to enable stock alerts.', 'alertx'); ?>
                </p>
            </div>
            <?php endif; ?>

        </div>

        <?php submit_button(__('Save Settings', 'alertx'), 'primary large'); ?>
    </form>

    <!-- Pro Features Info -->
    <div class="alertx-pro-info">
        <h3><?php _e('🎯 Want More Control?', 'alertx'); ?></h3>
        <p><?php _e('Upgrade to Pro to unlock:', 'alertx'); ?></p>
        <ul>
            <li><?php _e('Customizable alert thresholds for all metrics', 'alertx'); ?></li>
            <li><?php _e('Email notifications with custom recipients', 'alertx'); ?></li>
            <li><?php _e('Webhook integrations for Slack, Discord, Telegram', 'alertx'); ?></li>
            <li><?php _e('Schedule automated reports', 'alertx'); ?></li>
            <li><?php _e('White-label options for agencies', 'alertx'); ?></li>
        </ul>
        <a href="#" class="button button-primary"><?php _e('Learn More', 'alertx'); ?></a>
    </div>

</div>