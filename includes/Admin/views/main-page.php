<?php
/**
 * Main Dashboard Page View
 *
 * @package AlertX
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap alertx-dashboard">
    <h1><?php _e('AlertX Dashboard', 'alertx'); ?></h1>

    <div class="alertx-header">
        <button type="button" class="button button-primary alertx-refresh-btn">
            <span class="dashicons dashicons-update"></span> <?php _e('Refresh All Data', 'alertx'); ?>
        </button>
    </div>

    <div class="alertx-grid">

        <!-- System Health Section -->
        <div class="alertx-card">
            <div class="alertx-card-header">
                <h2><?php _e('System Health', 'alertx'); ?></h2>
            </div>
            <div class="alertx-card-body">

                <!-- Memory Usage -->
                <div class="alertx-metric">
                    <div class="alertx-metric-icon" style="background-color: <?php echo AlertX_Notifications::get_status_color($system_data['memory']['status']); ?>">
                        <span class="dashicons dashicons-dashboard"></span>
                    </div>
                    <div class="alertx-metric-content">
                        <h3><?php _e('Memory Usage', 'alertx'); ?></h3>
                        <div class="alertx-progress-bar">
                            <div class="alertx-progress-fill" style="width: <?php echo $system_data['memory']['percentage']; ?>%; background-color: <?php echo AlertX_Notifications::get_status_color($system_data['memory']['status']); ?>"></div>
                        </div>
                        <p>
                            <strong><?php echo $system_data['memory']['percentage']; ?>%</strong> -
                            <?php echo $system_data['memory']['current_formatted']; ?> / <?php echo $system_data['memory']['limit']; ?>
                        </p>
                    </div>
                </div>

                <!-- Disk Space -->
                <?php if (isset($system_data['disk']['status'])): ?>
                <div class="alertx-metric">
                    <div class="alertx-metric-icon" style="background-color: <?php echo AlertX_Notifications::get_status_color($system_data['disk']['status']); ?>">
                        <span class="dashicons dashicons-media-default"></span>
                    </div>
                    <div class="alertx-metric-content">
                        <h3><?php _e('Disk Space', 'alertx'); ?></h3>
                        <div class="alertx-progress-bar">
                            <div class="alertx-progress-fill" style="width: <?php echo $system_data['disk']['percentage']; ?>%; background-color: <?php echo AlertX_Notifications::get_status_color($system_data['disk']['status']); ?>"></div>
                        </div>
                        <p>
                            <strong><?php echo $system_data['disk']['percentage']; ?>%</strong> -
                            <?php echo $system_data['disk']['used_formatted']; ?> / <?php echo $system_data['disk']['total_formatted']; ?>
                        </p>
                        <p class="alertx-small-text">
                            <?php printf(__('Free: %s', 'alertx'), $system_data['disk']['free_formatted']); ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Database -->
                <div class="alertx-metric">
                    <div class="alertx-metric-icon" style="background-color: <?php echo AlertX_Notifications::get_status_color($system_data['database']['status']); ?>">
                        <span class="dashicons dashicons-database"></span>
                    </div>
                    <div class="alertx-metric-content">
                        <h3><?php _e('Database Size', 'alertx'); ?></h3>
                        <p>
                            <strong><?php echo $system_data['database']['total_size']; ?> MB</strong> -
                            <?php printf(_n('%d table', '%d tables', $system_data['database']['table_count'], 'alertx'), $system_data['database']['table_count']); ?>
                        </p>
                        <?php if (!empty($system_data['database']['top_tables'])): ?>
                        <details class="alertx-details">
                            <summary><?php _e('Top 5 Largest Tables', 'alertx'); ?></summary>
                            <ul class="alertx-table-list">
                                <?php foreach ($system_data['database']['top_tables'] as $table): ?>
                                <li>
                                    <span class="table-name"><?php echo esc_html($table['name']); ?></span>
                                    <span class="table-size"><?php echo $table['size']; ?> MB</span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </details>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Section -->
        <div class="alertx-card">
            <div class="alertx-card-header">
                <h2><?php _e('Security Status', 'alertx'); ?></h2>
            </div>
            <div class="alertx-card-body">

                <!-- WordPress Update -->
                <div class="alertx-metric">
                    <div class="alertx-metric-icon" style="background-color: <?php echo AlertX_Notifications::get_status_color($security_data['wp_update']['status']); ?>">
                        <span class="dashicons dashicons-wordpress"></span>
                    </div>
                    <div class="alertx-metric-content">
                        <h3><?php _e('WordPress Core', 'alertx'); ?></h3>
                        <p><?php echo esc_html($security_data['wp_update']['message']); ?></p>
                        <p class="alertx-small-text">
                            <?php printf(__('Current Version: %s', 'alertx'), $security_data['wp_update']['current_version']); ?>
                        </p>
                    </div>
                </div>

                <!-- Admin Users -->
                <div class="alertx-metric">
                    <div class="alertx-metric-icon" style="background-color: <?php echo AlertX_Notifications::get_status_color($security_data['admins']['status']); ?>">
                        <span class="dashicons dashicons-admin-users"></span>
                    </div>
                    <div class="alertx-metric-content">
                        <h3><?php _e('Administrator Accounts', 'alertx'); ?></h3>
                        <p>
                            <strong><?php echo $security_data['admins']['count']; ?></strong>
                            <?php echo _n('admin user', 'admin users', $security_data['admins']['count'], 'alertx'); ?>
                        </p>
                        <?php if (!empty($security_data['admins']['admins'])): ?>
                        <details class="alertx-details">
                            <summary><?php _e('View Admin Users', 'alertx'); ?></summary>
                            <ul class="alertx-admin-list">
                                <?php foreach ($security_data['admins']['admins'] as $admin): ?>
                                <li>
                                    <strong><?php echo esc_html($admin['username']); ?></strong>
                                    <span class="alertx-small-text"><?php echo esc_html($admin['email']); ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </details>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Plugin Updates -->
                <div class="alertx-metric">
                    <div class="alertx-metric-icon" style="background-color: <?php echo AlertX_Notifications::get_status_color($security_data['plugin_updates']['status']); ?>">
                        <span class="dashicons dashicons-admin-plugins"></span>
                    </div>
                    <div class="alertx-metric-content">
                        <h3><?php _e('Plugin Updates', 'alertx'); ?></h3>
                        <p><?php echo esc_html($security_data['plugin_updates']['message']); ?></p>
                    </div>
                </div>

                <!-- Theme Updates -->
                <div class="alertx-metric">
                    <div class="alertx-metric-icon" style="background-color: <?php echo AlertX_Notifications::get_status_color($security_data['theme_updates']['status']); ?>">
                        <span class="dashicons dashicons-admin-appearance"></span>
                    </div>
                    <div class="alertx-metric-content">
                        <h3><?php _e('Theme Updates', 'alertx'); ?></h3>
                        <p><?php echo esc_html($security_data['theme_updates']['message']); ?></p>
                    </div>
                </div>

            </div>
        </div>

        <!-- WooCommerce Section -->
        <?php if ($woocommerce_data['active']): ?>
            <div class="alertx-card alertx-card-full">
                <div class="alertx-card-header">
                    <h2><?php _e('WooCommerce Status', 'alertx'); ?></h2>
                </div>
                <div class="alertx-card-body">

                    <div class="alertx-woo-grid">
                        <!-- Out of Stock -->
                        <div class="alertx-metric">
                            <div class="alertx-metric-icon" style="background-color: <?php echo AlertX_Notifications::get_status_color($woocommerce_data['out_of_stock']['status']); ?>">
                                <span class="dashicons dashicons-products"></span>
                            </div>
                            <div class="alertx-metric-content">
                                <h3><?php _e('Out of Stock Products', 'alertx'); ?></h3>
                                <p>
                                    <strong><?php echo $woocommerce_data['out_of_stock']['count']; ?></strong>
                                    <?php echo _n('product', 'products', $woocommerce_data['out_of_stock']['count'], 'alertx'); ?>
                                </p>
                                <?php if ($woocommerce_data['out_of_stock']['count'] > 0 && !empty($woocommerce_data['out_of_stock']['products'])): ?>
                                <details class="alertx-details">
                                    <summary><?php _e('View Products', 'alertx'); ?></summary>
                                    <ul class="alertx-product-list">
                                        <?php foreach ($woocommerce_data['out_of_stock']['products'] as $product): ?>
                                        <li>
                                            <a href="<?php echo esc_url($product['edit_link']); ?>" target="_blank">
                                                <?php echo esc_html($product['title']); ?>
                                            </a>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </details>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Low Stock -->
                        <div class="alertx-metric">
                            <div class="alertx-metric-icon" style="background-color: <?php echo AlertX_Notifications::get_status_color($woocommerce_data['low_stock']['status']); ?>">
                                <span class="dashicons dashicons-warning"></span>
                            </div>
                            <div class="alertx-metric-content">
                                <h3><?php _e('Low Stock Products', 'alertx'); ?></h3>
                                <p>
                                    <strong><?php echo $woocommerce_data['low_stock']['count']; ?></strong>
                                    <?php echo _n('product', 'products', $woocommerce_data['low_stock']['count'], 'alertx'); ?>
                                    <?php printf(__('(threshold: %d)', 'alertx'), $woocommerce_data['low_stock']['threshold']); ?>
                                </p>
                                <?php if ($woocommerce_data['low_stock']['count'] > 0 && !empty($woocommerce_data['low_stock']['products'])): ?>
                                <details class="alertx-details">
                                    <summary><?php _e('View Products', 'alertx'); ?></summary>
                                    <ul class="alertx-product-list">
                                        <?php foreach ($woocommerce_data['low_stock']['products'] as $product): ?>
                                        <li>
                                            <a href="<?php echo esc_url($product['edit_link']); ?>" target="_blank">
                                                <?php echo esc_html($product['title']); ?>
                                                <span class="stock-count">(<?php echo $product['stock']; ?>)</span>
                                            </a>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </details>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- Pro Features Banner -->
    <div class="alertx-pro-banner">
        <h3><?php _e('🚀 Upgrade to AlertX Pro', 'alertx'); ?></h3>
        <p><?php _e('Get advanced features including:', 'alertx'); ?></p>
        <ul>
            <li>✉️ <?php _e('Email notifications for critical alerts', 'alertx'); ?></li>
            <li>🚀 <?php _e('Website speed monitoring with PageSpeed integration', 'alertx'); ?></li>
            <li>🦠 <?php _e('Malware scanning and security monitoring', 'alertx'); ?></li>
            <li>👤 <?php _e('User login tracking and brute force detection', 'alertx'); ?></li>
            <li>📈 <?php _e('Historical data and detailed logs', 'alertx'); ?></li>
            <li>⚙️ <?php _e('Customizable alert thresholds', 'alertx'); ?></li>
            <li>📱 <?php _e('Webhook integrations (Slack, Telegram)', 'alertx'); ?></li>
        </ul>
        <a href="#" class="button button-primary button-hero"><?php _e('Upgrade Now', 'alertx'); ?></a>
    </div>

</div>
