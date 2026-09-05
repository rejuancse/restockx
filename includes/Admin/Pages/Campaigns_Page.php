<?php
/**
 * Campaigns page methods for the AlertX Pro admin menu.
 *
 * @package Alertx\Admin\Pages
 */

namespace Alertx\Admin\Pages;

defined( 'ABSPATH' ) || exit;

/**
 * Trait Campaigns_Page
 *
 * Admin page methods moved out of Alertx_Menu. The trait is merged back
 * into the Alertx_Menu class, so every method keeps the exact same
 * visibility and $this behaviour as before the split.
 */
trait Campaigns_Page {

	/**
	 * Get campaign statistics data for the campaigns page KPI cards.
	 *
	 * Results are cached for one minute to avoid repeating the aggregate
	 * queries and the coupon/order scan on every page load.
	 *
	 * @return array Campaign stats (active, scheduled, emails_sent, redemptions).
	 */
	private function get_campaign_stats() {
		$stats = wp_cache_get( 'campaign_stats', 'alertx_stats' );

		if ( false === $stats ) {
			$stats = $this->build_campaign_stats();
			wp_cache_set( 'campaign_stats', $stats, 'alertx_stats', MINUTE_IN_SECONDS );
		}

		return $stats;
	}

	/**
	 * Build the campaign statistics data for the campaigns page KPI cards.
	 *
	 * @return array Campaign stats (active, scheduled, emails_sent, redemptions).
	 */
	private function build_campaign_stats() {
		global $wpdb;
		$campaigns_table = $wpdb->prefix . 'alertx_campaigns';

		// Active campaigns — sending or sent. Drafts and scheduled are not active.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom plugin table, no WP API equivalent; result is cached by get_campaign_stats() for one minute.
		$active_campaigns = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM %i WHERE camp_status IN ('sending', 'sent')",
				$campaigns_table
			)
		);

		// Scheduled campaigns.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom plugin table, no WP API equivalent; result is cached by get_campaign_stats() for one minute.
		$scheduled_campaigns = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM %i WHERE camp_status = 'scheduled'",
				$campaigns_table
			)
		);

		// Emails delivered — total successful sends across all campaigns,.
		// straight from the campaigns table (no guesswork attribution).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom plugin table, no WP API equivalent; result is cached by get_campaign_stats() for one minute.
		$emails_sent = (int) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COALESCE(SUM(emails_sent), 0) FROM %i',
				$campaigns_table
			)
		);

		// Coupon uses this month — orders that used a campaign coupon this.
		// month. Counted from the live orders (works with HPOS) because the.
		// wc_order_coupon_lookup table is only filled by WooCommerce Admin's.
		// scheduled sync and is frequently empty.
		$redemptions = 0;

		if ( class_exists( 'WooCommerce' ) && function_exists( 'wc_get_orders' ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom plugin table, no WP API equivalent; result is cached by get_campaign_stats() for one minute.
			$coupon_codes = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT DISTINCT discount_code FROM %i WHERE discount_code != ''",
					$campaigns_table
				)
			);

			if ( ! empty( $coupon_codes ) ) {
				$campaign_codes = array_map(
					function ( $code ) {
						return strtolower( trim( (string) $code ) );
					},
					(array) $coupon_codes
				);

				// Local start of the current month.
				$orders = wc_get_orders(
					array(
						'status'       => array( 'processing', 'completed' ),
						// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- Compared against current_time( 'mysql' ) values stored in the site-local timezone.
						'date_created' => '>=' . gmdate( 'Y-m-01 00:00:00', current_time( 'timestamp' ) ),
						'limit'        => -1,
					)
				);

				foreach ( $orders as $order ) {
					foreach ( $order->get_coupon_codes() as $used_code ) {
						if ( in_array( strtolower( trim( $used_code ) ), $campaign_codes, true ) ) {
							++$redemptions;
							break; // Count each order once.
						}
					}
				}
			}
		}

		return array(
			'active'      => $active_campaigns,
			'scheduled'   => $scheduled_campaigns,
			'emails_sent' => $emails_sent,
			'redemptions' => $redemptions,
		);
	}

	/**
	 * Count the published products restricted to a WooCommerce coupon.
	 *
	 * Mirrors Campaign_Ajax::get_coupon_product_count() — the same source
	 * the email's {product_list} is built from.
	 *
	 * @param string $code Coupon code.
	 * @return int Product count.
	 */
	private function get_campaign_coupon_product_count( $code ) {
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
				++$count;
			}
		}

		return $count;
	}

	/**
	 * Displays the admin page for managing campaigns.
	 *
	 * Fetches campaigns from the database with pagination and includes the
	 * admin page template to render the campaigns list.
	 *
	 * @return void
	 */
	public function alertx_campaigns() {
		global $wpdb;

		// Table name for campaigns in the database.
		$campaigns_table = $wpdb->prefix . 'alertx_campaigns';

		// Number of campaigns to show per page.
		$alertx_items_per_page = 10;

		// Get the current page number, ensuring it's valid.
		$paged  = isset( $_GET['paged'] ) && is_numeric( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public token-verified endpoint accessed via email links; a nonce cannot be supplied.
		$offset = ( $paged - 1 ) * $alertx_items_per_page;

		// Fetch the total number of campaigns to calculate pagination.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom plugin table, no WP API equivalent; admin list pagination must reflect real-time data.
		$alertx_total_campaigns = (int) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM %i',
				$campaigns_table
			)
		);

		// Fetch the campaigns for the current page, using SQL LIMIT and OFFSET.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom plugin table, no WP API equivalent; admin list pagination must reflect real-time data.
		$alertx_campaigns = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM %i ORDER BY created_at DESC LIMIT %d OFFSET %d',
				$campaigns_table,
				$alertx_items_per_page,
				$offset
			)
		);

		// Attach the live product count from the WooCommerce coupon - the.
		// same source the email's {product_list} is built from.
		if ( ! empty( $alertx_campaigns ) ) {
			$coupon_counts = array();

			foreach ( $alertx_campaigns as $key => $campaign ) {
				$code = isset( $campaign->discount_code ) ? $campaign->discount_code : '';
				$alertx_campaigns[ $key ]->coupon_product_count = 0;

				if ( '' === $code ) {
					continue;
				}

				// Same coupon used by several campaigns? Count only once.
				if ( ! isset( $coupon_counts[ $code ] ) ) {
					$coupon_counts[ $code ] = $this->get_campaign_coupon_product_count( $code );
				}

				$alertx_campaigns[ $key ]->coupon_product_count = $coupon_counts[ $code ];
			}
		}

		// Get campaign statistics data.
		$alertx_campaign_stats = $this->get_campaign_stats();

		// Path to the admin page template file.
		$template_path = ALERTX_PATH . 'views/admin-campaigns.php';

		// Check if the template exists before including it.
		if ( file_exists( $template_path ) ) {
			include $template_path; // No parentheses needed for include.
		} else {
			// Template not found, display an error or a fallback message.
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Template file not found.', 'alertx-pro' ) . '</p></div>';
		}
	}
}
