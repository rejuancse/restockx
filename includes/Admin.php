<?php

namespace Alertx;

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, PluginCheck.Security.DirectDB.UnescapedDBParameter
/**
 * The admin class
 */
class Admin {

    protected $wpdb;
    protected $table_name;

    /**
     * Initialize the class
     */
    public function __construct() {
        // global $wpdb;
        // $this->wpdb = $wpdb;
        // $this->table_name = $this->wpdb->prefix . 'alertx_subscriptions';

        new Admin\Alertx_Menu();
        new Admin\Campaign_Ajax();

        add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );
        add_action( 'admin_head', array( $this, 'hide_other_plugin_notices' ) );
    }

    /**
     * Add the dashboard widget to the WordPress admin dashboard.
     *
     * @return void
     */
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'stock_notification_dashboard_widget',
            __('AlertX Notification Statistics', 'alertx-pro'),
            array($this, 'dashboard_widget_function')
        );
    }

    /**
     * Retrieve stock notification statistics and include the dashboard widget template.
     *
     * This function counts total notifications, unique products, and unique emails
     * from the alertx_subscriptions table, then includes the corresponding template.
     *
     * @since   1.0.0
     * @access  public
     * @return  void
     */
    public function dashboard_widget_function() {
        try {
            // Retrieve stock notification statistics
            $statistics = $this->get_stock_notification_statistics();

            // Extract variables for template
            $total_notifications = $statistics['total_notifications'];
            $unique_products = $statistics['unique_products'];
            $unique_emails = $statistics['unique_emails'];

            // Include the dashboard widget template
            include_once( ALERTX_PATH . 'views/dashboard-widget.php' );
        } catch (\Exception $e) {
            // Handle any exceptions that occur during data retrieval
            echo '<p>An error occurred while retrieving data. Please try again later.</p>';
        }
    }

    /**
     * Hide other plugin notices on our plugin pages
     *
     * This method hides all admin notices from other plugins when viewing
     * Alertx plugin pages to provide a cleaner interface.
     *
     * @since   1.0.0
     * @access  public
     * @return  void
     */
    public function hide_other_plugin_notices() {
        // Check if we're on one of our plugin pages
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading URL parameter for conditional display, not processing form data
        $page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

        if ( 'stock-availability-alert' === $page || 'stock-availability-alert-settings' === $page ) {
            // Remove all admin notices except our own
            remove_all_actions( 'admin_notices' );
            remove_all_actions( 'all_admin_notices' );
            remove_all_actions( 'network_admin_notices' );
            remove_all_actions( 'user_admin_notices' );
        }
    }

    /**
     * Resolve the From / Reply-To addresses for outgoing AlertX emails.
     *
     * The configured sender address (Settings → Channels) is only used as the
     * "From" header when its domain matches the site domain. Web hosts are not
     * authorized to send mail for external domains (Gmail, Outlook, etc.);
     * using such addresses as "From" breaks SPF/DMARC alignment and recipients'
     * providers silently drop the messages. In that case the From header falls
     * back to a domain-aligned no-reply@ address and the configured address is
     * kept as "Reply-To" so replies still reach that inbox.
     *
     * @return array { 'from' => string, 'reply_to' => string } Empty "reply_to" when no extra Reply-To is needed.
     */
    public static function resolve_email_sender() {
        $site_domain = (string) wp_parse_url( home_url(), PHP_URL_HOST );
        $configured  = \alertx_get_sender_email();

        // Normalize an optional leading "www." on both domains before comparing.
        $normalize = static function ( $domain ) {
            return strtolower( preg_replace( '/^www\./i', '', (string) $domain ) );
        };

        if ( '' !== $configured ) {
            $sender_domain = $normalize( substr( $configured, strrpos( $configured, '@' ) + 1 ) );

            if ( $sender_domain === $normalize( $site_domain ) ) {
                return array(
                    'from'     => $configured,
                    'reply_to' => '',
                );
            }
        }

        return array(
            'from'     => 'no-reply@' . $site_domain,
            'reply_to' => $configured,
        );
    }

    /**
     * Get stock notification statistics from the database.
     *
     * @return array An associative array containing total notifications, unique products, and unique emails.
     */
    protected function get_stock_notification_statistics() {
        return [
            'total_notifications' => $this->get_total_notifications(),
            'unique_products' => $this->get_unique_products(),
            'unique_emails' => $this->get_unique_emails(),
        ];
    }

    /**
     * Get the total number of notifications.
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     * @return int Total number of notifications.
     */
    protected function get_total_notifications() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'alertx_subscriptions';
        return (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM `{$table_name}`"
        ) ?: 0;
    }

    /**
     * Get the count of unique products.
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     * @return int Number of unique products.
     */
    protected function get_unique_products() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'alertx_subscriptions';
        return (int) $wpdb->get_var(
            "SELECT COUNT(DISTINCT product_id) FROM `{$table_name}`"
        ) ?: 0;
    }

    /**
     * Get the count of unique email addresses.
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     * @return int Number of unique email addresses.
     */
    protected function get_unique_emails() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'alertx_subscriptions';
        return (int) $wpdb->get_var(
            "SELECT COUNT(DISTINCT email) FROM `{$table_name}`"
        ) ?: 0;
    }
}
// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, PluginCheck.Security.DirectDB.UnescapedDBParameter
