<?php
/**
 * RestockX Pro admin menu and front-end notification handlers.
 *
 * @package RestockX\Admin
 */

namespace RestockX\Admin;

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom tables are queried with $wpdb->prepare(); these low-frequency admin queries intentionally bypass the object cache.

/**
 * Class RestockX_Menu
 *
 * Handles the admin menu for stock notifications and associated functionalities.
 */
class RestockX_Menu {

	use \RestockX\Admin\Pages\Dashboard_Page;
	use \RestockX\Admin\Pages\Subscribers_Page;
	use \RestockX\Admin\Pages\Email_Templates_Page;

	/**
	 * Singleton instance of the class.
	 *
	 * @var RestockX_Menu|null
	 */
	private static $instance = null;

	/**
	 * Retrieves the singleton instance of the class.
	 *
	 * @return RestockX_Menu Singleton instance.
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
		add_action( 'wp_ajax_restockx_stock_notification', array( $this, 'handle_stock_notification' ) );
		add_action( 'wp_ajax_nopriv_restockx_stock_notification', array( $this, 'handle_stock_notification' ) );
		// Double opt-in confirmation endpoint.
		add_action( 'wp_ajax_restockx_stock_confirm_subscription', array( $this, 'confirm_subscription' ) );
		add_action( 'wp_ajax_nopriv_restockx_stock_confirm_subscription', array( $this, 'confirm_subscription' ) );
		// Unsubscribe endpoint.
		add_action( 'wp_ajax_restockx_stock_unsubscribe', array( $this, 'unsubscribe' ) );
		add_action( 'wp_ajax_nopriv_restockx_stock_unsubscribe', array( $this, 'unsubscribe' ) );
		// Trigger notifications when stock status changes on products.
		add_action( 'woocommerce_product_set_stock_status', array( $this, 'send_restockx_subscriptions' ), 10, 3 );
		// Ensure variations also trigger notifications when stock/status changes.
		add_action( 'woocommerce_variation_set_stock_status', array( $this, 'send_restockx_subscriptions' ), 10, 3 );
		// Trigger threshold-based checks when quantity is set on products and variations.
		add_action( 'woocommerce_product_set_stock', array( $this, 'check_stock_and_notify' ) );
		add_action( 'woocommerce_variation_set_stock', array( $this, 'check_stock_and_notify' ) );
		add_action( 'admin_init', array( $this, 'handle_bulk_action_restockx_subscriptions' ) );

		add_action( 'admin_init', array( $this, 'restockx_page_hide_notifications' ) );
	}

	/**
	 * Adds the admin menu items for the stock notifications plugin.
	 *
	 * This method creates the main menu and a submenu under the WordPress admin dashboard.
	 * It defines the menu pages and their corresponding callback functions.
	 *
	 * The premium screens (Campaigns, New Campaign and Settings) are registered as
	 * free teasers by default. Extensions can replace a callback through the
	 * `restockx_submenu_callback` filter to render their own full-featured page
	 * under the same menu item (this is how RestockX Pro integrates).
	 *
	 * @return void
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'RestockX', 'restockx' ),
			__( 'RestockX', 'restockx' ),
			'manage_options',
			'restockx',
			array( $this, 'restockx_admin_dashboard' ),
			RESTOCKX_URL . 'assets/images/icon.png'
		);

		add_submenu_page(
			'restockx',
			__( 'Subscribers', 'restockx' ),
			__( 'Subscribers', 'restockx' ),
			'manage_options',
			'restockx-subscribers',
			array( $this, 'restockx_subscribers' )
		);

		add_submenu_page(
			'restockx',
			__( 'Email Templates', 'restockx' ),
			__( 'Email Templates', 'restockx' ),
			'manage_options',
			'restockx-email-templates',
			array( $this, 'restockx_email_templates' )
		);

		add_submenu_page(
			'restockx',
			__( 'Campaigns', 'restockx' ),
			__( 'Campaigns', 'restockx' ),
			'manage_options',
			'restockx-campaigns',
			apply_filters( 'restockx_submenu_callback', array( $this, 'restockx_campaigns' ), 'restockx-campaigns' )
		);

		add_submenu_page(
			'restockx',
			__( 'New Campaign', 'restockx' ),
			__( 'New Campaign', 'restockx' ),
			'manage_options',
			'restockx-new-campaign',
			apply_filters( 'restockx_submenu_callback', array( $this, 'restockx_new_campaign' ), 'restockx-new-campaign' )
		);

		add_submenu_page(
			'restockx',
			__( 'Settings', 'restockx' ),
			__( 'Settings', 'restockx' ),
			'manage_options',
			'restockx-settings',
			apply_filters( 'restockx_submenu_callback', array( $this, 'restockx_settings' ), 'restockx-settings' )
		);
	}

	public function restockx_campaigns() {
		// Path to the admin page template file.
		$template_path = RESTOCKX_PATH . 'views/admin-campaign.php';

		// Check if the template exists before including it.
		if ( file_exists( $template_path ) ) {
			include $template_path; // No parentheses needed for include.
		} else {
			// Template not found, display an error or a fallback message.
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Template file not found.', 'restockx' ) . '</p></div>';
		}
	}

	public function restockx_new_campaign() {
		// Path to the admin page template file.
		$template_path = RESTOCKX_PATH . 'views/admin-new-campaign.php';

		// Check if the template exists before including it.
		if ( file_exists( $template_path ) ) {
			include $template_path; // No parentheses needed for include.
		} else {
			// Template not found, display an error or a fallback message.
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Template file not found.', 'restockx' ) . '</p></div>';
		}
	}

	public function restockx_settings() {
		// Path to the admin page template file.
		$template_path = RESTOCKX_PATH . 'views/admin-settings.php';

		// Check if the template exists before including it.
		if ( file_exists( $template_path ) ) {
			include $template_path; // No parentheses needed for include.
		} else {
			// Template not found, display an error or a fallback message.
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Template file not found.', 'restockx' ) . '</p></div>';
		}
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
			wp_send_json_error( __( 'Security token is missing. Please refresh the page and try again.', 'restockx' ) );
		}

		// For variable products, use parent_id for nonce verification.
		// For simple products, product_id is used.
		$nonce_product_id = $parent_id > 0 ? $parent_id : $product_id;

		// Verify nonce with product-specific action.
		$nonce_action = 'restockx_notify_me_' . $nonce_product_id;
		if ( ! wp_verify_nonce( $nonce, $nonce_action ) ) {
			wp_send_json_error( __( 'Security check failed. Please refresh the page and try again.', 'restockx' ) );
		}

		// Validate product ID.
		if ( empty( $product_id ) ) {
			wp_send_json_error( __( 'Error: Product/Variation ID not found. Please select a variation (if applicable) or refresh the page and try again.', 'restockx' ) );
		}

		if ( isset( $_POST['email'] ) && $product_id ) {
			// Sanitize and validate inputs.
			$email      = $this->sanitize_and_validate_email( sanitize_email( wp_unslash( $_POST['email'] ) ) );
			$product_id = $this->sanitize_and_validate_product_id( $product_id );

			// Check rate limiting to prevent multiple requests.
			if ( $this->is_rate_limited( $email ) ) {
				wp_send_json_error( __( 'Too many requests. Please try again later.', 'restockx' ) );
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
			wp_send_json_error( __( 'Invalid email address', 'restockx' ) );
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
			wp_send_json_error( __( 'Invalid product ID', 'restockx' ) );
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
		$table_name = $wpdb->prefix . 'restockx_subscriptions';

		return $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM %i WHERE email = %s AND product_id = %d',
				$table_name,
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
			wp_send_json_error( __( 'You have already subscribed to notifications for this product.', 'restockx' ) );
		}

		// Renew the subscription.
		$this->renew_notification( $existing_notification->id );
		wp_send_json_success(
			array(
				'message'      => __( 'Your notification subscription has been renewed for this product.', 'restockx' ),
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
		$table_name = $wpdb->prefix . 'restockx_subscriptions';

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
		$table_name = $wpdb->prefix . 'restockx_subscriptions';

		// Check if double opt-in confirmation is required (Premium feature —
		// only runs when RestockX Pro is active; a leftover '1' from before
		// Pro was deactivated never enables it in the free version).
		$require_confirmation = get_option( 'restockx_require_confirmation', '0' ) === '1' && ! restockx_show_upgrade_cta();

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
			$product_name = $product ? $product->get_name() : __( 'this product', 'restockx' );

			$response_data = array(
				'message'      => sprintf(
					/* translators: %s: product name */
					__( 'You have been subscribed to notifications for <strong>%s</strong>. We will notify you when it is back in stock.', 'restockx' ),
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
		$product_name = $product ? $product->get_name() : __( 'this product', 'restockx' );

		$response_data = array(
			'message'      => sprintf(
				/* translators: %s: product name */
				__( 'Almost done! Confirm your subscription via email to get notified when <strong>%s</strong> is back in stock.', 'restockx' ),
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
		$product_name = $product ? $product->get_name() : __( 'this product', 'restockx' );

		// Build confirmation URL using AJAX endpoint.
		$confirm_url = add_query_arg(
			array(
				'action' => 'restockx_stock_confirm_subscription',
				'token'  => rawurlencode( $token ),
				'pid'    => intval( $product_id ),
			),
			admin_url( 'admin-ajax.php' )
		);

		/* translators: %s: Product name */
		$subject = sprintf( __( 'Confirm your stock alert for %s', 'restockx' ), $product_name );

		// Keep the From address domain-aligned so messages are not dropped.
		// by recipients' providers; external configured addresses become.
		// the Reply-To instead.
		$sender    = \RestockX\Admin::resolve_email_sender();
		$from_name = get_bloginfo( 'name' ) . ' ' . __( 'Stock Alerts', 'restockx' );

		// HTML message for better client compatibility.
		$message = sprintf(
			/* translators: 1: Product name, 2: Confirmation URL */
			__( 'Please confirm your subscription to be notified when %1$s is back in stock. <a href="%2$s">Click here to confirm</a>.', 'restockx' ),
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
		$product_name = $product ? $product->get_name() : __( 'this product', 'restockx' );
		$product_url  = $product ? $product->get_permalink() : home_url( '/' );

		/* translators: %s: Product name */
		$subject = sprintf( __( 'You are subscribed: %s stock alerts', 'restockx' ), $product_name );

		$sender    = \RestockX\Admin::resolve_email_sender();
		$from_name = get_bloginfo( 'name' ) . ' ' . __( 'Stock Alerts', 'restockx' );

		$message = sprintf(
			/* translators: 1: Product name, 2: Site name, 3: Product URL */
			__( 'You will receive an email as soon as <strong>%1$s</strong> is back in stock at %2$s.<br><br><a href="%3$s">View product</a>', 'restockx' ),
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
			wp_die( esc_html__( 'Invalid confirmation request.', 'restockx' ) );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'restockx_subscriptions';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public token-verified endpoint accessed via email links; a nonce cannot be supplied.
		$token = sanitize_text_field( wp_unslash( $_GET['token'] ) );
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public token-verified endpoint accessed via email links; a nonce cannot be supplied.
		$product_id = absint( wp_unslash( $_GET['pid'] ) );

		$notification = $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM %i WHERE token = %s AND product_id = %d', $table_name, $token, $product_id )
		);
		if ( ! $notification ) {
			wp_die( esc_html__( 'Subscription not found or already confirmed.', 'restockx' ) );
		}

		// Confirm subscription.
		$wpdb->update( $table_name, array( 'status' => 'confirmed' ), array( 'id' => intval( $notification->id ) ), array( '%s' ), array( '%d' ) );

		wp_die( esc_html__( 'Your subscription has been confirmed. Thank you!', 'restockx' ) );
	}

	/**
	 * Unsubscribe via token
	 */
	public function unsubscribe() {
		// Note: This is a public endpoint accessed via email links.
		// Security is handled via token validation instead of nonce.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public token-verified endpoint accessed via email links; a nonce cannot be supplied.
		if ( empty( $_GET['token'] ) || empty( $_GET['pid'] ) ) {
			wp_die( esc_html__( 'Invalid unsubscribe request.', 'restockx' ) );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'restockx_subscriptions';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public token-verified endpoint accessed via email links; a nonce cannot be supplied.
		$token = sanitize_text_field( wp_unslash( $_GET['token'] ) );
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public token-verified endpoint accessed via email links; a nonce cannot be supplied.
		$product_id = absint( wp_unslash( $_GET['pid'] ) );

		$notification = $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM %i WHERE token = %s AND product_id = %d', $table_name, $token, $product_id )
		);
		if ( ! $notification ) {
			wp_die( esc_html__( 'Subscription not found.', 'restockx' ) );
		}

		// Update subscription status to 'unsubscribed' instead of deleting.
		$wpdb->update(
			$table_name,
			array( 'status' => 'unsubscribed' ),
			array( 'id' => intval( $notification->id ) ),
			array( '%s' ),
			array( '%d' )
		);

		wp_die( esc_html__( 'You have been unsubscribed from stock alerts.', 'restockx' ) );
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
	public function send_restockx_subscriptions( $product_id, $stock_status = null, $old_stock_status = null ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- The parameter is required by the stock status hook signature.
		// Only send when a product/variation becomes in stock.
		if ( ! empty( $stock_status ) && 'instock' !== $stock_status ) {
			return;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'restockx_subscriptions';

		// Fetch confirmed notifications for the given product ID.
		$notifications = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM %i WHERE product_id = %d AND status = %s',
				$table_name,
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
		$email_templates = get_option( 'restockx_email_templates', $this->get_default_email_templates() );
		$product         = wc_get_product( $product_id );

		if ( ! $product ) {
			return; // Exit if the product is not found.
		}

		// Prepare email headers with proper sender info and unsubscribe header.

		// Keep the From address domain-aligned so messages are not dropped.
		// by recipients' providers; external configured addresses become.
		// the Reply-To instead.
		$sender    = \RestockX\Admin::resolve_email_sender();
		$from_name = get_bloginfo( 'name' ) . ' ' . __( 'Stock Alerts', 'restockx' );
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
				__( 'Product Back in Stock: %s', 'restockx' ),
				esc_html( $product->get_name() )
			);

			// Replace placeholders in the email template with actual values and unsubscribe link.
			$unsubscribe_url = add_query_arg(
				array(
					'action' => 'restockx_stock_unsubscribe',
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
		$restock_table = $wpdb->prefix . 'restockx_restock_tracking';

		// Check if this product already has a restock record.
		$existing = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM %i WHERE product_id = %d ORDER BY restock_date DESC LIMIT 1',
				$restock_table,
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
		$notification_threshold = get_option( 'restockx_threshold', 1 );
		$stock_status           = $product->get_stock_status();

		// Ensure stock quantity and notification threshold are integers.
		$stock_quantity         = intval( $stock_quantity );
		$notification_threshold = intval( $notification_threshold );

		// Check if the stock quantity is above the threshold AND product is in stock.
		if ( $stock_quantity >= $notification_threshold && 'instock' === $stock_status ) {
			// Pass all required parameters including stock_status.
			$this->send_restockx_subscriptions( $product_id, 'instock', $stock_status );
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
		$transient_name = 'restockx_rate_' . md5( $email );
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
	public function handle_bulk_action_restockx_subscriptions() {
		// Check for nonce verification and required POST data.
		if ( ! isset( $_POST['submit_bulk_action'], $_POST['bulk_action_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bulk_action_nonce'] ) ), 'restockx_bulk_action' ) ) {
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
		$table_name = $wpdb->prefix . 'restockx_subscriptions';

		// Delete each notification by ID.
		foreach ( $notification_ids as $notification_id ) {
			$this->delete_stock_notification( intval( $notification_id ) );
		}

		// Add admin notice to indicate successful deletion.
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-success is-dismissible">';
				echo '<p>' . esc_html__( 'Selected notifications have been deleted.', 'restockx' ) . '</p>';
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
		$table = $wpdb->prefix . 'restockx_subscriptions'; // Define the table name.

		// Ensure the ID is an integer and delete the record from the database.
		$wpdb->delete(
			$table,
			array( 'id' => $notification_id ), // The condition for the delete query.
			array( '%d' ) // Format for the value, %d for integer.
		);
	}

	/**
	 * Suppress admin notices on RestockX Pro admin pages.
	 */
	public function restockx_page_hide_notifications() {
		if ( ! is_admin() ) {
			return;
		}

		// Read-only page detection on every admin request; no action is.
		// taken on the data, so no nonce applies.
		$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only GET param used only to identify the current admin page.

		// RestockX pages only.
		$restockx_pages = array(
			'restockx',
			'restockx-subscribers',
			'restockx-email-templates',
		);

		if ( in_array( $page, $restockx_pages, true ) ) {

			// Hide admin notices.
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
		}
	}
}
// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, PluginCheck.Security.DirectDB.UnescapedDBParameter
