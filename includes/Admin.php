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
