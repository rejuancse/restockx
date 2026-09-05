<?php
/**
 * AlertX Pro admin menu and front-end notification handlers.
 *
 * @package Alertx\Admin
 */

namespace Alertx\Admin;

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom tables are queried with $wpdb->prepare(); these low-frequency admin queries intentionally bypass the object cache.

/**
 * Class Alertx_Menu
 *
 * Handles the admin menu for stock notifications and associated functionalities.
 */
class Alertx_Menu {

	use \Alertx\Admin\Pages\Dashboard_Page;
	use \Alertx\Admin\Pages\Subscribers_Page;
	use \Alertx\Admin\Pages\Campaigns_Page;
	use \Alertx\Admin\Pages\New_Campaign_Page;
	use \Alertx\Admin\Pages\Email_Templates_Page;
	use \Alertx\Admin\Pages\Settings_Page;

	/**
	 * Singleton instance of the class.
	 *
	 * @var Alertx_Menu|null
	 */
	private static $instance = null;

	/**
	 * Retrieves the singleton instance of the class.
	 *
	 * @return Alertx_Menu Singleton instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor for initializing hooks and actions for the plugin.
	 *
	 * This method registers various WordPress and WooCommerce actions that
	 * connect plugin functionality to WordPress events and AJAX requests.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'wp_ajax_stock_notification', array( $this, 'handle_stock_notification' ) );
		add_action( 'wp_ajax_nopriv_stock_notification', array( $this, 'handle_stock_notification' ) );
		// Double opt-in confirmation endpoint.
		add_action( 'wp_ajax_stock_confirm_subscription', array( $this, 'confirm_subscription' ) );
		add_action( 'wp_ajax_nopriv_stock_confirm_subscription', array( $this, 'confirm_subscription' ) );
		// Unsubscribe endpoint.
		add_action( 'wp_ajax_stock_unsubscribe', array( $this, 'unsubscribe' ) );
		add_action( 'wp_ajax_nopriv_stock_unsubscribe', array( $this, 'unsubscribe' ) );
		// Trigger notifications when stock status changes on products.
		add_action( 'woocommerce_product_set_stock_status', array( $this, 'send_alertx_subscriptions' ), 10, 3 );
		// Ensure variations also trigger notifications when stock/status changes.
		add_action( 'woocommerce_variation_set_stock_status', array( $this, 'send_alertx_subscriptions' ), 10, 3 );
		// Trigger threshold-based checks when quantity is set on products and variations.
		add_action( 'woocommerce_product_set_stock', array( $this, 'check_stock_and_notify' ) );
		add_action( 'woocommerce_variation_set_stock', array( $this, 'check_stock_and_notify' ) );
		add_action( 'admin_init', array( $this, 'handle_bulk_action_alertx_subscriptions' ) );

		// Track WooCommerce orders for restocked products - use proper hooks.
		add_action( 'woocommerce_order_status_completed', array( $this, 'track_restock_sales' ), 10, 1 );
		add_action( 'woocommerce_order_status_processing', array( $this, 'track_restock_sales' ), 10, 1 );

		// Debug action to manually test restock tracking (access via: /wp-admin/admin-ajax.php?action=alertx_debug_restock).
		add_action( 'wp_ajax_alertx_debug_restock', array( $this, 'debug_restock_tracking' ) );
		add_action( 'wp_ajax_alertx_manual_track_order', array( $this, 'manual_track_order' ) );
		add_action( 'wp_ajax_alertx_auto_fix', array( $this, 'auto_fix_restock_tracking' ) );
		add_action( 'wp_ajax_alertx_test_tracking', array( $this, 'test_tracking' ) );

		// Settings page — save "Notify Me" button options.
		add_action( 'wp_ajax_alertx_save_settings', array( $this, 'save_notify_me_settings' ) );
		add_action( 'admin_init', array( $this, 'alertx_page_hide_notifications' ) );
	}

	/**
	 * Adds the admin menu items for the stock notifications plugin.
	 *
	 * This method creates the main menu and a submenu under the WordPress admin dashboard.
	 * It defines the menu pages and their corresponding callback functions.
	 *
	 * @return void
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'AlertX', 'alertx-pro' ),
			__( 'AlertX', 'alertx-pro' ),
			'manage_options',
			'alertx-pro',
			array( $this, 'alertx_admin_dashboard' ),
			ALERTX_URL . 'assets/images/icon.png'
		);

		add_submenu_page(
			'alertx-pro',
			__( 'Subscribers', 'alertx-pro' ),
			__( 'Subscribers', 'alertx-pro' ),
			'manage_options',
			'subscribers',
			array( $this, 'alertx_subscribers' )
		);

		add_submenu_page(
			'alertx-pro',
			__( 'Email Templates', 'alertx-pro' ),
			__( 'Email Templates', 'alertx-pro' ),
			'manage_options',
			'email-templates',
			array( $this, 'alertx_email_templates' )
		);

		add_submenu_page(
			'alertx-pro',
			__( 'Campaigns', 'alertx-pro' ),
			__( 'Campaigns', 'alertx-pro' ),
			'manage_options',
			'campaigns',
			array( $this, 'alertx_campaigns' )
		);

		add_submenu_page(
			'alertx-pro',
			__( 'New Campaign', 'alertx-pro' ),
			__( 'New Campaign', 'alertx-pro' ),
			'manage_options',
			'new-campaign',
			array( $this, 'alertx_new_campaign' )
		);

		add_submenu_page(
			'alertx-pro',
			__( 'Settings', 'alertx-pro' ),
			__( 'Settings', 'alertx-pro' ),
			'manage_options',
			'alertx-settings',
			array( $this, 'alertx_settings' )
		);
	}

	/**
	 * Handles the stock notification AJAX request.
	 *
	 * Sanitizes and validates the email and product ID from the request.
	 * Checks if the request is rate-limited.
	 * Checks for existing notifications and either renews or creates a new one.
	 * Sends appropriate JSON response based on the outcome.
	 */
	public function handle_stock_notification() {
		// Verify nonce for AJAX requests.
		$nonce      = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		$product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
		$parent_id  = isset( $_POST['parent_id'] ) ? absint( wp_unslash( $_POST['parent_id'] ) ) : 0;

		if ( empty( $nonce ) ) {
			wp_send_json_error( __( 'Security token is missing. Please refresh the page and try again.', 'alertx-pro' ) );
		}

		// For variable products, use parent_id for nonce verification.
		// For simple products, product_id is used.
		$nonce_product_id = $parent_id > 0 ? $parent_id : $product_id;

		// Verify nonce with product-specific action.
		$nonce_action = 'alertx_notify_me_' . $nonce_product_id;
		if ( ! wp_verify_nonce( $nonce, $nonce_action ) ) {
			wp_send_json_error( __( 'Security check failed. Please refresh the page and try again.', 'alertx-pro' ) );
		}

		// Validate product ID.
		if ( empty( $product_id ) ) {
			wp_send_json_error( __( 'Error: Product/Variation ID not found. Please select a variation (if applicable) or refresh the page and try again.', 'alertx-pro' ) );
		}

		if ( isset( $_POST['email'] ) && $product_id ) {
			// Sanitize and validate inputs.
			$email      = $this->sanitize_and_validate_email( sanitize_email( wp_unslash( $_POST['email'] ) ) );
			$product_id = $this->sanitize_and_validate_product_id( $product_id );

			// Check rate limiting to prevent multiple requests.
			if ( $this->is_rate_limited( $email ) ) {
				wp_send_json_error( __( 'Too many requests. Please try again later.', 'alertx-pro' ) );
			}

			// Check for existing notification.
			$existing_notification = $this->get_existing_notification( $email, $product_id );

			if ( $existing_notification ) {
				$this->handle_existing_notification( $existing_notification, $product_id );
			} else {
				$this->create_new_notification( $email, $product_id );
			}
		}
	}

	/**
	 * Sanitizes and validates the email address.
	 *
	 * @param string $email The email address to be validated.
	 * @return string The sanitized and validated email address.
	 * @throws WP_Error If the email address is invalid.
	 */
	private function sanitize_and_validate_email( $email ): string {
		$email = sanitize_email( $email );

		if ( ! is_email( $email ) ) {
			wp_send_json_error( __( 'Invalid email address', 'alertx-pro' ) );
		}

		return $email;
	}

	/**
	 * Sanitizes and validates the product ID.
	 *
	 * @param mixed $product_id The product ID to be validated.
	 * @return int The validated product ID.
	 * @throws WP_Error If the product ID is invalid.
	 */
	private function sanitize_and_validate_product_id( $product_id ): int {
		$product_id = intval( $product_id );

		if ( $product_id <= 0 ) {
			wp_send_json_error( __( 'Invalid product ID', 'alertx-pro' ) );
		}

		return $product_id;
	}

	/**
	 * Retrieves an existing notification based on email and product ID.
	 *
	 * @param string $email The email address of the user.
	 * @param int    $product_id The ID of the product.
	 * @return object|null The notification record or null if not found.
	 */
	private function get_existing_notification( string $email, int $product_id ): ?object {
		global $wpdb;
		$table_name = esc_sql( $wpdb->prefix . 'alertx_subscriptions' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM `$table_name` WHERE email = %s AND product_id = %d",
				$email,
				$product_id
			)
		);
	}

	/**
	 * Handles an existing notification by renewing it if it's older than 24 hours.
	 *
	 * @param object $existing_notification The existing notification record.
	 * @param int    $product_id The ID of the product for which notifications are managed.
	 */
	private function handle_existing_notification( object $existing_notification, int $product_id ) {
		// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- Compared against current_time( 'mysql' ) values stored in the site-local timezone.
		$time_difference = current_time( 'timestamp' ) - strtotime( $existing_notification->date_added );

		// If the existing notification is within the last 24 hours.
		if ( $time_difference < 24 * 60 * 60 ) {
			wp_send_json_error( __( 'You have already subscribed to notifications for this product.', 'alertx-pro' ) );
		}

		// Renew the subscription.
		$this->renew_notification( $existing_notification->id );
		wp_send_json_success(
			array(
				'message'      => __( 'Your notification subscription has been renewed for this product.', 'alertx-pro' ),
				'alternatives' => $this->get_alternative_products( $product_id ),
			)
		);
	}

	/**
	 * Renews the notification by updating the date_added field.
	 *
	 * @param int $notification_id Notification ID to be renewed.
	 */
	private function renew_notification( int $notification_id ) {
		global $wpdb;
		$table_name = esc_sql( $wpdb->prefix . 'alertx_subscriptions' );

		// Update the notification's date_added field.
		$wpdb->update(
			$table_name,
			array( 'date_added' => current_time( 'mysql' ) ),
			array( 'id' => intval( $notification_id ) ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Creates a new stock notification and sends a confirmation message.
	 *
	 * @param string $email User's email address.
	 * @param int    $product_id Product ID.
	 */
	private function create_new_notification( string $email, int $product_id ) {
		global $wpdb;
		$table_name = esc_sql( $wpdb->prefix . 'alertx_subscriptions' );

		// Check if double opt-in confirmation is required.
		$require_confirmation = get_option( 'alertx_require_confirmation', '1' ) === '1';

		// Generate confirmation token (always generate for unsubscribe functionality).
		$token = wp_generate_password( 32, false, false );

		// Set status based on confirmation requirement.
		$status = $require_confirmation ? 'pending' : 'confirmed';

		// Insert notification into database.
		$wpdb->insert(
			$table_name,
			array(
				'email'      => sanitize_email( $email ),
				'product_id' => intval( $product_id ),
				'date_added' => current_time( 'mysql' ),
				'status'     => $status,
				'token'      => $token,
			),
			array( '%s', '%d', '%s', '%s', '%s' )
		);

		// Send confirmation email only if confirmation is required.
		if ( $require_confirmation ) {
			$this->send_confirmation_email( $email, $product_id, $token );
			// Return JSON to frontend with confirmation message.
			$this->send_subscription_confirmation( $product_id );
		} else {
			// No double opt-in: send a subscription success email.
			$this->send_subscription_success_email( $email, $product_id );

			// Skip confirmation, send immediate success message.
			$product      = wc_get_product( $product_id );
			$product_name = $product ? $product->get_name() : __( 'this product', 'alertx-pro' );

			$response_data = array(
				'message'      => sprintf(
					/* translators: %s: product name */
					__( 'You have been subscribed to notifications for <strong>%s</strong>. We will notify you when it is back in stock.', 'alertx-pro' ),
					esc_html( $product_name )
				),
				'alternatives' => $this->get_alternative_products( $product_id ),
			);

			wp_send_json_success( $response_data );
		}
	}

	/**
	 * Sends a JSON response confirming the subscription and providing product alternatives.
	 *
	 * This method generates a JSON response to confirm the user's subscription to stock notifications
	 * for a specified product. It includes a thank you message and a list of alternative products.
	 *
	 * @param int $product_id The ID of the product for which the subscription is made.
	 * @return void
	 */
	private function send_subscription_confirmation( int $product_id ) {
		// Retrieve the product object from the product ID.
		$product = wc_get_product( $product_id );

		// Determine the product name, defaulting to 'this product' if not found.
		$product_name = $product ? $product->get_name() : __( 'this product', 'alertx-pro' );

		$response_data = array(
			'message'      => sprintf(
				/* translators: %s: product name */
				__( 'Almost done! Confirm your subscription via email to get notified when <strong>%s</strong> is back in stock.', 'alertx-pro' ),
				esc_html( $product_name )
			),
			'alternatives' => $this->get_alternative_products( $product_id ),
		);

		// Translators: %d is the number of alternative products.
		wp_send_json_success( $response_data );
	}

	/**
	 * Send double opt-in confirmation email with confirm link.
	 *
	 * @param string $email      Subscriber email address.
	 * @param int    $product_id Product ID.
	 * @param string $token      Confirmation token.
	 */
	private function send_confirmation_email( string $email, int $product_id, string $token ) {
		$product      = wc_get_product( $product_id );
		$product_name = $product ? $product->get_name() : __( 'this product', 'alertx-pro' );

		// Build confirmation URL using AJAX endpoint.
		$confirm_url = add_query_arg(
			array(
				'action' => 'stock_confirm_subscription',
				'token'  => rawurlencode( $token ),
				'pid'    => intval( $product_id ),
			),
			admin_url( 'admin-ajax.php' )
		);

		/* translators: %s: Product name */
		$subject = sprintf( __( 'Confirm your stock alert for %s', 'alertx-pro' ), $product_name );

		// Keep the From address domain-aligned so messages are not dropped.
		// by recipients' providers; external configured addresses become.
		// the Reply-To instead.
		$sender    = \Alertx\Admin::resolve_email_sender();
		$from_name = get_bloginfo( 'name' ) . ' ' . __( 'Stock Alerts', 'alertx-pro' );

		// HTML message for better client compatibility.
		$message = sprintf(
			/* translators: 1: Product name, 2: Confirmation URL */
			__( 'Please confirm your subscription to be notified when %1$s is back in stock. <a href="%2$s">Click here to confirm</a>.', 'alertx-pro' ),
			esc_html( $product_name ),
			esc_url( $confirm_url )
		);

		$headers = array(
			'MIME-Version: 1.0',
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . esc_html( $from_name ) . ' <' . sanitize_email( $sender['from'] ) . '>',
		);

		if ( '' !== $sender['reply_to'] ) {
			$headers[] = 'Reply-To: ' . sanitize_email( $sender['reply_to'] );
		}

		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_mail_wp_mail -- Transactional stock-alert emails are the core feature of this plugin; wp_mail is the intended transport.
		wp_mail( sanitize_email( $email ), $subject, $message, $headers );
	}

	/**
	 * Send a subscription success email when double opt-in is disabled.
	 *
	 * Without double opt-in the subscriber is confirmed instantly, so this
	 * email confirms what they signed up for and sets the expectation that
	 * the restock alert will follow.
	 *
	 * @param string $email      Subscriber email address.
	 * @param int    $product_id Product ID.
	 */
	private function send_subscription_success_email( string $email, int $product_id ) {
		$product      = wc_get_product( $product_id );
		$product_name = $product ? $product->get_name() : __( 'this product', 'alertx-pro' );
		$product_url  = $product ? $product->get_permalink() : home_url( '/' );

		/* translators: %s: Product name */
		$subject = sprintf( __( 'You are subscribed: %s stock alerts', 'alertx-pro' ), $product_name );

		$sender    = \Alertx\Admin::resolve_email_sender();
		$from_name = get_bloginfo( 'name' ) . ' ' . __( 'Stock Alerts', 'alertx-pro' );

		$message = sprintf(
			/* translators: 1: Product name, 2: Site name, 3: Product URL */
			__( 'You will receive an email as soon as <strong>%1$s</strong> is back in stock at %2$s.<br><br><a href="%3$s">View product</a>', 'alertx-pro' ),
			esc_html( $product_name ),
			esc_html( get_bloginfo( 'name' ) ),
			esc_url( $product_url )
		);

		$headers = array(
			'MIME-Version: 1.0',
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . esc_html( $from_name ) . ' <' . sanitize_email( $sender['from'] ) . '>',
		);

		if ( '' !== $sender['reply_to'] ) {
			$headers[] = 'Reply-To: ' . sanitize_email( $sender['reply_to'] );
		}

		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_mail_wp_mail -- Transactional stock-alert emails are the core feature of this plugin; wp_mail is the intended transport.
		wp_mail( sanitize_email( $email ), $subject, $message, $headers );
	}

	/**
	 * Confirm subscription via token
	 */
	public function confirm_subscription() {
		// Note: This is a public endpoint accessed via email links.
		// Security is handled via token validation instead of nonce.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public token-verified endpoint accessed via email links; a nonce cannot be supplied.
		if ( empty( $_GET['token'] ) || empty( $_GET['pid'] ) ) {
			wp_die( esc_html__( 'Invalid confirmation request.', 'alertx-pro' ) );
		}

		global $wpdb;
		$table_name = esc_sql( $wpdb->prefix . 'alertx_subscriptions' );
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public token-verified endpoint accessed via email links; a nonce cannot be supplied.
		$token = sanitize_text_field( wp_unslash( $_GET['token'] ) );
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public token-verified endpoint accessed via email links; a nonce cannot be supplied.
		$product_id = absint( wp_unslash( $_GET['pid'] ) );

		$notification = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM `$table_name` WHERE token = %s AND product_id = %d", $token, $product_id )
		);
		if ( ! $notification ) {
			wp_die( esc_html__( 'Subscription not found or already confirmed.', 'alertx-pro' ) );
		}

		// Confirm subscription.
		$wpdb->update( $table_name, array( 'status' => 'confirmed' ), array( 'id' => intval( $notification->id ) ), array( '%s' ), array( '%d' ) );

		wp_die( esc_html__( 'Your subscription has been confirmed. Thank you!', 'alertx-pro' ) );
	}

	/**
	 * Unsubscribe via token
	 */
	public function unsubscribe() {
		// Note: This is a public endpoint accessed via email links.
		// Security is handled via token validation instead of nonce.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public token-verified endpoint accessed via email links; a nonce cannot be supplied.
		if ( empty( $_GET['token'] ) || empty( $_GET['pid'] ) ) {
			wp_die( esc_html__( 'Invalid unsubscribe request.', 'alertx-pro' ) );
		}

		global $wpdb;
		$table_name = esc_sql( $wpdb->prefix . 'alertx_subscriptions' );
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public token-verified endpoint accessed via email links; a nonce cannot be supplied.
		$token = sanitize_text_field( wp_unslash( $_GET['token'] ) );
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public token-verified endpoint accessed via email links; a nonce cannot be supplied.
		$product_id = absint( wp_unslash( $_GET['pid'] ) );

		$notification = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM `$table_name` WHERE token = %s AND product_id = %d", $token, $product_id )
		);
		if ( ! $notification ) {
			wp_die( esc_html__( 'Subscription not found.', 'alertx-pro' ) );
		}

		// Update subscription status to 'unsubscribed' instead of deleting.
		$wpdb->update(
			$table_name,
			array( 'status' => 'unsubscribed' ),
			array( 'id' => intval( $notification->id ) ),
			array( '%s' ),
			array( '%d' )
		);

		wp_die( esc_html__( 'You have been unsubscribed from stock alerts.', 'alertx-pro' ) );
	}

	/**
	 * Finds in-stock alternative products sharing the product's categories or tags.
	 *
	 * @param int $product_id Product ID.
	 * @return array[] Alternative product summaries (id, name, url, price, image).
	 */
	private function get_alternative_products( $product_id ) {
		// Get the product and its categories and tags.
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return array(); // Return an empty array if product is not found.
		}

		$category_ids = $product->get_category_ids();
		$tag_ids      = wp_get_post_terms( $product_id, 'product_tag', array( 'fields' => 'ids' ) );

		// Filter out empty arrays to avoid query issues.
		$tax_query = array();

		// Add category filter if categories exist.
		if ( ! empty( $category_ids ) ) {
			$tax_query[] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => $category_ids,
			);
		}

		// Add tag filter if tags exist.
		if ( ! empty( $tag_ids ) ) {
			$tax_query[] = array(
				'taxonomy' => 'product_tag',
				'field'    => 'term_id',
				'terms'    => $tag_ids,
			);
		}

		// Set relation to OR if both category and tag filters exist.
		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'OR';
		}

		// Prepare query arguments to fetch alternative products.
		$args = array(
			'posts_per_page' => 5, // Limit to 5 alternative products.
			'post__not_in'   => array( $product_id ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Excluding only one product is acceptable for this use case.
			'post_status'    => 'publish',
			'post_type'      => 'product',
		);

		// Add tax query if we have filters.
		if ( ! empty( $tax_query ) ) {
			$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Necessary for filtering by category/tag.
		}

		// Add stock status filter - works for both simple and variable products.
		$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Necessary to filter for in-stock products.
			array(
				'relation' => 'OR',
				array(
					'key'   => '_stock_status',
					'value' => 'instock',
				),
				// Also include products without stock status meta (back-compat).
				array(
					'key'     => '_stock_status',
					'compare' => 'NOT EXISTS',
				),
			),
		);

		// Fetch products using WP_Query for better compatibility.
		$query                = new \WP_Query( $args );
		$alternative_products = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$alt_product = wc_get_product( get_the_ID() );

				if ( $alt_product && $alt_product->is_in_stock() ) {
					$image_id  = $alt_product->get_image_id();
					$image_url = $image_id ? wp_get_attachment_url( $image_id ) : false;
					$price     = $alt_product->get_price();

					$alternative_products[] = array(
						'id'    => $alt_product->get_id(),
						'name'  => $alt_product->get_name(),
						'url'   => get_permalink( $alt_product->get_id() ),
						'price' => $price ? (string) $price : '',  // Ensure string type.
						'image' => $image_url ? (string) $image_url : '',  // Ensure string type.
					);
				}

				// Stop if we have enough alternatives.
				if ( count( $alternative_products ) >= 5 ) {
					break;
				}
			}
			wp_reset_postdata();
		}

		return $alternative_products;
	}

	/**
	 * Sends stock notifications to users when product stock status changes.
	 *
	 * @param int    $product_id        Product ID.
	 * @param string $stock_status      New stock status.
	 * @param int    $old_stock_status  Previous stock status (unused; required by the hook signature).
	 */
	public function send_alertx_subscriptions( $product_id, $stock_status = null, $old_stock_status = null ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- The parameter is required by the stock status hook signature.
		// Only send when a product/variation becomes in stock.
		if ( ! empty( $stock_status ) && 'instock' !== $stock_status ) {
			return;
		}

		global $wpdb;
		$table_name = esc_sql( $wpdb->prefix . 'alertx_subscriptions' );

		// Fetch confirmed notifications for the given product ID.
		$notifications = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM `$table_name` WHERE product_id = %d AND status = %s",
				$product_id,
				'confirmed'
			)
		);

		$notification_count = is_array( $notifications ) ? count( $notifications ) : 0;

		// If no confirmed notifications found, exit.
		if ( empty( $notifications ) || 0 === $notification_count ) {
			return;
		}

		// Track restock event for sales tracking.
		$this->track_restock_event( $product_id, $notification_count );

		// Get email templates and product details.
		$email_templates = get_option( 'stock_notification_email_templates', $this->get_default_email_templates() );
		$product         = wc_get_product( $product_id );

		if ( ! $product ) {
			return; // Exit if the product is not found.
		}

		// Prepare email headers with proper sender info and unsubscribe header.

		// Keep the From address domain-aligned so messages are not dropped.
		// by recipients' providers; external configured addresses become.
		// the Reply-To instead.
		$sender    = \Alertx\Admin::resolve_email_sender();
		$from_name = get_bloginfo( 'name' ) . ' ' . __( 'Stock Alerts', 'alertx-pro' );
		$headers   = array(
			'MIME-Version: 1.0',
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . esc_html( $from_name ) . ' <' . sanitize_email( $sender['from'] ) . '>',
		);

		if ( '' !== $sender['reply_to'] ) {
			$headers[] = 'Reply-To: ' . sanitize_email( $sender['reply_to'] );
		}

		// Loop through each notification and send emails.
		foreach ( $notifications as $notification ) {
			$to = sanitize_email( $notification->email );

			$subject = sprintf(
				/* translators: %s: product name */
				__( 'Product Back in Stock: %s', 'alertx-pro' ),
				esc_html( $product->get_name() )
			);

			// Replace placeholders in the email template with actual values and unsubscribe link.
			$unsubscribe_url = add_query_arg(
				array(
					'action' => 'stock_unsubscribe',
					'token'  => rawurlencode( $notification->token ),
					'pid'    => intval( $product_id ),
				),
				admin_url( 'admin-ajax.php' )
			);

			// Build a correct URL for variable products (use parent permalink if variation ID).
			$product_url = '';
			if ( $product->is_type( 'variation' ) ) {
				$parent_id   = method_exists( $product, 'get_parent_id' ) ? $product->get_parent_id() : 0;
				$product_url = $parent_id ? get_permalink( $parent_id ) : get_permalink( $product_id );
			} else {
				$product_url = get_permalink( $product_id );
			}

			$message = str_replace(
				array( '{product_name}', '{product_url}', '{site_name}', '{unsubscribe_url}' ),
				array(
					esc_html( $product->get_name() ),
					esc_url( $product_url ),
					esc_html( get_bloginfo( 'name' ) ),
					esc_url( $unsubscribe_url ),
				),
				wp_kses_post( $email_templates )
			);

			// Add List-Unsubscribe header.
			$headers_with_unsub   = $headers;
			$headers_with_unsub[] = 'List-Unsubscribe: <' . esc_url( $unsubscribe_url ) . '>';

			// Send the email.
			// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_mail_wp_mail -- Transactional stock-alert emails are the core feature of this plugin; wp_mail is the intended transport.
			$sent = wp_mail( $to, $subject, $message, $headers_with_unsub );
		}
	}

	/**
	 * Track restock event for sales tracking.
	 *
	 * @param int $product_id         Product ID.
	 * @param int $notification_count Number of notifications sent.
	 */
	private function track_restock_event( $product_id, $notification_count ) {
		global $wpdb;
		$restock_table = esc_sql( $wpdb->prefix . 'alertx_restock_tracking' );

		// Check if this product already has a restock record.
		$existing = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM `$restock_table` WHERE product_id = %d ORDER BY restock_date DESC LIMIT 1",
				$product_id
			)
		);

		if ( $existing ) {
			// Update existing record.
			$wpdb->update(
				$restock_table,
				array(
					'restock_date'       => current_time( 'mysql' ),
					'notifications_sent' => $notification_count,
				),
				array( 'product_id' => $product_id ),
				array( '%s', '%d' ),
				array( '%d' )
			);
		} else {
			// Insert new restock record.
			$wpdb->insert(
				$restock_table,
				array(
					'product_id'         => $product_id,
					'restock_date'       => current_time( 'mysql' ),
					'notifications_sent' => $notification_count,
					'total_sales'        => 0.00,
					'total_orders'       => 0,
				),
				array( '%d', '%s', '%d', '%f', '%d' )
			);
		}
	}

	/**
	 * Track WooCommerce orders for restocked products.
	 *
	 * @param int $order_id Order ID.
	 */
	public function track_restock_sales( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		global $wpdb;
		$restock_table = esc_sql( $wpdb->prefix . 'alertx_restock_tracking' );

		// Get all restocked product IDs including variations.
		$restocked_products = $wpdb->get_col( "SELECT product_id FROM `$restock_table`" );

		if ( empty( $restocked_products ) ) {
			return;
		}

		// Loop through order items.
		foreach ( $order->get_items() as $item ) {
			$product_id   = $item->get_product_id();
			$variation_id = $item->get_variation_id();
			$line_total   = floatval( $item->get_total() );
			$quantity     = $item->get_quantity();

			// Check if this product or variation was restocked.
			$is_restocked      = false;
			$target_product_id = null;

			if ( in_array( $product_id, $restocked_products, true ) ) {
				$is_restocked      = true;
				$target_product_id = $product_id;
			} elseif ( $variation_id && in_array( $variation_id, $restocked_products, true ) ) {
				$is_restocked      = true;
				$target_product_id = $variation_id;
			}

			if ( $is_restocked && $line_total > 0 ) {
				// Update restock tracking.
				$wpdb->query(
					$wpdb->prepare(
						"UPDATE `$restock_table`
                        SET total_sales = total_sales + %f,
                            total_orders = total_orders + 1
                        WHERE product_id = %d",
						$line_total,
						$target_product_id
					)
				);
			}
		}
	}

	/**
	 * Debug restock tracking - outputs current restock status
	 * Access via: /wp-admin/admin-ajax.php?action=alertx_debug_restock
	 */
	public function debug_restock_tracking() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Access denied' );
		}

		global $wpdb;
		$restock_table      = esc_sql( $wpdb->prefix . 'alertx_restock_tracking' );
		$subscription_table = esc_sql( $wpdb->prefix . 'alertx_subscriptions' );

		echo '<h2>AlertX Restock Tracking Debug</h2>';

		// Show restock tracking table.
		echo '<h3>Restock Tracking Table:</h3>';
		$restock_data = $wpdb->get_results( "SELECT * FROM `$restock_table`" );

		if ( empty( $restock_data ) ) {
			echo '<p><strong>No restock records found!</strong> This means no products have been restocked yet.</p>';
		} else {
			echo '<table border="1" cellpadding="5" style="border-collapse: collapse;">';
			echo '<tr><th>ID</th><th>Product ID</th><th>Restock Date</th><th>Notifications Sent</th><th>Total Sales</th><th>Total Orders</th></tr>';
			foreach ( $restock_data as $row ) {
				echo '<tr>';
				echo '<td>' . esc_html( $row->id ) . '</td>';
				echo '<td>' . esc_html( $row->product_id ) . '</td>';
				echo '<td>' . esc_html( $row->restock_date ) . '</td>';
				echo '<td>' . esc_html( $row->notifications_sent ) . '</td>';
				echo '<td>' . esc_html( $row->total_sales ) . '</td>';
				echo '<td>' . esc_html( $row->total_orders ) . '</td>';
				echo '</tr>';
			}
			echo '</table>';

			$total_sales = $wpdb->get_var( "SELECT SUM(total_sales) FROM `$restock_table`" );
			echo '<p><strong>Total Restock Sales: $' . number_format( $total_sales ? $total_sales : 0, 2 ) . '</strong></p>';
		}

		// Show tracked orders.
		echo '<h3>Tracked Orders (Transient):</h3>';
		$tracked_orders = get_transient( 'alertx_tracked_orders' );
		if ( $tracked_orders ) {
			echo '<p>Order IDs: ' . esc_html( implode( ', ', $tracked_orders ) ) . '</p>';
		} else {
			echo '<p>No tracked orders found.</p>';
		}

		// Show recent orders.
		echo '<h3>Recent WooCommerce Orders:</h3>';
		$recent_orders = wc_get_orders(
			array(
				'limit'   => 10,
				'orderby' => 'date',
				'order'   => 'DESC',
			)
		);

		if ( empty( $recent_orders ) ) {
			echo '<p>No recent orders found.</p>';
		} else {
			echo '<table border="1" cellpadding="5" style="border-collapse: collapse;">';
			echo '<tr><th>Order ID</th><th>Status</th><th>Total</th><th>Date</th><th>Products</th></tr>';
			foreach ( $recent_orders as $order ) {
				echo '<tr>';
				echo '<td>' . esc_html( $order->get_id() ) . '</td>';
				echo '<td>' . esc_html( $order->get_status() ) . '</td>';
				echo '<td>' . esc_html( $order->get_total() ) . '</td>';
				echo '<td>' . esc_html( $order->get_date_created()->date_i18n( 'Y-m-d H:i:s' ) ) . '</td>';
				echo '<td>';
				foreach ( $order->get_items() as $item ) {
					echo esc_html( $item->get_name() ) . ' (ID: ' . esc_html( $item->get_product_id() ) . ') - $' . esc_html( $item->get_total() ) . '<br>';
				}
				echo '</td>';
				echo '</tr>';
			}
			echo '</table>';
		}

		echo '<hr>';
		echo '<p><strong>To test:</strong> Create a new order for a restocked product and check if the sales are updated.</p>';

		wp_die();
	}

	/**
	 * Manually track a specific order for restock sales
	 * Access via: /wp-admin/admin-ajax.php?action=alertx_manual_track_order&order_id=XXXX
	 */
	public function manual_track_order() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Access denied' );
		}

		// Debug-only endpoint invoked via a manually constructed URL (see.
		// docblock above); a nonce cannot be supplied for direct URL access,.
		// so access is restricted to manage_options instead.
		$order_id = isset( $_GET['order_id'] ) ? intval( $_GET['order_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Capability-gated debug tool; nonce impossible for direct URL access.

		if ( ! $order_id ) {
			echo '<p style="color: red;">Error: No order ID provided. Usage: /wp-admin/admin-ajax.php?action=alertx_manual_track_order&order_id=XXXX</p>';
			wp_die();
		}

		echo '<h2>AlertX Manual Order Tracking</h2>';
		echo '<p>Processing Order ID: ' . esc_html( $order_id ) . '</p>';

		// Clear the tracked orders cache for this specific order.
		$tracked_orders = get_transient( 'alertx_tracked_orders' );
		if ( $tracked_orders && in_array( $order_id, $tracked_orders, true ) ) {
			// Remove this order from tracked list so we can re-process it.
			$tracked_orders = array_diff( $tracked_orders, array( $order_id ) );
			set_transient( 'alertx_tracked_orders', $tracked_orders, DAY_IN_SECONDS );
			echo '<p style="color: orange;">Order removed from cache, re-processing...</p>';
		}

		// Call the tracking function.
		$this->track_restock_sales( $order_id );

		echo '<p style="color: green; font-weight: bold;">Tracking complete! Check the results above.</p>';
		echo '<p><a href="/wp-admin/admin-ajax.php?action=alertx_debug_restock">View Debug Info</a></p>';

		wp_die();
	}

	/**
	 * Test tracking functionality
	 * Access via: /wp-admin/admin-ajax.php?action=alertx_test_tracking
	 */
	public function test_tracking() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Access denied' );
		}

		echo '<h2>AlertX Test Tracking</h2>';

		// Get the most recent completed order.
		$recent_orders = wc_get_orders(
			array(
				'limit'   => 1,
				'status'  => 'completed',
				'orderby' => 'date',
				'order'   => 'DESC',
			)
		);

		if ( empty( $recent_orders ) ) {
			echo '<p>No completed orders found. Please create a test order first.</p>';
			wp_die();
		}

		$test_order = $recent_orders[0];
		$order_id   = $test_order->get_id();

		echo '<h3>Testing with Order #' . esc_html( $order_id ) . '</h3>';
		echo '<p><strong>Order Total:</strong> $' . number_format( $test_order->get_total(), 2 ) . '</p>';
		echo '<p><strong>Order Status:</strong> ' . esc_html( $test_order->get_status() ) . '</p>';

		echo '<h4>Order Items:</h4>';
		echo '<ul>';
		foreach ( $test_order->get_items() as $item ) {
			echo '<li>';
			echo 'Product ID: ' . esc_html( $item->get_product_id() );
			if ( $item->get_variation_id() ) {
				echo ' (Variation: ' . esc_html( $item->get_variation_id() ) . ')';
			}
			echo '<br>';
			echo 'Quantity: ' . esc_html( $item->get_quantity() ) . '<br>';
			echo 'Line Total: $' . number_format( $item->get_total(), 2 );
			echo '</li>';
		}
		echo '</ul>';

		echo '<hr>';
		echo '<h3>Running Tracking Test...</h3>';

		// Call the tracking function.
		$this->track_restock_sales( $order_id );

		echo '<hr>';
		echo '<h3>Result:</h3>';

		global $wpdb;
		$restock_table = esc_sql( $wpdb->prefix . 'alertx_restock_tracking' );

		// Show updated restock tracking.
		$restock_data = $wpdb->get_results( "SELECT * FROM `$restock_table`" );

		if ( empty( $restock_data ) ) {
			echo '<p style="color: red;">No restock records found.</p>';
		} else {
			echo '<table border="1" cellpadding="5">';
			echo '<tr><th>Product ID</th><th>Total Sales</th><th>Total Orders</th></tr>';
			foreach ( $restock_data as $row ) {
				echo '<tr>';
				echo '<td>' . esc_html( $row->product_id ) . '</td>';
				echo '<td>' . esc_html( $row->total_sales ) . '</td>';
				echo '<td>' . esc_html( $row->total_orders ) . '</td>';
				echo '</tr>';
			}
			echo '</table>';

			$total_sales = $wpdb->get_var( "SELECT SUM(total_sales) FROM `$restock_table`" );
			echo '<p style="font-size: 20px; font-weight: bold; color: green;">Total Restock Sales: $' . number_format( $total_sales ? $total_sales : 0, 2 ) . '</p>';
		}

		echo '<hr>';
		echo '<p><a href="/wp-admin/admin-ajax.php?action=alertx_debug_restock">View Full Debug Info</a></p>';

		wp_die();
	}

	/**
	 * Auto-fix restock tracking issues
	 * Access via: /wp-admin/admin-ajax.php?action=alertx_auto_fix
	 */
	public function auto_fix_restock_tracking() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Access denied' );
		}

		echo '<h2>AlertX Auto-Fix Restock Tracking</h2>';

		global $wpdb;
		$restock_table      = esc_sql( $wpdb->prefix . 'alertx_restock_tracking' );
		$subscription_table = esc_sql( $wpdb->prefix . 'alertx_subscriptions' );

		// Step 1: Check and create table.
		echo '<h3>Step 1: Checking restock table...</h3>';
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$restock_table'" );

		if ( ! $table_exists ) {
			echo '<p style="color: red;">Table NOT found. Creating...</p>';
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $restock_table (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                product_id bigint(20) NOT NULL,
                restock_date datetime NULL DEFAULT NULL,
                notifications_sent int(11) DEFAULT 0 NOT NULL,
                total_sales decimal(10,2) DEFAULT 0.00 NOT NULL,
                total_orders int(11) DEFAULT 0 NOT NULL,
                PRIMARY KEY  (id),
                KEY product_id (product_id)
            ) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			echo '<p style="color: green;"><strong>Table created successfully!</strong></p>';
		} else {
			echo '<p style="color: green;">✓ Table exists</p>';
		}

		// Step 2: Find products that were restocked (from subscriptions).
		echo '<h3>Step 2: Finding restocked products...</h3>';

		// Get unique products that have subscriptions.
		$subscribed_products = $wpdb->get_col( "SELECT DISTINCT product_id FROM `$subscription_table`" );
		echo '<p>Found ' . count( $subscribed_products ) . ' products with subscriptions</p>';

		$restock_count = 0;
		foreach ( $subscribed_products as $product_id ) {
			// Check if this product already has a restock record.
			$existing = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM `$restock_table` WHERE product_id = %d",
					$product_id
				)
			);

			if ( ! $existing ) {
				// Create restock record for this product.
				$wpdb->insert(
					$restock_table,
					array(
						'product_id'         => $product_id,
						'restock_date'       => current_time( 'mysql' ),
						'notifications_sent' => 0,
						'total_sales'        => 0.00,
						'total_orders'       => 0,
					),
					array( '%d', '%s', '%d', '%f', '%d' )
				);
				++$restock_count;
			}
		}

		echo '<p style="color: green;"><strong>Created ' . esc_html( $restock_count ) . ' restock records!</strong></p>';

		// Step 3: Process recent orders.
		echo '<h3>Step 3: Processing recent orders...</h3>';

		// Clear all tracked orders cache to force re-tracking.
		delete_transient( 'alertx_tracked_orders' );
		echo '<p>Cleared tracking cache</p>';

		// Get recent completed orders - extended to 60 days and all order statuses.
		$recent_orders = wc_get_orders(
			array(
				'limit'        => 200,
				'status'       => 'any',  // Get all orders regardless of status.
				'date_created' => '>' . strtotime( '-60 days' ),
				'orderby'      => 'date',
				'order'        => 'DESC',
			)
		);

		echo '<p>Found ' . count( $recent_orders ) . ' orders to process (last 60 days)...</p>';

		$tracked_count       = 0;
		$total_sales_tracked = 0;
		$processed_products  = array();

		foreach ( $recent_orders as $order ) {
			$order_id     = $order->get_id();
			$order_status = $order->get_status();

			echo '<p style="color: blue;">Processing Order ' . esc_html( $order_id ) . ' (Status: ' . esc_html( $order_status ) . ')</p>';

			// Process each order item.
			foreach ( $order->get_items() as $item ) {
				$product_id   = $item->get_product_id();
				$variation_id = $item->get_variation_id();
				$line_total   = floatval( $item->get_total() );
				$quantity     = $item->get_quantity();

				echo '&nbsp;&nbsp;&nbsp;Item: Product ID ' . esc_html( $product_id );
				if ( $variation_id ) {
					echo ' (Variation: ' . esc_html( $variation_id ) . ')';
				}
				echo ' - Qty: ' . esc_html( $quantity ) . ' - Total: $' . number_format( $line_total, 2 );

				// Check if this product OR variation is in our restock tracking.
				$is_restocked_product = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT id FROM `$restock_table` WHERE product_id = %d",
						$product_id
					)
				);

				$is_restocked_variation = false;
				if ( $variation_id ) {
					$is_restocked_variation = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT id FROM `$restock_table` WHERE product_id = %d",
							$variation_id
						)
					);
				}

				$target_product_id = null;
				if ( $is_restocked_product ) {
					$target_product_id = $product_id;
					echo ' - <span style="color: green;">✓ Product found in tracking!</span>';
				} elseif ( $is_restocked_variation ) {
					$target_product_id = $variation_id;
					echo ' - <span style="color: green;">✓ Variation found in tracking!</span>';
				} else {
					echo ' - <span style="color: gray;">Not in tracking</span>';
				}

				if ( $target_product_id && $line_total > 0 ) {
					// Update restock tracking.
					$result = $wpdb->query(
						$wpdb->prepare(
							"UPDATE `$restock_table`
                            SET total_sales = total_sales + %f,
                                total_orders = total_orders + 1
                            WHERE product_id = %d",
							$line_total,
							$target_product_id
						)
					);

					if ( $result ) {
						$total_sales_tracked += $line_total;
						++$tracked_count;
						if ( ! isset( $processed_products[ $target_product_id ] ) ) {
							$processed_products[ $target_product_id ] = 0;
						}
						$processed_products[ $target_product_id ] += $line_total;

						echo ' - <strong style="color: green;">Added $' . number_format( $line_total, 2 ) . ' to product ' . esc_html( $target_product_id ) . '</strong>';
					}
				}

				echo '</p>';
			}

			// Mark order as tracked.
			$tracked_orders = get_transient( 'alertx_tracked_orders' );
			if ( ! $tracked_orders ) {
				$tracked_orders = array();
			}
			if ( ! in_array( $order_id, $tracked_orders, true ) ) {
				$tracked_orders[] = $order_id;
			}
			set_transient( 'alertx_tracked_orders', $tracked_orders, DAY_IN_SECONDS );
		}

		echo '<hr>';
		echo '<h3 style="color: green;">Tracking Summary:</h3>';
		echo '<p><strong>Total items tracked:</strong> ' . esc_html( $tracked_count ) . '</p>';
		echo '<p><strong>Total sales tracked:</strong> $' . number_format( $total_sales_tracked, 2 ) . '</p>';

		if ( ! empty( $processed_products ) ) {
			echo '<h4>Product-wise breakdown:</h4>';
			echo '<ul>';
			foreach ( $processed_products as $prod_id => $sales ) {
				echo '<li>Product ' . esc_html( $prod_id ) . ': $' . number_format( $sales, 2 ) . '</li>';
			}
			echo '</ul>';
		}

		echo '<p style="color: green;"><strong>Total tracked: ' . esc_html( $tracked_count ) . ' items worth $' . number_format( $total_sales_tracked, 2 ) . '!</strong></p>';

		// Step 4: Show current status.
		echo '<h3>Step 4: Current Restock Status</h3>';

		$restock_data = $wpdb->get_results( "SELECT * FROM `$restock_table`" );

		if ( empty( $restock_data ) ) {
			echo '<p style="color: orange;">No restock records found.</p>';
		} else {
			echo '<table border="1" cellpadding="5" style="border-collapse: collapse; max-width: 100%; overflow-x: auto;">';
			echo '<tr><th>Product ID</th><th>Restock Date</th><th>Notifications Sent</th><th>Total Sales</th><th>Total Orders</th></tr>';
			foreach ( $restock_data as $row ) {
				echo '<tr>';
				echo '<td>' . esc_html( $row->product_id ) . '</td>';
				echo '<td>' . esc_html( $row->restock_date ) . '</td>';
				echo '<td>' . esc_html( $row->notifications_sent ) . '</td>';
				echo '<td>' . esc_html( $row->total_sales ) . '</td>';
				echo '<td>' . esc_html( $row->total_orders ) . '</td>';
				echo '</tr>';
			}
			echo '</table>';

			$total_sales = $wpdb->get_var( "SELECT SUM(total_sales) FROM `$restock_table`" );
			echo '<p style="font-size: 24px; font-weight: bold; color: green; margin: 20px 0;">Total Restock Sales: $' . number_format( $total_sales ? $total_sales : 0, 2 ) . '</p>';
		}

		echo '<hr>';
		echo '<div style="padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px;">';
		echo '<p style="color: #155724; font-weight: bold; margin: 0;">✓ Auto-fix complete!</p>';
		echo '<p style="margin: 10px 0 0 0;"><a href="/wp-admin/admin.php?page=alertx" style="color: #155724; font-weight: bold; text-decoration: underline;">Go to Dashboard →</a> to see the updated values.</p>';
		echo '</div>';

		wp_die();
	}

	/**
	 * Checks stock levels and notifies users if necessary.
	 *
	 * @param WC_Product|int $product Product object or product ID.
	 */
	public function check_stock_and_notify( $product ) {
		// Ensure $product is a valid WC_Product object.
		if ( ! $product instanceof WC_Product ) {
			return; // Exit if the product is not a valid WC_Product instance.
		}

		$product_id             = $product->get_id();
		$stock_quantity         = $product->get_stock_quantity();
		$notification_threshold = get_option( 'stock_notification_threshold', 1 );
		$stock_status           = $product->get_stock_status();

		// Ensure stock quantity and notification threshold are integers.
		$stock_quantity         = intval( $stock_quantity );
		$notification_threshold = intval( $notification_threshold );

		// Check if the stock quantity is above the threshold AND product is in stock.
		if ( $stock_quantity >= $notification_threshold && 'instock' === $stock_status ) {
			// Pass all required parameters including stock_status.
			$this->send_alertx_subscriptions( $product_id, 'instock', $stock_status );
		}
	}

	/**
	 * Checks if the email address has exceeded the allowed number of notifications
	 * within a specified rate limit (e.g., 5 notifications per hour).
	 *
	 * This method uses a transient to keep track of the number of requests from
	 * a particular email address. If the number of requests exceeds the limit,
	 * the email address is considered rate-limited.
	 *
	 * @param string $email The email address to check for rate-limiting.
	 * @return bool Returns true if the email address is rate-limited, otherwise false.
	 */
	private function is_rate_limited( $email ) {
		// Validate email format.
		if ( ! is_email( $email ) ) {
			return true; // Treat invalid email addresses as rate-limited.
		}

		// Generate a unique transient name based on the email address.
		$transient_name = 'stock_notify_' . md5( $email );
		// Retrieve the current count of requests from the transient.
		$count = get_transient( $transient_name );

		// If no transient is found, initialize it with a count of 1.
		if ( false === $count ) {
			set_transient( $transient_name, 1, HOUR_IN_SECONDS );
			return false;
		}

		// Check if the count exceeds the rate limit (5 requests per hour).
		if ( 5 <= $count ) {
			return true; // Email is rate-limited.
		}

		// Increment the count and reset the expiration time of the transient.
		set_transient( $transient_name, $count + 1, HOUR_IN_SECONDS );
		return false;
	}

	/**
	 * Handles bulk actions for stock notifications in the WordPress admin.
	 *
	 * This method processes bulk actions (e.g., deleting selected notifications)
	 * submitted via the admin interface. It verifies nonce for security and
	 * performs the requested action on selected notifications.
	 *
	 * @return void
	 */
	public function handle_bulk_action_alertx_subscriptions() {
		// Check for nonce verification and required POST data.
		if ( ! isset( $_POST['submit_bulk_action'], $_POST['bulk_action_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bulk_action_nonce'] ) ), 'bulk_action' ) ) {
			return; // Exit if nonce verification fails or POST data is missing.
		}

		// Check if notifications are selected and ensure it's an array.
		if ( isset( $_POST['notifications'] ) && is_array( $_POST['notifications'] ) ) {
			$action = isset( $_POST['bulk_action'] ) ? sanitize_text_field( wp_unslash( $_POST['bulk_action'] ) ) : '';

			if ( 'delete' === $action ) {
				$notifications = array_map( 'absint', wp_unslash( $_POST['notifications'] ) );
				$this->handle_bulk_delete( $notifications );
			}
		}
	}

	/**
	 * Handles the deletion of multiple stock notifications.
	 *
	 * Iterates through the provided notification IDs and deletes each one
	 * from the database. Displays a success message in the admin area upon completion.
	 *
	 * @param array $notification_ids Array of notification IDs to delete.
	 * @return void
	 */
	private function handle_bulk_delete( array $notification_ids ) {
		global $wpdb;
		$table_name = esc_sql( $wpdb->prefix . 'alertx_subscriptions' );

		// Delete each notification by ID.
		foreach ( $notification_ids as $notification_id ) {
			$this->delete_stock_notification( intval( $notification_id ) );
		}

		// Add admin notice to indicate successful deletion.
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-success is-dismissible">';
				echo '<p>' . esc_html__( 'Selected notifications have been deleted.', 'alertx-pro' ) . '</p>';
				echo '</div>';
			}
		);
	}

	/**
	 * Deletes a stock notification from the database.
	 *
	 * This method removes a specific stock notification entry based on the provided
	 * notification ID. It uses the WordPress $wpdb object to perform a safe delete operation.
	 *
	 * @param int $notification_id The ID of the notification to delete.
	 * @return void
	 */
	private function delete_stock_notification( int $notification_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'alertx_subscriptions'; // Define the table name.

		// Ensure the ID is an integer and delete the record from the database.
		$wpdb->delete(
			$table,
			array( 'id' => $notification_id ), // The condition for the delete query.
			array( '%d' ) // Format for the value, %d for integer.
		);
	}

	/**
	 * Suppress admin notices on AlertX Pro admin pages.
	 */
	public function alertx_page_hide_notifications() {
		if ( ! is_admin() ) {
			return;
		}

		// Read-only page detection on every admin request; no action is.
		// taken on the data, so no nonce applies.
		$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only GET param used only to identify the current admin page.

		// AlertX pages only.
		$alertx_pages = array(
			'alertx-pro',
			'subscribers',
			'campaigns',
			'email-templates',
			'new-campaign',
			'alertx-settings',
		);

		if ( in_array( $page, $alertx_pages, true ) ) {

			// Hide admin notices.
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
		}
	}
}
// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, PluginCheck.Security.DirectDB.UnescapedDBParameter
