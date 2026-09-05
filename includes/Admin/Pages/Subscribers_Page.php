<?php
/**
 * Subscribers page methods for the AlertX Pro admin menu.
 *
 * @package Alertx\Admin\Pages
 */

namespace Alertx\Admin\Pages;

defined( 'ABSPATH' ) || exit;

/**
 * Trait Subscribers_Page
 *
 * Admin page methods moved out of Alertx_Menu. The trait is merged back
 * into the Alertx_Menu class, so every method keeps the exact same
 * visibility and $this behaviour as before the split.
 */
trait Subscribers_Page {

	/**
	 * Get subscriber statistics data.
	 *
	 * Results are cached for one minute to avoid repeating the aggregate
	 * queries on every page load.
	 *
	 * @return array Subscriber stats array.
	 */
	private function get_subscribers_stats() {
		$stats = wp_cache_get( 'subscribers_stats', 'alertx_stats' );

		if ( false === $stats ) {
			$stats = $this->build_subscribers_stats();
			wp_cache_set( 'subscribers_stats', $stats, 'alertx_stats', MINUTE_IN_SECONDS );
		}

		return $stats;
	}

	/**
	 * Build the subscriber statistics data.
	 *
	 * @return array Subscriber stats array.
	 */
	private function build_subscribers_stats() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'alertx_subscriptions';

		// Total subscribers - all unique email subscribers.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom plugin table, no WP API equivalent; result is cached by get_subscribers_stats() for one minute.
		$total_subscribers = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(DISTINCT email) FROM %i',
				$table_name
			)
		);

		// Confirmed subscribers - users who confirmed their email.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom plugin table, no WP API equivalent; result is cached by get_subscribers_stats() for one minute.
		$confirmed_subscribers = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT email) FROM %i WHERE status = 'confirmed'",
				$table_name
			)
		);

		// Pending subscribers - users who subscribed but haven't confirmed yet.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom plugin table, no WP API equivalent; result is cached by get_subscribers_stats() for one minute.
		$pending_subscribers = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT email) FROM %i WHERE status = 'pending'",
				$table_name
			)
		);

		// Unsubscribed - users who unsubscribed.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom plugin table, no WP API equivalent; result is cached by get_subscribers_stats() for one minute.
		$unsubscribed = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT email) FROM %i WHERE status = 'unsubscribed'",
				$table_name
			)
		);

		$stats = array(
			array(
				'icon_class'  => 'c1',
				'icon'        => ' <svg width="43" height="39" viewBox="0 0 43 39" fill="none">
                            <path d="M7.8605 26.0928C5.34535 27.567 -1.24921 30.5772 2.76732 34.344C4.72936 36.184 6.91457 37.5 9.66191 37.5H25.3388C28.0862 37.5 30.2714 36.184 32.2334 34.344C36.2499 30.5772 29.6554 27.567 27.1402 26.0928C21.2423 22.6357 13.7585 22.6357 7.8605 26.0928Z" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M25.5004 9.5C25.5004 13.9183 21.9186 17.5 17.5004 17.5C13.0821 17.5 9.50037 13.9183 9.50037 9.5C9.50037 5.08172 13.0821 1.5 17.5004 1.5C21.9186 1.5 25.5004 5.08172 25.5004 9.5Z" stroke="#ffffff" stroke-width="3"></path>
                            <path d="M36.5004 3.5V13.5M41.5004 8.5L31.5004 8.5" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>',
				'data_count'  => $total_subscribers,
				'data_suffix' => '',
				'value'       => $total_subscribers,
				'label'       => 'Total Subscribers',
			),
			array(
				'icon_class'  => 'c2',
				'icon'        => '<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>',
				'data_count'  => $confirmed_subscribers,
				'data_suffix' => '',
				'value'       => $confirmed_subscribers,
				'label'       => 'Confirmed',
			),
			array(
				'icon_class'  => 'c3',
				'icon'        => '<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>',
				'data_count'  => $pending_subscribers,
				'data_suffix' => '',
				'value'       => $pending_subscribers,
				'label'       => 'Pending Confirmation',
			),
			array(
				'icon_class'  => 'c4',
				'icon'        => '<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>',
				'data_count'  => $unsubscribed,
				'data_suffix' => '',
				'value'       => $unsubscribed,
				'label'       => 'Unsubscribed',
			),
		);

		return $stats;
	}

	/**
	 * Displays the admin page for managing stock notifications.
	 *
	 * This method handles CSV export requests, fetches stock notifications from the database,
	 * and includes the admin page template to render the notifications list.
	 *
	 * @return void
	 */
	public function alertx_subscribers() {
		global $wpdb;

		// Handle CSV export if the export button was clicked.
		if ( isset( $_POST['export_csv'] ) ) {
			// Verify nonce for security.
			if ( ! isset( $_POST['stock_notification_export_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['stock_notification_export_nonce'] ) ), 'stock_notification_export' ) ) {
				wp_die( esc_html__( 'Security check failed.', 'alertx-pro' ) );
			}

			// Clean any existing output buffers.
			while ( ob_get_level() > 0 ) {
				ob_end_clean();
			}

			// Generate CSV and exit - this will terminate script execution.
			$this->generate_csv(); // generate_csv() calls exit(), so code below never executes.
		}

		// Table name for stock notifications in the database.
		$table_name = $wpdb->prefix . 'alertx_subscriptions';

		// Number of notifications to show per page.
		$alertx_items_per_page = 10;

		// Get the current page number, ensuring it's valid.
		$paged  = isset( $_GET['paged'] ) && is_numeric( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;
		$offset = ( $paged - 1 ) * $alertx_items_per_page;

		// Fetch the total number of stock notifications to calculate pagination.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom plugin table, no WP API equivalent; admin list pagination must reflect real-time data.
		$total_notifications = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM %i',
				$table_name
			)
		);

		// Fetch the notifications for the current page, using SQL LIMIT and OFFSET.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom plugin table, no WP API equivalent; admin list pagination must reflect real-time data.
		$notifications = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM %i ORDER BY date_added DESC LIMIT %d OFFSET %d',
				$table_name,
				$alertx_items_per_page,
				$offset
			)
		);

		// Get subscriber statistics data.
		$subscribers_stats = $this->get_subscribers_stats();

		// Path to the admin page template file.
		$template_path = ALERTX_PATH . 'views/admin-subscribers.php';

		// Check if the template exists before including it.
		if ( file_exists( $template_path ) ) {
			include $template_path; // No parentheses needed for include.
		} else {
			// Template not found, display an error or a fallback message.
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Template file not found.', 'alertx-pro' ) . '</p></div>';
		}
	}

	/**
	 * Generates a CSV file of stock notifications and initiates a download.
	 *
	 * This method fetches stock notification data from the database, generates a CSV file with the data,
	 * and sets appropriate headers to force the browser to download the file.
	 *
	 * @return void
	 */
	private function generate_csv() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'alertx_subscriptions';

		// Select all relevant columns including status.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom plugin table, no WP API equivalent; CSV export must contain the complete real-time data set.
		$notifications = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT id, email, product_id, date_added, status FROM %i ORDER BY date_added DESC',
				$table_name
			),
			ARRAY_A
		);

		// Clean any existing output buffers to prevent HTML from being included.
		while ( ob_get_level() > 0 ) {
			ob_end_clean();
		}

		// Set headers for the CSV file.
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="alertx_subscriptions_' . gmdate( 'Y-m-d' ) . '.csv"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// Open output stream.
		$output = fopen( 'php://output', 'w' );

		// Add BOM for proper UTF-8 encoding in Excel.
		fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

		// Write the CSV column headers.
		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_fputcsv -- CSV export streams directly to the browser via php://output; WP_Filesystem does not support output streams.
		fputcsv( $output, array( 'Product', 'Email', 'Date', 'Status' ) );

		// Write each row of notification data to the CSV.
		foreach ( $notifications as $notification ) {
			// Get product object.
			$product = wc_get_product( $notification['product_id'] );

			// Get product name.
			$product_name = $product ? $product->get_name() : __( 'Product not found', 'alertx-pro' );

			// Format status for better readability.
			$status = isset( $notification['status'] ) ? ucfirst( $notification['status'] ) : 'Unknown';

			// Prepare row data with proper order: Product, Email, Date, Status.
			$row_data = array(
				$product_name,
				$notification['email'],
				$notification['date_added'],
				$status,
			);

			// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_fputcsv -- CSV export streams directly to the browser via php://output; WP_Filesystem does not support output streams.
			fputcsv( $output, $row_data );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- WP_Filesystem cannot write to the php://output stream used for CSV export.
		fclose( $output );

		// Stop further script execution after file download.
		exit;
	}
}
