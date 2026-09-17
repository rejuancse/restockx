<?php
/**
 * Dashboard page methods for the RestockX Pro admin menu.
 *
 * @package RestockX\Admin\Pages
 */

namespace RestockX\Admin\Pages;

defined( 'ABSPATH' ) || exit;

/**
 * Trait Dashboard_Page
 *
 * Admin page methods moved out of RestockX_Menu. The trait is merged back
 * into the RestockX_Menu class, so every method keeps the exact same
 * visibility and $this behaviour as before the split.
 */
trait Dashboard_Page {

	/**
	 * Displays the admin page for managing stock notifications.
	 *
	 * This method handles CSV export requests, fetches stock notifications from the database,
	 * and includes the admin page template to render the notifications list.
	 *
	 * @return void
	 */
	public function restockx_admin_dashboard() {
		// Auto-fix: Ensure restock table exists.
		$this->ensure_restock_table_exists();

		// Prepare dashboard stats data.
		$dashboard_stats = $this->get_dashboard_stats();

		// Get demand ranking data.
		$demand_ranking = $this->get_demand_ranking();

		// Get recent activity data.
		$recent_activity = $this->get_recent_activity();

		// Get the 5 most recent subscribers.
		$recent_subscribers = $this->get_recent_subscribers();

		// Recent campaigns (Premium). The free plugin ships an empty list;
		// RestockX Pro fills it via this filter when it is active.
		$recent_campaigns = apply_filters( 'restockx_dashboard_recent_campaigns', array() );

		// Path to the admin page template file.
		$template_path = RESTOCKX_PATH . 'views/admin-dashboard.php';

		// Check if the template exists before including it.
		if ( file_exists( $template_path ) ) {
			include $template_path; // No parentheses needed for include.
		} else {
			// Template not found, display an error or a fallback message.
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Template file not found.', 'restockx' ) . '</p></div>';
		}
	}

	/**
	 * Ensure restock tracking table exists
	 */
	private function ensure_restock_table_exists() {
		global $wpdb;
		$restock_table = $wpdb->prefix . 'restockx_restock_tracking';

		// Check if table exists.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Real-time schema check is required before running dbDelta().
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $restock_table ) );

		if ( ! $table_exists ) {
			// Table doesn't exist, create it.
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
		}
	}

	/**
	 * Get dashboard statistics data.
	 *
	 * Results are cached for one minute to avoid repeating the aggregate
	 * queries on every dashboard load.
	 *
	 * @return array Dashboard stats array.
	 */
	private function get_dashboard_stats() {
		$stats = wp_cache_get( 'dashboard_stats', 'restockx_stats' );

		if ( false === $stats ) {
			$stats = $this->build_dashboard_stats();
			wp_cache_set( 'dashboard_stats', $stats, 'restockx_stats', MINUTE_IN_SECONDS );
		}

		return $stats;
	}

	/**
	 * Build the dashboard statistics data.
	 *
	 * @return array Dashboard stats array.
	 */
	private function build_dashboard_stats() {
		// Get total subscribers count.
		global $wpdb;
		$table_name = $wpdb->prefix . 'restockx_subscriptions';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom plugin table, no WP API equivalent; result is cached by get_dashboard_stats() for one minute.
		$total_subscribers = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(DISTINCT email) FROM %i',
				$table_name
			)
		);

		// Get notifications sent count (confirmed email subscribers).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom plugin table, no WP API equivalent; result is cached by get_dashboard_stats() for one minute.
		$notifications_sent = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT email) FROM %i WHERE status = 'confirmed'",
				$table_name
			)
		);

		// Get Unique Products
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom plugin table, no WP API equivalent; result is cached by get_dashboard_stats() for one minute.
        $unique_products = (int) $wpdb->get_var(
            $wpdb->prepare( 'SELECT COUNT(DISTINCT product_id) FROM %i', $table_name )
        ) ?: 0;

		// Get products still waiting count.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom plugin table, no WP API equivalent; result is cached by get_dashboard_stats() for one minute.
		$products_waiting = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(DISTINCT product_id) FROM %i',
				$table_name
			)
		);

		$stats = array(
			array(
				'icon_class'  => 'c1',
				'icon'        => '<svg width="43" height="39" viewBox="0 0 43 39" fill="none">
                            <path d="M7.8605 26.0928C5.34535 27.567 -1.24921 30.5772 2.76732 34.344C4.72936 36.184 6.91457 37.5 9.66191 37.5H25.3388C28.0862 37.5 30.2714 36.184 32.2334 34.344C36.2499 30.5772 29.6554 27.567 27.1402 26.0928C21.2423 22.6357 13.7585 22.6357 7.8605 26.0928Z" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M25.5004 9.5C25.5004 13.9183 21.9186 17.5 17.5004 17.5C13.0821 17.5 9.50037 13.9183 9.50037 9.5C9.50037 5.08172 13.0821 1.5 17.5004 1.5C21.9186 1.5 25.5004 5.08172 25.5004 9.5Z" stroke="#ffffff" stroke-width="3"/>
                            <path d="M36.5004 3.5V13.5M41.5004 8.5L31.5004 8.5" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>',
				'delta_class' => 'up',
				'data_count'  => $total_subscribers,
				'data_suffix' => '',
				'value'       => $total_subscribers,
				'label'       => 'Total subscribers',
				'sparkline'   => '<polyline points="0,20 12,17 24,19 36,12 48,14 60,6 72,4" fill="none" stroke="#3c06c5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />',
			),
			array(
				'icon_class'  => 'c2',
				'icon'        => '<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                            <path d="M22 2 11 13" />
                            <path d="M22 2 15 22l-4-9-9-4 20-7Z" />
                        </svg>',
				'delta_class' => 'up',
				'data_count'  => $notifications_sent,
				'data_suffix' => '',
				'value'       => $notifications_sent,
				'label'       => 'Notifications sent',
				'sparkline'   => '<polyline points="0,18 12,19 24,14 36,16 48,9 60,11 72,3" fill="none" stroke="#D90DD9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />',
			),
			array(
				'icon_class'  => 'c3',
				'icon'        => '<svg viewBox="0 0 24 24">
									<g fill="none" fill-rule="evenodd" stroke="none" stroke-width="1">
									<g transform="translate(-325.000000, -80.000000)">
									<g transform="translate(325.000000, 80.000000)">
									<polygon fill="#FFFFFF" fill-opacity="0.01" fill-rule="nonzero" points="24 0 0 0 0 24 24 24"/>
									<polygon points="22 7 12 2 2 7 2 17 12 22 22 17" stroke="#ffffff" stroke-linejoin="round" stroke-width="1.5"/>
									<line stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" x1="2" x2="12" y1="7" y2="12"/>
									<line stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" x1="12" x2="12" y1="22" y2="12"/>
									<line stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" x1="22" x2="12" y1="7" y2="12"/>
									<line stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" x1="17" x2="7" y1="4.5" y2="9.5"/>
									</g>
									</g>
									</g>
								</svg>',
				'delta_class' => 'up',
				'data_count'  => $unique_products,
				'data_suffix' => '',
				'value'       => $unique_products,
				'label'       => 'Unique Products',
				'sparkline'   => '<polyline points="0,10 12,13 24,8 36,11 48,7 60,9 72,5" fill="none" stroke="#FC301D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />',
			),
			array(
				'icon_class'  => 'c4',
				'icon'        => '<svg fill="#ffffff" viewBox="0 0 32 32" fill="none" stroke="#fff" stroke-width="1">
                            <path d="M15.888 31.977c-7.539 0-12.887-5.228-12.887-12.431 0-3.824 2.293-7.944 2.39-8.116 0.199-0.354 0.59-0.547 0.998-0.502 0.404 0.052 0.736 0.343 0.84 0.736 0.006 0.024 0.624 2.336 1.44 3.62 0.548 0.864 1.104 1.475 1.729 1.899-0.423-1.833-0.747-4.591-0.22-7.421 1.448-7.768 7.562-9.627 7.824-9.701 0.337-0.097 0.695-0.010 0.951 0.223 0.256 0.235 0.373 0.586 0.307 0.927-0.010 0.054-1.020 5.493 1.123 10.127 0.195 0.421 0.466 0.91 0.758 1.399 0.083-0.672 0.212-1.386 0.41-2.080 0.786-2.749 2.819-3.688 2.904-3.726 0.339-0.154 0.735-0.104 1.027 0.126 0.292 0.231 0.433 0.603 0.365 0.969-0.011 0.068-0.294 1.938 1.298 4.592 1.438 2.396 1.852 3.949 1.852 6.928 0 7.203-5.514 12.43-13.111 12.43zM6.115 14.615c-0.549 1.385-1.115 3.226-1.115 4.931 0 6.044 4.506 10.43 10.887 10.43 6.438 0 11.11-4.386 11.11-10.431 0-2.611-0.323-3.822-1.567-5.899-0.832-1.386-1.243-2.633-1.439-3.625-0.198 0.321-0.382 0.712-0.516 1.184-0.61 2.131-0.456 4.623-0.454 4.649 0.029 0.446-0.242 0.859-0.664 1.008s-0.892 0.002-1.151-0.364c-0.075-0.107-1.854-2.624-2.637-4.32-1.628-3.518-1.601-7.323-1.434-9.514-1.648 0.96-4.177 3.104-4.989 7.466-0.791 4.244 0.746 8.488 0.762 8.529 0.133 0.346 0.063 0.739-0.181 1.018-0.245 0.277-0.622 0.4-0.986 0.313-0.124-0.030-2.938-0.762-4.761-3.634-0.325-0.514-0.617-1.137-0.864-1.742z"></path>
                        </svg>',
				'delta_class' => 'up',
				'data_count'  => $products_waiting,
				'data_suffix' => '',
				'value'       => $products_waiting,
				'label'       => 'High Demand Items',
				'sparkline'   => '<polyline points="0,10 12,13 24,8 36,11 48,7 60,9 72,5" fill="none" stroke="#FC301D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />',
			),
		);

		return $stats;
	}

	/**
	 * Get demand ranking data (products with most subscribers)
	 *
	 * Results are cached for one minute to avoid repeating the aggregate
	 * queries and the product lookups on every dashboard load.
	 *
	 * @return array Demand ranking data with product info and subscriber counts
	 */
	public function get_demand_ranking() {
		$demand_ranking = wp_cache_get( 'demand_ranking', 'restockx_stats' );

		if ( false === $demand_ranking ) {
			$demand_ranking = $this->build_demand_ranking();
			wp_cache_set( 'demand_ranking', $demand_ranking, 'restockx_stats', MINUTE_IN_SECONDS );
		}

		return $demand_ranking;
	}

	/**
	 * Build the demand ranking data (products with most subscribers)
	 *
	 * @return array Demand ranking data with product info and subscriber counts
	 */
	private function build_demand_ranking() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'restockx_subscriptions';

		// Get products with most subscribers, ordered by count (pending and sent both).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom plugin table, no WP API equivalent; result is cached by get_demand_ranking() for one minute.
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
                product_id,
                COUNT(DISTINCT email) as subscriber_count
            FROM %i
            WHERE status IN ('pending', 'confirmed')
            GROUP BY product_id
            ORDER BY subscriber_count DESC
            LIMIT 5",
				$table_name
			),
			ARRAY_A
		);

		$demand_ranking = array();

		if ( ! empty( $results ) ) {
			// Get max count for percentage calculation.
			$max_count = ! empty( $results[0]['subscriber_count'] ) ? intval( $results[0]['subscriber_count'] ) : 1;

			$rank = 1;
			foreach ( $results as $result ) {
				$product_id       = intval( $result['product_id'] );
				$subscriber_count = intval( $result['subscriber_count'] );

				// Get product details.
				$product = wc_get_product( $product_id );

				if ( ! $product ) {
					continue;
				}

				$product_name  = $product->get_name();
				$product_title = $product->get_title();

				// Handle product variations.
				if ( $product->is_type( 'variation' ) ) {
					$parent_product = wc_get_product( $product->get_parent_id() );
					if ( $parent_product ) {
						$product_name         = $parent_product->get_name();
						$variation_attributes = $product->get_variation_attributes();
						$attribute_labels     = array();

						foreach ( $variation_attributes as $attr_name => $attr_value ) {
							// Get attribute label.
							$taxonomy = str_replace( 'attribute_', '', $attr_name );
							if ( taxonomy_exists( $taxonomy ) ) {
								$term = get_term_by( 'slug', $attr_value, $taxonomy );
								if ( $term ) {
									$attr_label = $term->name;
								} else {
									$attr_label = $attr_value;
								}
							} else {
								$attr_label = ucfirst( str_replace( '-', ' ', $attr_value ) );
							}
							$attribute_labels[] = $attr_label;
						}

						$variant_name = ! empty( $attribute_labels ) ? implode( ' / ', $attribute_labels ) : 'All variations combined';
					} else {
						$variant_name = 'All variations combined';
					}
				} else {
					$variant_name = 'All variations combined';
				}

				// Calculate percentage.
				$percentage = $max_count > 0 ? round( ( $subscriber_count / $max_count ) * 100 ) : 0;

				$demand_ranking[] = array(
					'rank'             => str_pad( $rank, 2, '0', STR_PAD_LEFT ),
					'product_name'     => $product_name,
					'variant_name'     => $variant_name,
					'subscriber_count' => $subscriber_count,
					'max_count'        => $max_count,
					'percentage'       => $percentage,
					'product_id'       => $product_id,
				);

				++$rank;
			}
		}

		return $demand_ranking;
	}

	/**
	 * Get recent subscription activity
	 *
	 * Results are cached for one minute to avoid repeating the aggregate
	 * queries and the product lookups on every dashboard load.
	 *
	 * @return array Recent activity data showing products that recently received subscriptions
	 */
	public function get_recent_activity() {
		$recent_activity = wp_cache_get( 'recent_activity', 'restockx_stats' );

		if ( false === $recent_activity ) {
			$recent_activity = $this->build_recent_activity();
			wp_cache_set( 'recent_activity', $recent_activity, 'restockx_stats', MINUTE_IN_SECONDS );
		}

		return $recent_activity;
	}

	/**
	 * Build the recent subscription activity
	 *
	 * @return array Recent activity data showing products that recently received subscriptions
	 */
	private function build_recent_activity() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'restockx_subscriptions';

		// Get recent subscriptions grouped by product (last 5 unique products with newest subscription).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom plugin table, no WP API equivalent; result is cached by get_recent_activity() for one minute.
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
                product_id,
                MAX(date_added) as latest_date,
                COUNT(DISTINCT email) as subscriber_count
            FROM %i
            WHERE status IN ('pending', 'confirmed')
            GROUP BY product_id
            ORDER BY latest_date DESC
            LIMIT 5",
				$table_name
			),
			ARRAY_A
		);

		$recent_activity = array();

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$product_id       = intval( $result['product_id'] );
				$latest_date      = $result['latest_date'];
				$subscriber_count = intval( $result['subscriber_count'] );

				// Get product details.
				$product = wc_get_product( $product_id );

				if ( ! $product ) {
					continue;
				}

				$product_name = $product->get_name();

				// Handle product variations.
				if ( $product->is_type( 'variation' ) ) {
					$parent_product = wc_get_product( $product->get_parent_id() );
					if ( $parent_product ) {
						$product_name         = $parent_product->get_name();
						$variation_attributes = $product->get_variation_attributes();
						$attribute_labels     = array();

						foreach ( $variation_attributes as $attr_name => $attr_value ) {
							// Get attribute label.
							$taxonomy = str_replace( 'attribute_', '', $attr_name );
							if ( taxonomy_exists( $taxonomy ) ) {
								$term = get_term_by( 'slug', $attr_value, $taxonomy );
								if ( $term ) {
									$attr_label = $term->name;
								} else {
									$attr_label = $attr_value;
								}
							} else {
								$attr_label = ucfirst( str_replace( '-', ' ', $attr_value ) );
							}
							$attribute_labels[] = $attr_label;
						}

						$variant_name  = ! empty( $attribute_labels ) ? implode( ' / ', $attribute_labels ) : '';
						$product_name .= ! empty( $variant_name ) ? ' (' . $variant_name . ')' : '';
					}
				}

				// Calculate time ago.
				// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- Compared against current_time( 'mysql' ) values stored in the site-local timezone.
				$time_diff = human_time_diff( strtotime( $latest_date ), current_time( 'timestamp' ) );
				$time_ago  = sprintf( '%s ago', $time_diff );

				// Format message based on subscriber count.
				if ( 1 === $subscriber_count ) {
					$message = sprintf( '1 person subscribed to %s', $product_name );
				} else {
					$message = sprintf( '%d people subscribed to %s', $subscriber_count, $product_name );
				}

				$recent_activity[] = array(
					'type'             => 'product_subscription',
					'message'          => $message,
					'time'             => $time_ago,
					'product_id'       => $product_id,
					'subscriber_count' => $subscriber_count,
				);
			}
		}

		return $recent_activity;
	}

	/**
	 * Get the 5 most recent subscribers for the dashboard widget.
	 *
	 * Results are cached for one minute to avoid repeating the query and
	 * the product lookups on every dashboard load.
	 *
	 * @return array Recent subscribers with email, product name, status and time.
	 */
	public function get_recent_subscribers() {
		$recent_subscribers = wp_cache_get( 'recent_subscribers', 'restockx_stats' );

		if ( false === $recent_subscribers ) {
			$recent_subscribers = $this->build_recent_subscribers();
			wp_cache_set( 'recent_subscribers', $recent_subscribers, 'restockx_stats', MINUTE_IN_SECONDS );
		}

		return $recent_subscribers;
	}

	/**
	 * Build the 5 most recent subscribers for the dashboard widget.
	 *
	 * @return array Recent subscribers with email, product name, status and time.
	 */
	private function build_recent_subscribers() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'restockx_subscriptions';

		// Table may not exist yet on fresh installs.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Real-time schema check; result is cached by get_recent_subscribers() for one minute.
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );
		if ( ! $table_exists ) {
			return array();
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom plugin table, no WP API equivalent; result is cached by get_recent_subscribers() for one minute.
		$results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT email, product_id, status, date_added
            FROM %i
            ORDER BY date_added DESC
            LIMIT 5',
				$table_name
			),
			ARRAY_A
		);

		$recent_subscribers = array();

		foreach ( (array) $results as $result ) {
			$product_id = intval( $result['product_id'] );

			// Fall back to the ID when the product no longer exists.
			$product_name = sprintf( '#%d', $product_id );
			$product      = $product_id ? wc_get_product( $product_id ) : false;
			if ( $product ) {
				$product_name = $product->get_name();
			}

			$date_added = ! empty( $result['date_added'] ) ? strtotime( $result['date_added'] ) : 0;
			// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- Compared against current_time( 'mysql' ) values stored in the site-local timezone.
			$time_ago = $date_added ? sprintf( '%s ago', human_time_diff( $date_added, current_time( 'timestamp' ) ) ) : '-';

			$recent_subscribers[] = array(
				'email'        => $result['email'],
				'product_name' => $product_name,
				'status'       => $result['status'],
				'time'         => $time_ago,
			);
		}

		return $recent_subscribers;
	}

	/**
	 * Displays the notifications admin page.
	 *
	 * @return void
	 */
	public function restockx_notifications() {
		// Path to the admin page template file.
		$template_path = RESTOCKX_PATH . 'views/admin-notifications.php';

		// Check if the template exists before including it.
		if ( file_exists( $template_path ) ) {
			include $template_path; // No parentheses needed for include.
		} else {
			// Template not found, display an error or a fallback message.
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Template file not found.', 'restockx' ) . '</p></div>';
		}
	}
}
