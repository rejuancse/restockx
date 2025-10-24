<?php
/**
 * Notifications Class
 *
 * Handles all notification display and status indicators
 *
 * @package AlertX
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class AlertX_Notifications {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_notices', array($this, 'display_admin_notices'));
    }

    /**
     * Display admin notices
     */
    public function display_admin_notices() {
        $settings = get_option('alertx_settings');

        // Check if alerts are enabled
        if (!isset($settings['memory_alert']) && !isset($settings['storage_alert'])) {
            return;
        }

        $alerts = $this->get_critical_alerts();

        if (empty($alerts)) {
            return;
        }

        foreach ($alerts as $alert) {
            $this->display_notice($alert);
        }
    }

    /**
     * Get critical alerts
     *
     * @return array List of critical alerts
     */
    private function get_critical_alerts() {
        $settings = get_option('alertx_settings');
        $alerts = array();

        // Memory alert
        if (isset($settings['memory_alert']) && $settings['memory_alert'] === 'yes') {
            $memory = AlertX_System_Monitor::get_memory_usage();
            if ($memory['status'] === 'critical') {
                $alerts[] = array(
                    'type' => 'error',
                    'message' => sprintf(
                        __('Memory Usage Critical: %s%% of %s used', 'alertx'),
                        $memory['percentage'],
                        $memory['limit']
                    )
                );
            }
        }

        // Storage alert
        if (isset($settings['storage_alert']) && $settings['storage_alert'] === 'yes') {
            $disk = AlertX_System_Monitor::get_disk_usage();
            if (isset($disk['status']) && $disk['status'] === 'critical') {
                $alerts[] = array(
                    'type' => 'error',
                    'message' => sprintf(
                        __('Disk Space Critical: %s%% used (%s of %s)', 'alertx'),
                        $disk['percentage'],
                        $disk['used_formatted'],
                        $disk['total_formatted']
                    )
                );
            }
        }

        // WordPress update alert
        if (isset($settings['update_alert']) && $settings['update_alert'] === 'yes') {
            $wp_update = AlertX_Security_Monitor::check_wp_updates();
            if ($wp_update['status'] === 'critical') {
                $alerts[] = array(
                    'type' => 'warning',
                    'message' => $wp_update['message']
                );
            }
        }

        // Admin count alert
        if (isset($settings['admin_alert']) && $settings['admin_alert'] === 'yes') {
            $admins = AlertX_Security_Monitor::get_admin_count();
            if ($admins['status'] === 'warning') {
                $alerts[] = array(
                    'type' => 'warning',
                    'message' => $admins['message']
                );
            }
        }

        // WooCommerce alerts
        if (AlertX_WooCommerce_Monitor::is_woocommerce_active()) {
            if (isset($settings['stock_alert']) && $settings['stock_alert'] === 'yes') {
                $out_of_stock = AlertX_WooCommerce_Monitor::get_out_of_stock_products();
                if ($out_of_stock['status'] === 'warning' && $out_of_stock['count'] > 0) {
                    $alerts[] = array(
                        'type' => 'warning',
                        'message' => $out_of_stock['message']
                    );
                }
            }
        }

        return $alerts;
    }

    /**
     * Display a single notice
     *
     * @param array $alert Alert data
     */
    private function display_notice($alert) {
        $class = 'notice notice-' . $alert['type'] . ' is-dismissible alertx-notice';
        printf(
            '<div class="%1$s"><p><strong>AlertX:</strong> %2$s</p></div>',
            esc_attr($class),
            esc_html($alert['message'])
        );
    }

    /**
     * Get status icon
     *
     * @param string $status Status (good, warning, critical, unknown)
     * @return string Icon emoji
     */
    public static function get_status_icon($status) {
        $icons = array(
            'good' => '✅',
            'warning' => '⚠️',
            'critical' => '🔴',
            'unknown' => '❓'
        );

        return isset($icons[$status]) ? $icons[$status] : $icons['unknown'];
    }

    /**
     * Get status color
     *
     * @param string $status Status (good, warning, critical, unknown)
     * @return string Color hex code
     */
    public static function get_status_color($status) {
        $colors = array(
            'good' => '#46b450',
            'warning' => '#ffb900',
            'critical' => '#dc3232',
            'unknown' => '#72aee6'
        );

        return isset($colors[$status]) ? $colors[$status] : $colors['unknown'];
    }
}

// Initialize notifications
new AlertX_Notifications();
