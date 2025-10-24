<?php

namespace Alertx\Admin;

/**
 * Class Menu
 *
 * Handles administration menu and AJAX functionality for AlertX plugin.
 */
class Menu {

    /**
     * Menu constructor.
     *
     * Adds necessary actions and filters upon initialization.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
        add_action('wp_ajax_alertx_refresh_data', array($this, 'ajax_refresh_data'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Add media page to WordPress admin menu.
     */
    public function add_admin_menu() {
        add_menu_page(
            __('AlertX', 'alertx'),
            __('AlertX', 'alertx'),
            'manage_options',
            'alertx',
            array($this, 'render_main_page'),
            'dashicons-warning',
            100
        );

        add_submenu_page(
            'alertx',
            __('Dashboard', 'alertx'),
            __('Dashboard', 'alertx'),
            'manage_options',
            'alertx',
            array($this, 'render_main_page')
        );

        add_submenu_page(
            'alertx',
            __('Settings', 'alertx'),
            __('Settings', 'alertx'),
            'manage_options',
            'alertx-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('alertx_settings_group', 'alertx_settings', array($this, 'sanitize_settings'));
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();

        $checkboxes = array(
            'memory_alert', 'storage_alert', 'database_alert',
            'admin_alert', 'update_alert', 'stock_alert', 'low_stock_alert'
        );

        foreach ($checkboxes as $checkbox) {
            $sanitized[$checkbox] = isset($input[$checkbox]) ? 'yes' : 'no';
        }

        $sanitized['storage_threshold'] = isset($input['storage_threshold']) ?
            absint($input['storage_threshold']) : 75;

        $sanitized['low_stock_threshold'] = isset($input['low_stock_threshold']) ?
            absint($input['low_stock_threshold']) : 5;

        // Clear cache when settings are saved
        delete_transient('alertx_system_data');

        return $sanitized;
    }

    /**
     * Add dashboard widget
     */
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'alertx_dashboard_widget',
            __('AlertX - System Monitor', 'alertx'),
            array($this, 'render_dashboard_widget')
        );
    }

    /**
     * Render dashboard widget
     */
    public function render_dashboard_widget() {
        $system_data = AlertX_System_Monitor::get_all_data();
        $security_data = AlertX_Security_Monitor::get_all_data();
        $woocommerce_data = AlertX_WooCommerce_Monitor::get_all_data();

        // include ALERTX_PLUGIN_DIR . 'admin/views/dashboard-widget.php';

        $template = __DIR__ . '/views/dashboard-widget.php';
        if ( file_exists( $template ) ) {
            include $template;
        }
    }

    /**
     * Render main page
     */
    public function render_main_page() {
        // $system_data = AlertX_System_Monitor::get_all_data();
        // $security_data = AlertX_Security_Monitor::get_all_data();
        // $woocommerce_data = AlertX_WooCommerce_Monitor::get_all_data();

        // include ALERTX_PLUGIN_DIR . 'admin/views/main-page.php';

        $template = __DIR__ . '/views/main-page.php';
        if ( file_exists( $template ) ) {
            include $template;
        }
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Include the view template for displaying the list
        $template = __DIR__ . '/views/settings-page.php';
        if ( file_exists( $template ) ) {
            include $template;
        }
    }

    /**
     * AJAX refresh data
     */
    public function ajax_refresh_data() {
        check_ajax_referer('alertx_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        // Clear cache
        delete_transient('alertx_system_data');

        $data = array(
            'system' => AlertX_System_Monitor::get_all_data(),
            'security' => AlertX_Security_Monitor::get_all_data(),
            'woocommerce' => AlertX_WooCommerce_Monitor::get_all_data()
        );

        wp_send_json_success($data);
    }
}
