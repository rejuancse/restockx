<?php

namespace RestockX;

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, PluginCheck.Security.DirectDB.UnescapedDBParameter
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
        new Admin\RestockX_Menu();

        add_action( 'admin_head', array( $this, 'hide_other_plugin_notices' ) );
    }

    /**
     * Hide other plugin notices on our plugin pages
     *
     * This method hides all admin notices from other plugins when viewing
     * RestockX plugin pages to provide a cleaner interface.
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
     * Resolve the From / Reply-To addresses for outgoing RestockX emails.
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
        $configured  = \restockx_get_sender_email();

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
}
// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, PluginCheck.Security.DirectDB.UnescapedDBParameter
