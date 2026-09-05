<?php

namespace Alertx;

defined( 'ABSPATH' ) || exit;

// This plugin stores data in custom tables that have no WordPress query
// abstraction, so direct $wpdb queries are required here. Writes must never
// be cached and admin reads are low-frequency (no persistent object cache
// benefit), so the DB sniffs are disabled for this file. Paired with the
// phpcs:enable at the bottom of the file.
// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, PluginCheck.Security.DirectDB.UnescapedDBParameter

/**
 * Installer class
 *
 * @package Alertx
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
        $this->alertx_create_tables();
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
    public function alertx_create_tables() {
        global $wpdb;

        $table_name = esc_sql( $wpdb->prefix . 'alertx_subscriptions' );
        $restock_table_name = $wpdb->prefix . 'alertx_restock_tracking';
        $campaigns_table_name = $wpdb->prefix . 'alertx_campaigns';
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

        $restock_sql = "CREATE TABLE $restock_table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            restock_date datetime NULL DEFAULT NULL,
            notifications_sent int(11) DEFAULT 0 NOT NULL,
            total_sales decimal(10,2) DEFAULT 0.00 NOT NULL,
            total_orders int(11) DEFAULT 0 NOT NULL,
            PRIMARY KEY  (id),
            KEY product_id (product_id)
        ) $charset_collate;";

        $campaigns_sql = "CREATE TABLE $campaigns_table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            camp_title varchar(255) DEFAULT '' NOT NULL,
            camp_subject varchar(255) NOT NULL,
            email_template_id varchar(50) DEFAULT 'default' NOT NULL,
            recipient_type varchar(20) DEFAULT 'subscribers' NOT NULL,
            recipient_csv_data longtext NULL DEFAULT NULL,
            recipient_emails longtext NULL DEFAULT NULL,
            product_selection varchar(20) DEFAULT 'all' NOT NULL,
            selected_products longtext NULL DEFAULT NULL,
            discount_code varchar(100) DEFAULT '' NOT NULL,
            discount_expiry varchar(100) DEFAULT '' NOT NULL,
            camp_message longtext NULL DEFAULT NULL,
            camp_status varchar(20) DEFAULT 'draft' NOT NULL,
            scheduled_date datetime NULL DEFAULT NULL,
            sent_at datetime NULL DEFAULT NULL,
            created_at datetime NULL DEFAULT NULL,
            total_recipients int(11) DEFAULT 0 NOT NULL,
            emails_sent int(11) DEFAULT 0 NOT NULL,
            emails_failed int(11) DEFAULT 0 NOT NULL,
            PRIMARY KEY  (id),
            KEY camp_status (camp_status),
            KEY scheduled_date (scheduled_date)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        dbDelta($restock_sql);
        dbDelta($campaigns_sql);
    }

    /**
     * Upgrade table schema if missing columns
     */
    public function maybe_upgrade_schema() {
        global $wpdb;
        $table_name = esc_sql( $wpdb->prefix . 'alertx_subscriptions' );
        $campaigns_table = esc_sql( $wpdb->prefix . 'alertx_campaigns' );

        // Check for 'status' column in subscriptions table
        $has_status = $wpdb->get_results( $wpdb->prepare( "SHOW COLUMNS FROM `$table_name` LIKE %s", 'status' ) );
        // Check for 'token' column in subscriptions table
        $has_token = $wpdb->get_results( $wpdb->prepare( "SHOW COLUMNS FROM `$table_name` LIKE %s", 'token' ) );

        if ( empty( $has_status ) ) {
            $wpdb->query( "ALTER TABLE `$table_name` ADD COLUMN `status` varchar(20) NOT NULL DEFAULT 'pending' AFTER `date_added`" );
        }

        if ( empty( $has_token ) ) {
            $wpdb->query( "ALTER TABLE `$table_name` ADD COLUMN `token` varchar(64) NOT NULL DEFAULT '' AFTER `status`" );
        }

        // Upgrade campaigns table if needed
        $this->upgrade_campaigns_table( $campaigns_table );
    }

    /**
     * Upgrade campaigns table schema
     */
    private function upgrade_campaigns_table( $campaigns_table ) {
        global $wpdb;

        // Static table identifier passed from the caller; re-escaped here
        // because the sniffs cannot trace function parameters.
        $campaigns_table = esc_sql( $campaigns_table );

        // Check if table exists
        $table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$campaigns_table'" );
        if ( ! $table_exists ) {
            return;
        }

        // Get existing columns
        $existing_columns = $wpdb->get_col( "SHOW COLUMNS FROM `$campaigns_table`" );
        $column_map = array_flip( $existing_columns );

        // Add new columns if they don't exist
        $new_columns = array(
            'camp_title' => "ADD COLUMN `camp_title` varchar(255) DEFAULT '' NOT NULL AFTER `id`",
            'email_template_id' => "ADD COLUMN `email_template_id` varchar(50) DEFAULT 'default' NOT NULL AFTER `camp_subject`",
            'recipient_type' => "ADD COLUMN `recipient_type` varchar(20) DEFAULT 'subscribers' NOT NULL",
            'recipient_csv_data' => "ADD COLUMN `recipient_csv_data` longtext NULL DEFAULT NULL",
            'recipient_emails' => "ADD COLUMN `recipient_emails` longtext NULL DEFAULT NULL",
            'product_selection' => "ADD COLUMN `product_selection` varchar(20) DEFAULT 'all' NOT NULL",
            'selected_products' => "ADD COLUMN `selected_products` longtext NULL DEFAULT NULL",
            'discount_code' => "ADD COLUMN `discount_code` varchar(100) DEFAULT '' NOT NULL AFTER `selected_products`",
            'discount_expiry' => "ADD COLUMN `discount_expiry` varchar(100) DEFAULT '' NOT NULL AFTER `discount_code`",
            'camp_message' => "ADD COLUMN `camp_message` longtext NULL DEFAULT NULL",
            'scheduled_date' => "ADD COLUMN `scheduled_date` datetime NULL DEFAULT NULL AFTER `camp_status`",
            'sent_at' => "ADD COLUMN `sent_at` datetime NULL DEFAULT NULL AFTER `scheduled_date`",
            'total_recipients' => "ADD COLUMN `total_recipients` int(11) DEFAULT 0 NOT NULL AFTER `created_at`",
            'emails_sent' => "ADD COLUMN `emails_sent` int(11) DEFAULT 0 NOT NULL AFTER `total_recipients`",
            'emails_failed' => "ADD COLUMN `emails_failed` int(11) DEFAULT 0 NOT NULL AFTER `emails_sent`",
        );

        foreach ( $new_columns as $column_name => $add_sql ) {
            if ( ! isset( $column_map[ $column_name ] ) ) {
                // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter -- $add_sql comes from the hardcoded $new_columns whitelist above; esc_sql() would corrupt the DEFAULT '' DDL fragments.
                $wpdb->query( "ALTER TABLE `$campaigns_table` $add_sql" );
            }
        }

        // Remove old columns that exist
        $old_columns = array(
            'discount_switch',
            'discount_all_products',
            'camp_product_name',
            'camp_product_url',
            'camp_descriptions',
        );

        foreach ( $old_columns as $column_name ) {
            if ( isset( $column_map[ $column_name ] ) ) {
                $wpdb->query( "ALTER TABLE `$campaigns_table` DROP COLUMN `" . esc_sql( $column_name ) . "`" );
            }
        }

        // Add indexes if they don't exist
        $indexes = $wpdb->get_results( "SHOW INDEX FROM `$campaigns_table`", ARRAY_A );
        $index_keys = wp_list_pluck( $indexes, 'Key_name' );
        $index_keys = array_unique( $index_keys );

        if ( ! in_array( 'camp_status', $index_keys, true ) ) {
            $wpdb->query( "ALTER TABLE `$campaigns_table` ADD INDEX `camp_status` (`camp_status`)" );
        }

        if ( ! in_array( 'scheduled_date', $index_keys, true ) ) {
            $wpdb->query( "ALTER TABLE `$campaigns_table` ADD INDEX `scheduled_date` (`scheduled_date`)" );
        }
    }
}
// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, PluginCheck.Security.DirectDB.UnescapedDBParameter
