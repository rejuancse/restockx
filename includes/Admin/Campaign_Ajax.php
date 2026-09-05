<?php

namespace Alertx\Admin;

defined( 'ABSPATH' ) || exit;

// This plugin stores data in custom tables that have no WordPress query
// abstraction, so direct $wpdb queries are required here. Writes must never
// be cached and admin reads are low-frequency (no persistent object cache
// benefit), so the DB sniffs are disabled for this file. Paired with the
// phpcs:enable at the bottom of the file.
// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, PluginCheck.Security.DirectDB.UnescapedDBParameter

/**
 * Campaign AJAX Handler
 *
 * Handles AJAX requests for saving campaigns and sending emails
 *
 * @package Alertx
 * @since 1.0.0
 */
class Campaign_Ajax {

    /**
     * Initialize the class
     */
    public function __construct() {
        add_action( 'wp_ajax_alertx_save_campaign', array( $this, 'save_campaign' ) );
        add_action( 'wp_ajax_alertx_send_campaign', array( $this, 'send_campaign' ) );
        add_action( 'wp_ajax_alertx_get_campaigns', array( $this, 'get_campaigns' ) );
        add_action( 'wp_ajax_alertx_get_campaign', array( $this, 'get_campaign' ) );
        add_action( 'wp_ajax_alertx_delete_campaign', array( $this, 'delete_campaign' ) );
        add_action( 'wp_ajax_alertx_bulk_delete_campaigns', array( $this, 'bulk_delete_campaigns' ) );
        add_action( 'wp_ajax_alertx_duplicate_campaign', array( $this, 'duplicate_campaign' ) );
        add_action( 'wp_ajax_alertx_activate_campaign', array( $this, 'activate_campaign' ) );
        add_action( 'wp_ajax_alertx_toggle_campaign_status', array( $this, 'toggle_campaign_status' ) );
        add_action( 'wp_ajax_alertx_preview_campaign', array( $this, 'preview_campaign' ) );

        // New AJAX handlers
        add_action( 'wp_ajax_alertx_validate_coupon', array( $this, 'validate_coupon' ) );
        add_action( 'wp_ajax_alertx_get_subscriber_count', array( $this, 'get_subscriber_count' ) );

        // Background sending (avoids long-running HTTP requests)
        add_action( 'alertx_dispatch_campaign_send', array( $this, 'dispatch_campaign_send' ), 10, 2 );

        // Legacy/scheduled start event (was scheduled but never handled)
        add_action( 'alertx_send_scheduled_campaign', array( $this, 'start_scheduled_campaign' ), 10, 1 );
    }

    /**
     * Save campaign as draft or schedule/send
     */
    public function save_campaign() {
        // Verify nonce
        check_ajax_referer( 'alertx_new_campaign', 'nonce' );

        // Validate required fields
        $title = isset( $_POST['campaign_title'] ) ? sanitize_text_field( wp_unslash( $_POST['campaign_title'] ) ) : '';
        $subject = isset( $_POST['campaign_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['campaign_subject'] ) ) : '';

        if ( empty( $title ) ) {
            wp_send_json_error( array( 'message' => 'Campaign title is required.' ) );
        }

        if ( empty( $subject ) ) {
            wp_send_json_error( array( 'message' => 'Subject line is required.' ) );
        }

        // Get campaign data
        $campaign_id = isset( $_POST['campaign_id'] ) ? intval( $_POST['campaign_id'] ) : 0;
        $campaign_action = isset( $_POST['campaign_action'] ) ? sanitize_text_field( wp_unslash( $_POST['campaign_action'] ) ) : 'draft';
        $email_template = isset( $_POST['email_template'] ) ? sanitize_text_field( wp_unslash( $_POST['email_template'] ) ) : 'default';
        $recipient_type = isset( $_POST['recipient_type'] ) ? sanitize_text_field( wp_unslash( $_POST['recipient_type'] ) ) : 'subscribers';
        $recipient_csv_data = isset( $_POST['recipient_csv_data'] ) ? sanitize_text_field( wp_unslash( $_POST['recipient_csv_data'] ) ) : '';
        $recipient_emails = isset( $_POST['recipient_emails'] ) ? sanitize_text_field( wp_unslash( $_POST['recipient_emails'] ) ) : '';
        $product_selection = isset( $_POST['product_selection'] ) ? sanitize_text_field( wp_unslash( $_POST['product_selection'] ) ) : 'all';
        $selected_products = isset( $_POST['selected_products'] ) ? sanitize_text_field( wp_unslash( $_POST['selected_products'] ) ) : '';
        $include_coupon = isset( $_POST['include_coupon'] ) ? sanitize_text_field( wp_unslash( $_POST['include_coupon'] ) ) : '0';
        $coupon_code = isset( $_POST['coupon_code'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) : '';
        $coupon_expiry = isset( $_POST['coupon_expiry'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_expiry'] ) ) : '';
        $campaign_message = isset( $_POST['campaign_message'] ) ? wp_kses_post( wp_unslash( $_POST['campaign_message'] ) ) : '';
        $schedule_type = isset( $_POST['schedule_type'] ) ? sanitize_text_field( wp_unslash( $_POST['schedule_type'] ) ) : 'now';
        $scheduled_date = isset( $_POST['scheduled_date'] ) ? sanitize_text_field( wp_unslash( $_POST['scheduled_date'] ) ) : '';

        // Validate coupon code if provided
        if ( $include_coupon === '1' && ! empty( $coupon_code ) ) {
            if ( ! $this->validate_wc_coupon( $coupon_code ) ) {
                wp_send_json_error( array( 'message' => 'Invalid coupon code. Please enter a valid WooCommerce coupon code.' ) );
            }
        }

        // Clear coupon if not included
        if ( $include_coupon !== '1' ) {
            $coupon_code = '';
            $coupon_expiry = '';
        }

        // Get recipient emails
        $recipients = $this->get_recipient_emails( $recipient_type, $recipient_csv_data, $recipient_emails );
        $total_recipients = count( $recipients );

        if ( $total_recipients === 0 ) {
            wp_send_json_error( array( 'message' => 'No valid recipients found. Please check your recipient selection.' ) );
        }

        // Determine campaign status
        $camp_status = 'draft';
        if ( $campaign_action === 'send' && $schedule_type === 'now' ) {
            $camp_status = 'sending';
        } elseif ( $campaign_action === 'schedule' ) {
            $camp_status = 'scheduled';
        }

        global $wpdb;
        $table_name = esc_sql( $wpdb->prefix . 'alertx_campaigns' );

        // Prepare campaign data
        //
        // scheduled_date is only included when actually scheduling AND the
        // value converts to a real MySQL datetime. Inserting an empty string
        // into a datetime column makes strict SQL-mode hosts reject the whole
        // INSERT/UPDATE ("Incorrect datetime value: ''").
        $campaign_data = array(
            'camp_title' => $title,
            'camp_subject' => $subject,
            'email_template_id' => $email_template,
            'recipient_type' => $recipient_type,
            'recipient_csv_data' => $recipient_csv_data,
            'recipient_emails' => $recipient_emails,
            'product_selection' => $product_selection,
            'selected_products' => $selected_products,
            'discount_code' => $coupon_code,
            'discount_expiry' => $coupon_expiry,
            'camp_message' => $campaign_message,
            'camp_status' => $camp_status,
            'total_recipients' => $total_recipients,
        );

        if ( $campaign_action === 'schedule' && ! empty( $scheduled_date ) ) {
            $schedule_ts = strtotime( $scheduled_date );

            if ( $schedule_ts ) {
                $campaign_data['scheduled_date'] = gmdate( 'Y-m-d H:i:s', $schedule_ts );
            }
        }

        // Update existing campaign or insert new one
        if ( $campaign_id > 0 ) {
            $result = $wpdb->update(
                $table_name,
                $campaign_data,
                array( 'id' => $campaign_id ),
                $this->get_campaign_field_formats( $campaign_data ),
                array( '%d' )
            );

            if ( $result === false ) {
                wp_send_json_error( array( 'message' => 'Failed to update campaign. Please try again.' ) );
            }
        } else {
            $campaign_data['created_at'] = current_time( 'mysql' );
            $result = $wpdb->insert(
                $table_name,
                $campaign_data,
                $this->get_campaign_field_formats( $campaign_data )
            );

            if ( ! $result ) {
                wp_send_json_error( array( 'message' => 'Failed to save campaign. Please try again.' ) );
            }

            $campaign_id = $wpdb->insert_id;
        }

        // Send or schedule campaign
        if ( $campaign_action === 'send' && $schedule_type === 'now' ) {
            // Queue the actual sending in the background. Sending hundreds of
            // emails inside this HTTP request would hit PHP/Proxy timeouts on
            // production hosting and leave the browser stuck.
            if ( ! wp_next_scheduled( 'alertx_dispatch_campaign_send', array( $campaign_id, 0 ) ) ) {
                wp_schedule_single_event( time() + 10, 'alertx_dispatch_campaign_send', array( $campaign_id, 0 ) );
                spawn_cron();
            }

            wp_send_json_success( array(
                'message' => sprintf(
                    'Campaign saved! Sending to %d recipients in the background.',
                    $total_recipients
                ),
                'campaign_id' => $campaign_id,
                'queued' => true
            ) );
        } elseif ( $campaign_action === 'schedule' ) {
            // Schedule campaign
            if ( ! empty( $scheduled_date ) ) {
                $this->schedule_campaign( $campaign_id, $scheduled_date );
            }

            wp_send_json_success( array(
                'message' => 'Campaign scheduled successfully!',
                'campaign_id' => $campaign_id
            ) );
        } else {
            // Draft saved
            wp_send_json_success( array(
                'message' => 'Campaign saved as draft successfully!',
                'campaign_id' => $campaign_id
            ) );
        }
    }

    /**
     * Send campaign to all subscribers
     */
    public function send_campaign() {
        // Verify nonce
        check_ajax_referer( 'alertx_campaign_nonce', 'nonce' );

        // Validate required fields
        $subject = isset( $_POST['camp_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['camp_subject'] ) ) : '';
        if ( empty( $subject ) ) {
            wp_send_json_error( array( 'message' => 'Subject line is required.' ) );
        }

        // Get campaign data
        $discount_switch = isset( $_POST['discount_switch'] ) ? sanitize_text_field( wp_unslash( $_POST['discount_switch'] ) ) : 'off';
        $discount_all_products = isset( $_POST['discount_all_products'] ) ? sanitize_text_field( wp_unslash( $_POST['discount_all_products'] ) ) : 'off';
        $camp_product_name = isset( $_POST['camp_product_name'] ) ? sanitize_text_field( wp_unslash( $_POST['camp_product_name'] ) ) : '';
        $camp_product_url = isset( $_POST['camp_product_url'] ) ? esc_url_raw( wp_unslash( $_POST['camp_product_url'] ) ) : '';
        $discount_code = isset( $_POST['discount_code'] ) ? sanitize_text_field( wp_unslash( $_POST['discount_code'] ) ) : '';
        $discount_expiry = isset( $_POST['discount_expiry'] ) ? sanitize_text_field( wp_unslash( $_POST['discount_expiry'] ) ) : '';
        $camp_descriptions = isset( $_POST['camp_descriptions'] ) ? sanitize_textarea_field( wp_unslash( $_POST['camp_descriptions'] ) ) : '';
        $campaign_id = isset( $_POST['campaign_id'] ) ? intval( $_POST['campaign_id'] ) : 0;

        // Only clear product fields if All Products is ON (meaning we don't need specific products)
        if ( $discount_all_products === 'on' ) {
            $camp_product_name = '';
            $camp_product_url = '';
        }

        // If discount switch is off, only clear discount code and expiry
        // Keep product name and URL as they may have been entered by user
        if ( $discount_switch !== 'on' ) {
            $discount_all_products = 'off';
            $discount_code = '';
            $discount_expiry = '';
        }

        global $wpdb;
        $table_name = esc_sql( $wpdb->prefix . 'alertx_campaigns' );
        $subscriptions_table = esc_sql( $wpdb->prefix . 'alertx_subscriptions' );

        // Prepare campaign data
        //
        // discount_switch/discount_all_products/camp_product_name/
        // camp_product_url were removed from the schema in favor of the
        // discount_code + camp_message columns; camp_descriptions was
        // renamed to camp_message.
        $campaign_data = array(
            'camp_subject' => $subject,
            'discount_code' => $discount_code,
            'discount_expiry' => $discount_expiry,
            'camp_message' => $camp_descriptions,
            'camp_status' => 'sent',
            'sent_at' => current_time( 'mysql' ),
        );

        // Update existing campaign or insert new one
        if ( $campaign_id > 0 ) {
            // Update existing campaign and mark as sent
            $result = $wpdb->update(
                $table_name,
                $campaign_data,
                array( 'id' => $campaign_id ),
                array( '%s', '%s', '%s', '%s', '%s', '%s' ),
                array( '%d' )
            );

            if ( $result === false ) {
                wp_send_json_error( array( 'message' => 'Failed to update campaign. Please try again.' ) );
            }
        } else {
            // Insert new campaign
            $campaign_data['created_at'] = current_time( 'mysql' );
            $result = $wpdb->insert(
                $table_name,
                $campaign_data,
                array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
            );

            if ( ! $result ) {
                wp_send_json_error( array( 'message' => 'Failed to save campaign. Please try again.' ) );
            }

            $campaign_id = $wpdb->insert_id;
        }

        // Get all unique subscribers
        $subscribers = $wpdb->get_results(
            "SELECT DISTINCT email FROM $subscriptions_table WHERE status != 'unsubscribed'",
            ARRAY_A
        );

        if ( empty( $subscribers ) ) {
            wp_send_json_success( array(
                'message' => 'Campaign saved but no subscribers found to send emails.',
                'campaign_id' => $campaign_id
            ) );
        }

        // Send emails to all subscribers
        $emails_sent = 0;
        $emails_failed = 0;

        foreach ( $subscribers as $subscriber ) {
            $email = $subscriber['email'];
            $first_name = $this->extract_first_name( $email );

            // Replace placeholders in message
            $message = $camp_descriptions;
            $message = str_replace( '{product_name}', $camp_product_name, $message );
            $message = str_replace( '{discount_code}', $discount_code, $message );
            $message = str_replace( '{product_url}', $camp_product_url, $message );

            // Send email
            $sent = $this->send_campaign_email( $email, $subject, $message );

            if ( $sent ) {
                $emails_sent++;
            } else {
                $emails_failed++;
            }
        }

        wp_send_json_success( array(
            'message' => sprintf(
                'Campaign sent successfully! %d emails sent, %d failed.',
                $emails_sent,
                $emails_failed
            ),
            'campaign_id' => $campaign_id,
            'emails_sent' => $emails_sent,
            'emails_failed' => $emails_failed
        ) );
    }

    /**
     * Get the real first name for an email address.
     *
     * Priority:
     * 1. WordPress user account first_name meta (covers registered
     *    users and WooCommerce customer accounts).
     * 2. The user's display name (first word).
     * 3. Guest WooCommerce customers - billing first name from their
     *    most recent order.
     * 4. Fallback: derive from the email address prefix.
     */
    private function extract_first_name( $email ) {
        $name = $this->lookup_first_name_from_accounts( $email );

        if ( ! empty( $name ) ) {
            return $name;
        }

        // Derive from the email address prefix
        $parts = explode( '@', $email );
        $name_part = isset( $parts[0] ) ? $parts[0] : '';

        // Remove numbers, dots, underscores, etc.
        $name_part = preg_replace( '/[0-9._-]+/', ' ', $name_part );

        // Capitalize first letter
        $name_part = ucwords( trim( $name_part ) );

        return empty( $name_part ) ? __( 'Subscriber', 'alertx-pro' ) : $name_part;
    }

    /**
     * Look up a real first name from the WordPress user record or,
     * for guests, from their WooCommerce billing details.
     */
    private function lookup_first_name_from_accounts( $email ) {
        if ( empty( $email ) || ! is_email( $email ) ) {
            return '';
        }

        // Registered user / WooCommerce customer account
        $user = get_user_by( 'email', $email );

        if ( $user ) {
            $first_name = get_user_meta( $user->ID, 'first_name', true );

            if ( '' !== trim( (string) $first_name ) ) {
                return trim( (string) $first_name );
            }

            if ( ! empty( $user->display_name ) ) {
                $words = preg_split( '/\s+/', trim( $user->display_name ) );

                if ( ! empty( $words[0] ) ) {
                    return $words[0];
                }
            }
        }

        // Guest customer - billing name from the most recent order
        if ( class_exists( 'WooCommerce' ) && function_exists( 'wc_get_orders' ) ) {
            $orders = wc_get_orders( array(
                'billing_email' => $email,
                'limit'         => 1,
                'orderby'       => 'date',
                'order'         => 'DESC',
                'type'          => 'shop_order',
            ) );

            if ( ! empty( $orders ) && is_object( $orders[0] ) && method_exists( $orders[0], 'get_billing_first_name' ) ) {
                $billing_name = trim( (string) $orders[0]->get_billing_first_name() );

                if ( '' !== $billing_name ) {
                    return $billing_name;
                }
            }
        }

        return '';
    }

    /**
     * Send campaign email to subscriber
     *
     * @param string $to          Recipient email address.
     * @param string $subject     Email subject.
     * @param string $message     Message body with placeholders resolved.
     * @param string $template_id Campaign's saved template ID (email_template_id).
     */
    private function send_campaign_email( $to, $subject, $message, $template_id = '' ) {
        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        // Keep the From address domain-aligned so messages are not dropped
        // by recipients' providers; external configured addresses become
        // the Reply-To instead.
        $sender = \Alertx\Admin::resolve_email_sender();

        $headers[] = 'From: ' . get_bloginfo( 'name' ) . ' <' . sanitize_email( $sender['from'] ) . '>';

        if ( '' !== $sender['reply_to'] ) {
            $headers[] = 'Reply-To: ' . sanitize_email( $sender['reply_to'] );
        }

        // Create HTML email body - renders the exact template saved with the
        // campaign, matching what the admin sees in the Quick View preview.
        $html_message = $this->get_campaign_email_html( $template_id, $subject, $message );

        return wp_mail( $to, $subject, $html_message, $headers );
    }

    /**
     * Get campaigns list with pagination
     */
    public function get_campaigns() {
        check_ajax_referer( 'alertx_campaign_nonce', 'nonce' );

        global $wpdb;
        $table_name = esc_sql( $wpdb->prefix . 'alertx_campaigns' );

        // Pagination parameters
        $page = isset( $_POST['page'] ) ? max(1, intval( $_POST['page'] )) : 1;
        $per_page = 10;
        $offset = ( $page - 1 ) * $per_page;

        // Get total count
        $total = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
        $total_pages = ceil( $total / $per_page );

        // Get campaigns
        $campaigns = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ), ARRAY_A );

        // Attach the live product count from the WooCommerce coupon - the
        // same source the email's {product_list} is built from.
        $coupon_counts = array();

        if ( ! empty( $campaigns ) && is_array( $campaigns ) ) {
            foreach ( $campaigns as $key => $campaign ) {
                $code = isset( $campaign['discount_code'] ) ? $campaign['discount_code'] : '';

                if ( '' === $code || ! class_exists( 'WooCommerce' ) ) {
                    $campaigns[ $key ]['coupon_product_count'] = 0;
                    continue;
                }

                // Same coupon used by several campaigns? Count only once.
                if ( ! isset( $coupon_counts[ $code ] ) ) {
                    $coupon_counts[ $code ] = $this->get_coupon_product_count( $code );
                }

                $campaigns[ $key ]['coupon_product_count'] = $coupon_counts[ $code ];
            }
        }

        wp_send_json_success( array(
            'campaigns' => $campaigns,
            'pagination' => array(
                'total' => $total,
                'per_page' => $per_page,
                'current_page' => $page,
                'total_pages' => $total_pages
            )
        ) );
    }

    /**
     * Get single campaign (for the edit screen)
     */
    public function get_campaign() {
        check_ajax_referer( 'alertx_new_campaign', 'nonce' );

        $campaign_id = isset( $_POST['campaign_id'] ) ? intval( $_POST['campaign_id'] ) : 0;

        if ( ! $campaign_id ) {
            wp_send_json_error( array( 'message' => 'Invalid campaign ID.' ) );
        }

        global $wpdb;
        $table_name = esc_sql( $wpdb->prefix . 'alertx_campaigns' );

        $campaign = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $campaign_id
        ), ARRAY_A );

        if ( ! $campaign ) {
            wp_send_json_error( array( 'message' => 'Campaign not found.' ) );
        }

        wp_send_json_success( array( 'campaign' => $campaign ) );
    }

    /**
     * Delete single campaign
     */
    public function delete_campaign() {
        check_ajax_referer( 'alertx_campaign_nonce', 'nonce' );

        $campaign_id = isset( $_POST['campaign_id'] ) ? intval( $_POST['campaign_id'] ) : 0;

        if ( ! $campaign_id ) {
            wp_send_json_error( array( 'message' => 'Invalid campaign ID.' ) );
        }

        global $wpdb;
        $table_name = esc_sql( $wpdb->prefix . 'alertx_campaigns' );

        $result = $wpdb->delete(
            $table_name,
            array( 'id' => $campaign_id ),
            array( '%d' )
        );

        if ( $result ) {
            wp_send_json_success( array( 'message' => 'Campaign deleted successfully.' ) );
        } else {
            wp_send_json_error( array( 'message' => 'Failed to delete campaign.' ) );
        }
    }

    /**
     * Bulk delete campaigns
     */
    public function bulk_delete_campaigns() {
        check_ajax_referer( 'alertx_campaign_nonce', 'nonce' );

        $campaign_ids = isset( $_POST['campaign_ids'] ) ? array_map( 'intval', (array) wp_unslash( $_POST['campaign_ids'] ) ) : array();

        if ( empty( $campaign_ids ) ) {
            wp_send_json_error( array( 'message' => 'No campaigns selected.' ) );
        }

        global $wpdb;
        $table_name = esc_sql( $wpdb->prefix . 'alertx_campaigns' );

        $placeholders = implode( ',', array_fill( 0, count( $campaign_ids ), '%d' ) );

        $result = $wpdb->query( $wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- %d placeholders are built dynamically for the IN() clause; every value is intval()'d above.
            "DELETE FROM $table_name WHERE id IN ($placeholders)",
            ...$campaign_ids
        ) );

        if ( $result ) {
            wp_send_json_success( array(
                'message' => sprintf( '%d campaigns deleted successfully.', $wpdb->rows_affected )
            ) );
        } else {
            wp_send_json_error( array( 'message' => 'Failed to delete campaigns.' ) );
        }
    }

    /**
     * Duplicate campaign
     */
    public function duplicate_campaign() {
        check_ajax_referer( 'alertx_campaign_nonce', 'nonce' );

        $campaign_id = isset( $_POST['campaign_id'] ) ? intval( $_POST['campaign_id'] ) : 0;

        if ( ! $campaign_id ) {
            wp_send_json_error( array( 'message' => 'Invalid campaign ID.' ) );
        }

        global $wpdb;
        $table_name = esc_sql( $wpdb->prefix . 'alertx_campaigns' );

        // Get original campaign
        $campaign = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $campaign_id
        ), ARRAY_A );

        if ( ! $campaign ) {
            wp_send_json_error( array( 'message' => 'Campaign not found.' ) );
        }

        // Append an incremented "copy" suffix to the title and subject
        // ("Base Name" -> "Base Name - copy" -> "Base Name - copy copy")
        $campaign['camp_title']   = $this->add_copy_suffix( isset( $campaign['camp_title'] ) ? $campaign['camp_title'] : '' );
        $campaign['camp_subject'] = $this->add_copy_suffix( isset( $campaign['camp_subject'] ) ? $campaign['camp_subject'] : '' );

        // Get current timestamp for the duplicated campaign
        $current_timestamp = current_time( 'mysql' );

        // Remove ID and old timestamps for duplication
        unset( $campaign['id'] );
        unset( $campaign['created_at'] );
        unset( $campaign['sent_at'] );

        // Set status to draft and add FRESH current timestamp
        $campaign['camp_status'] = 'draft';
        $campaign['created_at'] = $current_timestamp;

        // Prepare fields and formats for database insert with CURRENT time
        $campaign_fields = array(
            'camp_title' => isset( $campaign['camp_title'] ) ? $campaign['camp_title'] : '',
            'camp_subject' => $campaign['camp_subject'],
            'email_template_id' => isset( $campaign['email_template_id'] ) ? $campaign['email_template_id'] : 'default',
            'recipient_type' => isset( $campaign['recipient_type'] ) ? $campaign['recipient_type'] : 'subscribers',
            'recipient_csv_data' => isset( $campaign['recipient_csv_data'] ) ? $campaign['recipient_csv_data'] : '',
            'recipient_emails' => isset( $campaign['recipient_emails'] ) ? $campaign['recipient_emails'] : '',
            'product_selection' => isset( $campaign['product_selection'] ) ? $campaign['product_selection'] : 'all',
            'selected_products' => isset( $campaign['selected_products'] ) ? $campaign['selected_products'] : '',
            'discount_code' => isset( $campaign['discount_code'] ) ? $campaign['discount_code'] : '',
            'discount_expiry' => isset( $campaign['discount_expiry'] ) ? $campaign['discount_expiry'] : '',
            'camp_message' => isset( $campaign['camp_message'] ) ? $campaign['camp_message'] : '',
            // A duplicate is a fresh campaign: start as draft (publish it
            // with the status switcher when ready). Sent stats are not copied.
            'camp_status' => 'draft',
            'created_at' => $current_timestamp,  // Use CURRENT time
            'total_recipients' => isset( $campaign['total_recipients'] ) ? intval( $campaign['total_recipients'] ) : 0,
        );

        // Insert as new campaign with explicit field order
        $result = $wpdb->insert(
            $table_name,
            $campaign_fields,
            $this->get_campaign_field_formats( $campaign_fields )
        );

        if ( $result ) {
            wp_send_json_success( array(
                'message' => 'Campaign duplicated successfully.',
                'new_id' => $wpdb->insert_id,
                'created_at' => $current_timestamp  // Return current timestamp
            ) );
        } else {
            wp_send_json_error( array( 'message' => 'Failed to duplicate campaign.' ) );
        }
    }

    /**
     * Append an incremented "copy" suffix to a campaign title/subject.
     *
     * First duplication:  "Base Name"       -> "Base Name - copy"
     * Further duplicates: "Base Name - copy" -> "Base Name - copy copy"
     *
     * @param string $text Original title or subject.
     * @return string Text with the "copy" suffix appended.
     */
    private function add_copy_suffix( $text ) {
        // Already ends with a "copy" suffix? Extract the base and count the copies.
        // Match: Base Name - copy copy copy
        if ( preg_match( '/^(.*?)\s+-\s+(copy\s*)+$/i', $text, $matches ) ) {
            $base_text = $matches[1];

            // Count how many "copy" words exist (separated by spaces)
            preg_match_all( '/\bcopy\b/i', $text, $copy_matches );
            $copy_count = count( $copy_matches[0] );

            // Build new text with incremented copy count
            $copy_suffix = implode( ' ', array_fill( 0, $copy_count + 1, 'copy' ) );
            $new_text    = $base_text . ' - ' . $copy_suffix;
        } else {
            // First time duplication - add " - copy"
            $new_text = $text . ' - copy';
        }

        // Trim any extra spaces
        return trim( preg_replace( '/\s+/', ' ', $new_text ) );
    }

    /**
     * Activate draft campaign (send emails)
     *
     * Resolves recipients exactly like the campaign editor does, so CSV
     * imports and specific email lists are honored - not just subscribers.
     * The actual sending is queued through the shared WP-Cron batch
     * dispatcher instead of running inside this HTTP request.
     */
    public function activate_campaign() {
        check_ajax_referer( 'alertx_campaign_nonce', 'nonce' );

        $campaign_id = isset( $_POST['campaign_id'] ) ? intval( $_POST['campaign_id'] ) : 0;

        if ( ! $campaign_id ) {
            wp_send_json_error( array( 'message' => 'Invalid campaign ID.' ) );
        }

        global $wpdb;
        $table_name = esc_sql( $wpdb->prefix . 'alertx_campaigns' );

        // Get campaign
        $campaign = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $campaign_id
        ), ARRAY_A );

        if ( ! $campaign ) {
            wp_send_json_error( array( 'message' => 'Campaign not found.' ) );
        }

        if ( $campaign['camp_status'] !== 'draft' ) {
            wp_send_json_error( array( 'message' => 'Only draft campaigns can be activated.' ) );
        }

        // Resolve recipients based on the campaign's own recipient type
        // (subscribers, CSV import or specific emails).
        $recipients = $this->get_recipient_emails(
            isset( $campaign['recipient_type'] ) ? $campaign['recipient_type'] : 'subscribers',
            isset( $campaign['recipient_csv_data'] ) ? $campaign['recipient_csv_data'] : '[]',
            isset( $campaign['recipient_emails'] ) ? $campaign['recipient_emails'] : '[]'
        );

        if ( empty( $recipients ) ) {
            wp_send_json_error( array( 'message' => 'No valid recipients found for this campaign.' ) );
        }

        // Flip to sending and reset counters; the dispatcher keeps them updated per batch.
        $wpdb->update(
            $table_name,
            array(
                'camp_status'      => 'sending',
                'total_recipients' => count( $recipients ),
                'emails_sent'      => 0,
                'emails_failed'    => 0,
            ),
            array( 'id' => $campaign_id ),
            array( '%s', '%d', '%d', '%d' ),
            array( '%d' )
        );

        // Queue the first background batch (handles every recipient type,
        // placeholders, templates and batching).
        if ( ! wp_next_scheduled( 'alertx_dispatch_campaign_send', array( $campaign_id, 0 ) ) ) {
            wp_schedule_single_event( time() + 5, 'alertx_dispatch_campaign_send', array( $campaign_id, 0 ) );
            spawn_cron();
        }

        wp_send_json_success( array(
            'message' => sprintf(
                'Campaign activated! Sending to %d recipients in the background.',
                count( $recipients )
            ),
            'queued' => true,
        ) );
    }

    /**
     * Toggle campaign status (active/inactive)
     */
    public function toggle_campaign_status() {
        check_ajax_referer( 'alertx_campaign_nonce', 'nonce' );

        $campaign_id = isset( $_POST['campaign_id'] ) ? intval( $_POST['campaign_id'] ) : 0;

        if ( ! $campaign_id ) {
            wp_send_json_error( array( 'message' => 'Invalid campaign ID.' ) );
        }

        global $wpdb;
        $table_name = esc_sql( $wpdb->prefix . 'alertx_campaigns' );

        // Get current status
        $campaign = $wpdb->get_row( $wpdb->prepare(
            "SELECT camp_status FROM $table_name WHERE id = %d",
            $campaign_id
        ), ARRAY_A );

        if ( ! $campaign ) {
            wp_send_json_error( array( 'message' => 'Campaign not found.' ) );
        }

        // Only draft/sent campaigns can be toggled - scheduled or in-progress
        // sends must not be interrupted by the switcher.
        if ( ! in_array( $campaign['camp_status'], array( 'draft', 'sent' ), true ) ) {
            wp_send_json_error( array( 'message' => 'Only draft or published campaigns can be toggled.' ) );
        }

        // Toggle status
        $new_status = ( $campaign['camp_status'] === 'draft' ) ? 'sent' : 'draft';

        // Publishing via the switcher stamps the send time, so the
        // Schedule column never shows the MySQL zero date.
        $update_data   = array( 'camp_status' => $new_status );
        $update_format = array( '%s' );

        if ( 'sent' === $new_status ) {
            $update_data['sent_at'] = current_time( 'mysql' );
            $update_format[]        = '%s';
        }

        $result = $wpdb->update(
            $table_name,
            $update_data,
            array( 'id' => $campaign_id ),
            $update_format
        );

        if ( $result ) {
            wp_send_json_success( array(
                'message' => sprintf( 'Campaign status changed to %s.', $new_status ),
                'new_status' => $new_status
            ) );
        } else {
            wp_send_json_error( array( 'message' => 'Failed to update campaign status.' ) );
        }
    }

    /**
     * Preview campaign email (quick view in the campaigns list)
     */
    public function preview_campaign() {
        check_ajax_referer( 'alertx_campaign_nonce', 'nonce' );

        $campaign_id = isset( $_POST['campaign_id'] ) ? intval( $_POST['campaign_id'] ) : 0;

        if ( ! $campaign_id ) {
            wp_send_json_error( array( 'message' => 'Invalid campaign ID.' ) );
        }

        global $wpdb;
        $table_name = esc_sql( $wpdb->prefix . 'alertx_campaigns' );

        $campaign = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $campaign_id
        ), ARRAY_A );

        if ( ! $campaign ) {
            wp_send_json_error( array( 'message' => 'Campaign not found.' ) );
        }

        $subject = isset( $campaign['camp_subject'] ) ? $campaign['camp_subject'] : '';
        $template_id = isset( $campaign['email_template_id'] ) ? $campaign['email_template_id'] : '';

        // Render the message exactly like the real send does (with placeholders resolved)
        $message = $this->render_campaign_message( $campaign, __( 'Subscriber', 'alertx-pro' ) );

        // Render the exact email template selected and saved with this
        // campaign (falls back to the generic wrapper for legacy rows).
        $html_content = $this->get_campaign_email_html( $template_id, $subject, $message );

        wp_send_json_success( array(
            'subject' => $subject,
            'html_content' => $html_content,
            'preview_email' => 'subscriber@example.com'
        ) );
    }

    /**
     * Resolve all campaign message placeholders (same rules as
     * send_campaign_emails) so previews match the actual email.
     */
    private function render_campaign_message( $campaign, $first_name ) {
        $code          = isset( $campaign['discount_code'] ) ? $campaign['discount_code'] : '';
        $expiry_text   = $this->get_expiry_placeholder_text( isset( $campaign['discount_expiry'] ) ? $campaign['discount_expiry'] : '' );
        $discount_text = $this->get_discount_placeholder_text( $code );

        // Coupon driven placeholders:
        // - coupon has restricted products -> product cards (image/name/price/Buy Now)
        // - no restricted products         -> CTA button linking to the shop page
        $product_list_html = $this->get_coupon_products_cards_html( $code );

        if ( '' !== $product_list_html ) {
            $cta_button_html = '';
        } else {
            $cta_button_html = $this->get_cta_button_html( isset( $campaign['email_template_id'] ) ? $campaign['email_template_id'] : '' );
        }

        $message = isset( $campaign['camp_message'] ) ? $campaign['camp_message'] : '';

        $message = str_replace( '{campaign_title}', sanitize_text_field( isset( $campaign['camp_title'] ) ? $campaign['camp_title'] : '' ), $message );
        $message = str_replace( '{first_name}', $first_name, $message );
        $message = str_replace( '{discount_code}', $code, $message );
        $message = str_replace( '{discount_amount}', $discount_text, $message );
        $message = str_replace( '{discount_expiry}', $expiry_text, $message );
        $message = str_replace( '{site_name}', get_bloginfo( 'name' ), $message );
        $message = str_replace( '{cta_button}', $cta_button_html, $message );
        $message = str_replace( '{product_list}', $product_list_html, $message );

        // Legacy {product_name}/{product_url} support for older/custom templates
        $selection = isset( $campaign['product_selection'] ) ? $campaign['product_selection'] : 'all';

        if ( 'specific' === $selection ) {
            $products = json_decode( isset( $campaign['selected_products'] ) ? $campaign['selected_products'] : '', true );

            if ( ! empty( $products ) && is_array( $products ) && class_exists( 'WooCommerce' ) ) {
                $first_product = $products[0];
                $product = isset( $first_product['id'] ) ? wc_get_product( intval( $first_product['id'] ) ) : false;

                if ( $product ) {
                    $message = str_replace( '{product_name}', $product->get_name(), $message );
                    $message = str_replace( '{product_url}', get_permalink( $product->get_id() ), $message );
                }
            }
        } else {
            $message = str_replace( '{product_url}', $this->get_shop_page_url(), $message );
        }

        return $message;
    }

    /**
     * Validate WooCommerce coupon code
     */
    public function validate_coupon() {
        check_ajax_referer( 'alertx_new_campaign', 'nonce' );

        $code = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';

        if ( empty( $code ) ) {
            wp_send_json_error( array( 'message' => 'Coupon code is required' ) );
        }

        // Check if WooCommerce is active
        if ( ! class_exists( 'WooCommerce' ) ) {
            wp_send_json_error( array( 'message' => 'WooCommerce is not active' ) );
        }

        // Try to get the coupon
        $coupon = new \WC_Coupon( $code );

        // Check if coupon exists (has a valid ID)
        $coupon_id = $coupon->get_id();

        if ( ! $coupon_id ) {
            wp_send_json_error( array(
                'message' => 'Coupon code does not exist in WooCommerce'
            ) );
        }

        // Get coupon data for validation
        $amount = $coupon->get_amount();
        $discount_type = $coupon->get_discount_type();
        $description = $coupon->get_description();

        // Check coupon expiry
        $date_expires = $coupon->get_date_expires();
        $current_time = current_time( 'timestamp' );

        if ( $date_expires && $date_expires->getTimestamp() < $current_time ) {
            wp_send_json_error( array(
                'message' => 'Coupon has expired'
            ) );
        }

        // Check usage limit
        $usage_limit = $coupon->get_usage_limit();
        $usage_count = $coupon->get_usage_count();

        if ( $usage_limit > 0 && $usage_count >= $usage_limit ) {
            wp_send_json_error( array(
                'message' => 'Coupon usage limit has been reached'
            ) );
        }

        // Build success message with coupon details
        $message = 'Valid coupon';

        if ( $amount > 0 ) {
            if ( $discount_type === 'percent' ) {
                $message .= ' - ' . $amount . '% discount';
            } elseif ( $discount_type === 'fixed_cart' || $discount_type === 'fixed_product' ) {
                $message .= ' - ' . get_woocommerce_currency_symbol() . $amount . ' discount';
            }
        }

        if ( $description ) {
            $message .= ' (' . $description . ')';
        }

        wp_send_json_success( array(
            'message' => $message,
            'coupon_id' => $coupon_id,
            'amount' => $amount,
            'discount_type' => $discount_type,
            'expiry_date' => $date_expires ? $date_expires->date_i18n( 'Y-m-d' ) : '',
            'products' => $this->get_coupon_products_data( $code )
        ) );
    }

    /**
     * Get subscriber count
     */
    public function get_subscriber_count() {
        check_ajax_referer( 'alertx_new_campaign', 'nonce' );

        global $wpdb;
        $table_name = esc_sql( $wpdb->prefix . 'alertx_subscriptions' );

        $count = $wpdb->get_var(
            "SELECT COUNT(DISTINCT email) FROM $table_name WHERE status = 'confirmed'"
        );

        wp_send_json_success( array(
            'count' => intval( $count )
        ) );
    }

    /**
     * Get recipient emails by merging ALL recipient sources.
     *
     * A campaign email goes to every unique address found in any of the
     * three sources:
     * 1. Confirmed AlertX subscribers
     * 2. Manually added "Specific Emails" (may be non-subscribers)
     * 3. CSV imported addresses (may be non-subscribers)
     *
     * Subscription status only gates source 1; sources 2 and 3 receive the
     * campaign regardless of whether the address exists as a subscriber.
     * Every address is validated/normalized and duplicates (case
     * insensitive) are removed so each unique email is sent exactly once.
     *
     * @param string $recipient_type      Kept for caller compatibility (no longer filters the sources).
     * @param string $recipient_csv_data  JSON array of CSV imported emails.
     * @param string $recipient_emails    JSON array of manually added emails.
     * @return array Unique, valid recipient email addresses.
     */
    private function get_recipient_emails( $recipient_type, $recipient_csv_data, $recipient_emails ) {
        global $wpdb;

        $emails = array();

        // Source 1: confirmed AlertX subscribers.
        $table_name = esc_sql( $wpdb->prefix . 'alertx_subscriptions' );

        $subscribers = $wpdb->get_col(
            "SELECT DISTINCT email FROM $table_name WHERE status = 'confirmed'"
        );

        if ( is_array( $subscribers ) ) {
            $emails = array_merge( $emails, $subscribers );
        }

        // Source 2: manually added specific emails (may be non-subscribers).
        $specific = json_decode( (string) $recipient_emails, true );

        if ( is_array( $specific ) ) {
            $emails = array_merge( $emails, $specific );
        }

        // Source 3: CSV imported emails (may be non-subscribers).
        $csv_emails = json_decode( (string) $recipient_csv_data, true );

        if ( is_array( $csv_emails ) ) {
            $emails = array_merge( $emails, $csv_emails );
        }

        // Validate + normalize: drop empty/malformed rows so they neither
        // count as recipients nor burn a "sent" slot. Keying by the
        // lowercase address removes duplicates that differ only in case
        // ("User@Example.com" vs "user@example.com").
        $normalized = array();

        foreach ( $emails as $email ) {
            if ( ! is_string( $email ) ) {
                continue;
            }

            $email = sanitize_email( trim( $email ) );

            if ( empty( $email ) || ! is_email( $email ) ) {
                continue;
            }

            if ( ! isset( $normalized[ strtolower( $email ) ] ) ) {
                $normalized[ strtolower( $email ) ] = $email;
            }
        }

        return array_values( $normalized );
    }

    /**
     * Validate WooCommerce coupon
     *
     * Only checks that the coupon EXISTS in WooCommerce. Full checkout
     * validity rules (expiry, usage limits, restrictions) are enforced by
     * WooCommerce itself when customers apply the code - blocking campaign
     * creation over them caused valid campaigns to silently fail to save.
     */
    private function validate_wc_coupon( $code ) {
        try {
            $coupon = new \WC_Coupon( $code );
            return (bool) $coupon->get_id();
        } catch ( \Exception $e ) {
            return false;
        }
    }

    /**
     * Build wpdb format array matching the given campaign data keys.
     * Keeps INSERT/UPDATE formats in sync with dynamic field sets.
     */
    private function get_campaign_field_formats( $campaign_data ) {
        $formats = array();

        foreach ( (array) $campaign_data as $field => $value ) {
            $formats[] = ( 'total_recipients' === $field ) ? '%d' : '%s';
        }

        return $formats;
    }

    /**
     * Get replacement text for {discount_expiry} placeholder
     */
    private function get_expiry_placeholder_text( $expiry ) {
        if ( empty( $expiry ) ) {
            return '';
        }

        $timestamp = strtotime( $expiry );

        if ( ! $timestamp ) {
            return '';
        }

        return 'Valid until: ' . date_i18n( get_option( 'date_format' ), $timestamp );
    }

    /**
     * Get replacement text for {discount_amount} placeholder
     */
    private function get_discount_placeholder_text( $code ) {
        if ( empty( $code ) || ! class_exists( 'WooCommerce' ) ) {
            return '';
        }

        try {
            $coupon = new \WC_Coupon( $code );
        } catch ( \Exception $e ) {
            return '';
        }

        if ( ! $coupon->get_id() ) {
            return '';
        }

        $amount = $coupon->get_amount();

        if ( $amount <= 0 ) {
            return '';
        }

        switch ( $coupon->get_discount_type() ) {
            case 'percent':
                return sprintf( 'Get %s%% off!', wc_format_localized_price( $amount ) );
            case 'fixed_cart':
                return sprintf( 'Get %s off!', $this->format_coupon_price( $amount ) );
            case 'fixed_product':
                return sprintf( 'Get %s off each product!', $this->format_coupon_price( $amount ) );
            default:
                return '';
        }
    }

    /**
     * Format a price with currency symbol for discount placeholder
     */
    private function format_coupon_price( $amount ) {
        $symbol = html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' );

        return $symbol . wc_format_localized_price( $amount );
    }

    /**
     * Get WooCommerce shop page URL
     */
    private function get_shop_page_url() {
        if ( class_exists( 'WooCommerce' ) && function_exists( 'wc_get_page_permalink' ) ) {
            return wc_get_page_permalink( 'shop' );
        }

        return home_url( '/shop/' );
    }

    /**
     * Get HTML for the {cta_button} placeholder.
     * Shown when the coupon has no restricted products; links to the shop page.
     */
    private function get_cta_button_html( $template_id = '' ) {
        $label = __( 'All Products Discount', 'alertx-pro' );

        switch ( $template_id ) {
            case 'modern':
                $style = 'display: inline-block; padding: 16px 40px; background-color: #3c06c5; background-image: linear-gradient(135deg, #3c06c5, #3c06c5); color: #ffffff; text-decoration: none; border-radius: 30px; font-weight: 700; font-size: 16px;';
                break;
            case 'product':
                $style = 'display: inline-block; padding: 14px 32px; background: #23282d; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600;';
                break;
            default:
                $style = 'display: inline-block; padding: 14px 32px; background: #3c06c5; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600;';
        }

        return '<a href="' . esc_url( $this->get_shop_page_url() ) . '" style="' . $style . '">' . esc_html( $label ) . '</a>';
    }

    /**
     * Get product card HTML for the {product_list} placeholder.
     *
     * Builds "Image + Name + Price + Buy Now" cards from the products
     * restricted to the coupon. Empty when the coupon has no product
     * restrictions (the {cta_button} shop link is used instead).
     */
    private function get_coupon_products_cards_html( $code ) {
        if ( empty( $code ) || ! class_exists( 'WooCommerce' ) ) {
            return '';
        }

        try {
            $coupon = new \WC_Coupon( $code );
        } catch ( \Exception $e ) {
            return '';
        }

        if ( ! $coupon->get_id() ) {
            return '';
        }

        $product_ids = $coupon->get_product_ids();

        if ( empty( $product_ids ) || ! is_array( $product_ids ) ) {
            return '';
        }

        $symbol = html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' );
        $cards  = '';

        foreach ( $product_ids as $product_id ) {
            $product = wc_get_product( intval( $product_id ) );

            if ( ! $product || 'publish' !== $product->get_status() ) {
                continue;
            }

            // Product image URL
            $image_url = '';
            if ( $product->get_image_id() ) {
                $image_src = wp_get_attachment_image_src( $product->get_image_id(), 'woocommerce_thumbnail' );
                if ( $image_src ) {
                    $image_url = $image_src[0];
                }
            }

            $name   = $product->get_name();
            $price  = $symbol . wc_format_localized_price( wc_get_price_to_display( $product ) );
            $url    = get_permalink( $product->get_id() );

            $cards .= '<div style="display: inline-block; width: 46%; vertical-align: top; margin: 0 4% 16px 0; border: 1px solid #e8e8e8; border-radius: 8px; padding: 14px; text-align: center; background-color: #ffffff;">';

            if ( $image_url ) {
                $cards .= '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $name ) . '" width="140" style="max-width: 100%; height: auto; margin: 0 auto 10px; display: block;" />';
            }

            $cards .= '<div style="font-size: 14px; font-weight: 600; color: #333333; line-height: 1.4; margin-bottom: 6px;">' . esc_html( $name ) . '</div>';
            $cards .= '<div style="font-size: 16px; font-weight: 700; color: #3c06c5; margin-bottom: 12px;">' . esc_html( $price ) . '</div>';

            if ( ! empty( $url ) ) {
                $cards .= '<a href="' . esc_url( $url ) . '" style="display: inline-block; padding: 9px 24px; background: #3c06c5; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 13px;">' . __( 'Buy Now', 'alertx-pro' ) . '</a>';
            }

            $cards .= '</div>';
        }

        return $cards;
    }

    /**
     * Count the products restricted to a coupon - mirrors
     * get_coupon_products_cards_html() (only published, existing products
     * are counted, exactly the ones the email will show).
     */
    private function get_coupon_product_count( $code ) {
        if ( empty( $code ) || ! class_exists( 'WooCommerce' ) ) {
            return 0;
        }

        try {
            $coupon = new \WC_Coupon( $code );
        } catch ( \Exception $e ) {
            return 0;
        }

        if ( ! $coupon->get_id() ) {
            return 0;
        }

        $product_ids = $coupon->get_product_ids();

        if ( empty( $product_ids ) || ! is_array( $product_ids ) ) {
            return 0;
        }

        $count = 0;

        foreach ( $product_ids as $product_id ) {
            $product = wc_get_product( intval( $product_id ) );

            if ( $product && 'publish' === $product->get_status() ) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get coupon-restricted products data for the admin preview
     */
    private function get_coupon_products_data( $code ) {
        if ( empty( $code ) || ! class_exists( 'WooCommerce' ) ) {
            return array();
        }

        try {
            $coupon = new \WC_Coupon( $code );
        } catch ( \Exception $e ) {
            return array();
        }

        if ( ! $coupon->get_id() ) {
            return array();
        }

        $product_ids = $coupon->get_product_ids();

        if ( empty( $product_ids ) || ! is_array( $product_ids ) ) {
            return array();
        }

        $symbol = html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' );
        $items  = array();

        foreach ( $product_ids as $product_id ) {
            $product = wc_get_product( intval( $product_id ) );

            if ( ! $product || 'publish' !== $product->get_status() ) {
                continue;
            }

            $image_url = '';
            if ( $product->get_image_id() ) {
                $image_src = wp_get_attachment_image_src( $product->get_image_id(), 'woocommerce_thumbnail' );
                if ( $image_src ) {
                    $image_url = $image_src[0];
                }
            }

            $items[] = array(
                'id'    => $product->get_id(),
                'name'  => $product->get_name(),
                'price' => $symbol . wc_format_localized_price( wc_get_price_to_display( $product ) ),
                'image' => $image_url,
                'url'   => get_permalink( $product->get_id() ),
            );
        }

        return $items;
    }

    /**
     * Send campaign emails
     */
    private function send_campaign_emails( $campaign_id, $recipients, $campaign_data ) {
        $stats = array( 'sent' => 0, 'failed' => 0 );

        // Precompute coupon placeholders once (same for every recipient)
        $expiry_text   = $this->get_expiry_placeholder_text( $campaign_data['discount_expiry'] );
        $discount_text = $this->get_discount_placeholder_text( $campaign_data['discount_code'] );

        // Coupon driven placeholders:
        // - coupon has restricted products -> product cards (image/name/price/Buy Now)
        // - no restricted products         -> CTA button linking to the shop page
        $product_list_html = $this->get_coupon_products_cards_html( $campaign_data['discount_code'] );

        if ( '' !== $product_list_html ) {
            $cta_button_html = '';
        } else {
            $cta_button_html = $this->get_cta_button_html( isset( $campaign_data['email_template_id'] ) ? $campaign_data['email_template_id'] : '' );
        }

        foreach ( $recipients as $email ) {
            $first_name = $this->extract_first_name( $email );

            // Prepare message with placeholders
            $message = $campaign_data['camp_message'];
            $message = str_replace( '{campaign_title}', sanitize_text_field( isset( $campaign_data['camp_title'] ) ? $campaign_data['camp_title'] : '' ), $message );
            $message = str_replace( '{first_name}', $first_name, $message );
            $message = str_replace( '{discount_code}', $campaign_data['discount_code'], $message );
            $message = str_replace( '{discount_amount}', $discount_text, $message );
            $message = str_replace( '{discount_expiry}', $expiry_text, $message );
            $message = str_replace( '{site_name}', get_bloginfo( 'name' ), $message );

            // Product placeholders
            $message = str_replace( '{cta_button}', $cta_button_html, $message );
            $message = str_replace( '{product_list}', $product_list_html, $message );

            // Legacy {product_url} support for older/custom templates
            if ( $campaign_data['product_selection'] === 'specific' ) {
                $products = json_decode( $campaign_data['selected_products'], true );
                if ( ! empty( $products ) ) {
                    $first_product = $products[0];
                    $product = wc_get_product( $first_product['id'] );
                    if ( $product ) {
                        $message = str_replace( '{product_name}', $product->get_name(), $message );
                        $message = str_replace( '{product_url}', get_permalink( $product->get_id() ), $message );
                    }
                }
            } else {
                $message = str_replace( '{product_url}', $this->get_shop_page_url(), $message );
            }

            // Send email
            $sent = $this->send_campaign_email(
                $email,
                $campaign_data['camp_subject'],
                $message,
                isset( $campaign_data['email_template_id'] ) ? $campaign_data['email_template_id'] : ''
            );

            if ( $sent ) {
                $stats['sent']++;
            } else {
                $stats['failed']++;
            }
        }

        return $stats;
    }

    /**
     * Schedule campaign using WP-Cron
     */
    private function schedule_campaign( $campaign_id, $scheduled_date ) {
        // Convert scheduled date to timestamp
        $timestamp = strtotime( $scheduled_date );

        // Schedule the event
        if ( ! wp_next_scheduled( 'alertx_send_scheduled_campaign', array( $campaign_id ) ) ) {
            wp_schedule_single_event( $timestamp, 'alertx_send_scheduled_campaign', array( $campaign_id ) );
        }
    }

    /**
     * Entry point for scheduled campaigns: flip status, then start
     * the same chunked background dispatcher.
     */
    public function start_scheduled_campaign( $campaign_id ) {
        global $wpdb;
        $table_name = esc_sql( $wpdb->prefix . 'alertx_campaigns' );
        $campaign_id = intval( $campaign_id );

        $campaign = $wpdb->get_row(
            $wpdb->prepare( "SELECT camp_status FROM $table_name WHERE id = %d", $campaign_id ),
            ARRAY_A
        );

        if ( ! $campaign || ! in_array( $campaign['camp_status'], array( 'scheduled', 'sending' ), true ) ) {
            return;
        }

        // Queue the first chunk
        wp_schedule_single_event( time() + 5, 'alertx_dispatch_campaign_send', array( $campaign_id, 0 ) );
        spawn_cron();
    }

    /**
     * Send campaign emails in small batches from WP-Cron so we never hold a
     * long-running HTTP request open. Each tick processes BATCH_SIZE emails,
     * saves progress to the DB and schedules the next tick until done.
     */
    public function dispatch_campaign_send( $campaign_id, $offset = 0 ) {
        global $wpdb;

        $table_name = esc_sql( $wpdb->prefix . 'alertx_campaigns' );
        $batch_size = apply_filters( 'alertx_campaign_batch_size', 25 );
        $campaign_id = intval( $campaign_id );
        $offset = max( 0, intval( $offset ) );

        $campaign = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $campaign_id ),
            ARRAY_A
        );

        if ( ! $campaign || ! in_array( $campaign['camp_status'], array( 'sending', 'scheduled' ), true ) ) {
            return;
        }

        // Prevent overlapping ticks for the same campaign
        $lock_key = 'alertx_sending_lock_' . $campaign_id;

        if ( get_transient( $lock_key ) ) {
            // Still processing elsewhere - try again in 2 minutes
            wp_schedule_single_event( time() + 120, 'alertx_dispatch_campaign_send', array( $campaign_id, $offset ) );
            return;
        }

        set_transient( $lock_key, time(), 5 * MINUTE_IN_SECONDS );

        if ( 'scheduled' === $campaign['camp_status'] ) {
            $wpdb->update(
                $table_name,
                array( 'camp_status' => 'sending' ),
                array( 'id' => $campaign_id ),
                array( '%s' ),
                array( '%d' )
            );
            $campaign['camp_status'] = 'sending';
        }

        $recipients = array_values( $this->get_recipient_emails(
            $campaign['recipient_type'],
            $campaign['recipient_csv_data'],
            $campaign['recipient_emails']
        ) );

        $batch = array_slice( $recipients, $offset, $batch_size );

        if ( empty( $batch ) ) {
            // All done - finalize
            $wpdb->update(
                $table_name,
                array(
                    'camp_status' => 'sent',
                    'sent_at' => current_time( 'mysql' ),
                ),
                array( 'id' => $campaign_id ),
                array( '%s', '%s' ),
                array( '%d' )
            );

            delete_transient( $lock_key );
            return;
        }

        // Only the fields send_campaign_emails() needs
        $send_data = array(
            'camp_title' => isset( $campaign['camp_title'] ) ? $campaign['camp_title'] : '',
            'camp_subject' => isset( $campaign['camp_subject'] ) ? $campaign['camp_subject'] : '',
            'camp_message' => isset( $campaign['camp_message'] ) ? $campaign['camp_message'] : '',
            'discount_code' => isset( $campaign['discount_code'] ) ? $campaign['discount_code'] : '',
            'discount_expiry' => isset( $campaign['discount_expiry'] ) ? $campaign['discount_expiry'] : '',
            'email_template_id' => isset( $campaign['email_template_id'] ) ? $campaign['email_template_id'] : '',
            'product_selection' => isset( $campaign['product_selection'] ) ? $campaign['product_selection'] : 'all',
            'selected_products' => isset( $campaign['selected_products'] ) ? $campaign['selected_products'] : '',
        );

        $stats = $this->send_campaign_emails( $campaign_id, $batch, $send_data );

        // Persist batch progress
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$table_name} SET emails_sent = emails_sent + %d, emails_failed = emails_failed + %d WHERE id = %d",
                $stats['sent'],
                $stats['failed'],
                $campaign_id
            )
        );

        delete_transient( $lock_key );

        $next_offset = $offset + count( $batch );

        if ( $next_offset < count( $recipients ) ) {
            // More batches left - schedule the next tick
            wp_schedule_single_event( time() + 15, 'alertx_dispatch_campaign_send', array( $campaign_id, $next_offset ) );
            spawn_cron();
        } else {
            // Last batch just went out - finalize now. Without this the
            // campaign would stay stuck on "sending" forever, because the
            // empty-batch finalize below only runs if another tick happens.
            $wpdb->update(
                $table_name,
                array(
                    'camp_status' => 'sent',
                    'sent_at'     => current_time( 'mysql' ),
                ),
                array( 'id' => $campaign_id ),
                array( '%s', '%s' ),
                array( '%d' )
            );
        }
    }

    /**
     * Render the final email HTML for a campaign based on its saved template.
     *
     * Campaigns saved through the campaign builder always carry one of the
     * known template IDs (default, modern, minimal, product). For those, the
     * saved message body already IS the selected template's markup (with
     * placeholders resolved by the caller), so it is rendered as-is - this
     * is exactly the design the admin picked and previewed while creating
     * the campaign.
     *
     * Only legacy rows without a recognizable template fall back to the
     * generic wrapper template.
     *
     * @param string $template_id Saved email_template_id of the campaign.
     * @param string $subject     Campaign subject (used by the legacy wrapper).
     * @param string $message     Message body with placeholders already resolved.
     * @return string Final email HTML.
     */
    private function get_campaign_email_html( $template_id, $subject, $message ) {
        if ( in_array( $template_id, array( 'default', 'modern', 'minimal', 'product' ), true ) ) {
            return wp_kses_post( $message );
        }

        return $this->get_email_template( $subject, $message );
    }

    /**
     * Get email HTML template
     */
    private function get_email_template( $subject, $message ) {
        $message = wpautop( $message );

        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo esc_html( $subject ); ?></title>
        </head>
        <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
            <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 4px; overflow: hidden;">
                <!-- Header -->
                <div style="background-color: #3c06c5; padding: 25px 30px; text-align: center;">
                    <h1 style="margin: 0; color: #ffffff; font-size: 22px; font-weight: bold;">
                        <?php echo esc_html( get_bloginfo( 'name' ) ); ?>
                    </h1>
                </div>

                <!-- Content -->
                <div style="padding: 30px 30px 25px; color: #333333; line-height: 1.6; font-size: 14px;">
                    <h2 style="margin: 0 0 15px 0; color: #3c06c5; font-size: 20px;">
                        <?php echo esc_html( $subject ); ?>
                    </h2>
                    <?php echo wp_kses_post( $message ); ?>
                </div>

                <!-- Footer -->
                <div style="background-color: #f8f8f8; padding: 20px 30px; text-align: center; border-top: 1px solid #e0e0e0;">
                    <p style="margin: 0 0 8px; color: #666666; font-size: 12px;">
                        You received this email because you subscribed to notifications on our website.
                    </p>
                    <p style="margin: 0; color: #999999; font-size: 11px;">
                        © <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?>. All rights reserved.
                    </p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, PluginCheck.Security.DirectDB.UnescapedDBParameter
