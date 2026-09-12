<?php

namespace RestockX;

defined( 'ABSPATH' ) || exit;

/**
 * Installer class
 *
 * @package RestockX
 * @since 1.0.0
 */
class Installer {
    /**
     * Run the installer
     *
     * @since   1.0.0
     * @access  public
     * @return  void
     */
    public function run() {
        $this->add_version();
        $this->restockx_create_tables();
    }

    /**
     * Add time and version to the database
     *
     * @since   1.0.0
     * @access  public
     * @return  void
     */
    public function add_version() {
        $installed = get_option( 'restockx_installed' );

        if ( ! $installed ) {
            update_option( 'restockx_installed', time() );
        }

        update_option( 'restockx_version', RESTOCKX_VERSION );
    }

    /**
     * Create necessary tables
     *
     * @since 1.0.0
     * @access public
     * @return void
     */
    public function restockx_create_tables() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'restockx_subscriptions';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            email varchar(100) NOT NULL,
            product_id bigint(20) NOT NULL,
            date_added datetime NULL DEFAULT NULL,
            status varchar(20) DEFAULT 'pending' NOT NULL,
            token varchar(64) DEFAULT '' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Upgrade table schema if missing columns
     */
    public function maybe_upgrade_schema() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'restockx_subscriptions';

        // Check for 'status' column in subscriptions table
        $has_status = $wpdb->get_results( $wpdb->prepare( 'SHOW COLUMNS FROM %i LIKE %s', $table_name, 'status' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-time schema check during plugin upgrade; custom tables have no WP API equivalent.
        // Check for 'token' column in subscriptions table
        $has_token = $wpdb->get_results( $wpdb->prepare( 'SHOW COLUMNS FROM %i LIKE %s', $table_name, 'token' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-time schema check during plugin upgrade; custom tables have no WP API equivalent.

        if ( empty( $has_status ) ) {
            $wpdb->query( $wpdb->prepare( "ALTER TABLE %i ADD COLUMN `status` varchar(20) NOT NULL DEFAULT 'pending' AFTER `date_added`", $table_name ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- One-time schema migration; caching an ALTER is not applicable.
        }

        if ( empty( $has_token ) ) {
            $wpdb->query( $wpdb->prepare( "ALTER TABLE %i ADD COLUMN `token` varchar(64) NOT NULL DEFAULT '' AFTER `status`", $table_name ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- One-time schema migration; caching an ALTER is not applicable.
        }
    }
}
