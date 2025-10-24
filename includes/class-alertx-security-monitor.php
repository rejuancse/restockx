<?php
/**
 * Security Monitor Class
 *
 * Monitors security aspects including admin users and updates
 *
 * @package AlertX
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class AlertX_Security_Monitor {

    /**
     * Get admin users count
     *
     * @return array Admin users data
     */
    public static function get_admin_count() {
        $admin_users = get_users(array('role' => 'administrator'));
        $count = count($admin_users);

        $admin_list = array();
        foreach ($admin_users as $user) {
            $admin_list[] = array(
                'id' => $user->ID,
                'username' => $user->user_login,
                'email' => $user->user_email,
                'registered' => $user->user_registered
            );
        }

        return array(
            'count' => $count,
            'admins' => $admin_list,
            'status' => $count > 3 ? 'warning' : 'good',
            'message' => $count > 3 ?
                sprintf(__('You have %d administrator accounts. Consider reducing for security.', 'alertx'), $count) :
                __('Admin count looks good.', 'alertx')
        );
    }

    /**
     * Check WordPress core updates
     *
     * @return array WordPress update data
     */
    public static function check_wp_updates() {
        global $wp_version;

        // Force update check
        wp_version_check();

        $update_core = get_site_transient('update_core');

        if (!isset($update_core->updates[0])) {
            return array(
                'status' => 'good',
                'current_version' => $wp_version,
                'message' => __('WordPress is up to date', 'alertx')
            );
        }

        $update = $update_core->updates[0];

        if ($update->response == 'upgrade') {
            return array(
                'status' => 'critical',
                'current_version' => $wp_version,
                'latest_version' => $update->version,
                'message' => sprintf(
                    __('WordPress update available: %s → %s', 'alertx'),
                    $wp_version,
                    $update->version
                )
            );
        }

        return array(
            'status' => 'good',
            'current_version' => $wp_version,
            'message' => __('WordPress is up to date', 'alertx')
        );
    }

    /**
     * Check plugin updates
     *
     * @return array Plugin updates data
     */
    public static function check_plugin_updates() {
        wp_update_plugins();
        $update_plugins = get_site_transient('update_plugins');

        if (empty($update_plugins->response)) {
            return array(
                'status' => 'good',
                'count' => 0,
                'message' => __('All plugins are up to date', 'alertx')
            );
        }

        $count = count($update_plugins->response);

        return array(
            'status' => $count > 0 ? 'warning' : 'good',
            'count' => $count,
            'plugins' => array_keys($update_plugins->response),
            'message' => sprintf(
                _n('%d plugin update available', '%d plugin updates available', $count, 'alertx'),
                $count
            )
        );
    }

    /**
     * Check theme updates
     *
     * @return array Theme updates data
     */
    public static function check_theme_updates() {
        wp_update_themes();
        $update_themes = get_site_transient('update_themes');

        if (empty($update_themes->response)) {
            return array(
                'status' => 'good',
                'count' => 0,
                'message' => __('All themes are up to date', 'alertx')
            );
        }

        $count = count($update_themes->response);

        return array(
            'status' => $count > 0 ? 'warning' : 'good',
            'count' => $count,
            'themes' => array_keys($update_themes->response),
            'message' => sprintf(
                _n('%d theme update available', '%d theme updates available', $count, 'alertx'),
                $count
            )
        );
    }

    /**
     * Get all security data
     *
     * @return array All security monitoring data
     */
    public static function get_all_data() {
        return array(
            'admins' => self::get_admin_count(),
            'wp_update' => self::check_wp_updates(),
            'plugin_updates' => self::check_plugin_updates(),
            'theme_updates' => self::check_theme_updates()
        );
    }
}
