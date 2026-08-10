<?php

namespace AlertX;

defined( 'ABSPATH' ) || exit;

/**
 * Installer class
 *
 * @package AlertX
 * @since 1.0.0
 */
// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, PluginCheck.Security.DirectDB.UnescapedDBParameter
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
        $this->create_tables();
    }

    /**
     * Add time and version to the database
     *
     * @since   1.0.0
     * @access  public
     * @return  void
     */
    public function add_version() {
        $installed = get_option( 'alertx_installed' );

        if ( ! $installed ) {
            update_option( 'alertx_installed', time() );
        }

        update_option( 'alertx_version', ALERTX_VERSION );
    }

    /**
     * Create necessary tables
     *
     * @since 1.0.0
     * @access public
     * @return void
     */
    public function create_tables() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'stock_notifications';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            email varchar(100) NOT NULL,
            product_id bigint(20) NOT NULL,
            date_added datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
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
        $table_name = $wpdb->prefix . 'stock_notifications';

        // Check for 'status' column
        $has_status = $wpdb->get_results( $wpdb->prepare( "SHOW COLUMNS FROM `$table_name` LIKE %s", 'status' ) );
        // Check for 'token' column
        $has_token = $wpdb->get_results( $wpdb->prepare( "SHOW COLUMNS FROM `$table_name` LIKE %s", 'token' ) );

        if ( empty( $has_status ) ) {
            $wpdb->query( "ALTER TABLE `$table_name` ADD COLUMN `status` varchar(20) NOT NULL DEFAULT 'pending' AFTER `date_added`" );
        }

        if ( empty( $has_token ) ) {
            $wpdb->query( "ALTER TABLE `$table_name` ADD COLUMN `token` varchar(64) NOT NULL DEFAULT '' AFTER `status`" );
        }
    }
}
// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, PluginCheck.Security.DirectDB.UnescapedDBParameter

// Register the activation hook in the main plugin file
register_activation_hook( __FILE__, array( 'AlertX\Installer', 'run' ) );
