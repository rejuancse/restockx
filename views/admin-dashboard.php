<?php
/**
 * Admin Dashboard template
 *
 * @version 1.0.0
 */
defined( 'ABSPATH' ) || exit;
?>

<div id="restockx" class="restockx wrap alartx-sections">
	<?php include_once RESTOCKX_PATH .'/views/global/header.php'; ?>

	<main class="page page-dashboard">
		<div class="page-header">
			<div>
				<div class="page-h1"><?php esc_html_e('Dashboard', 'restockx'); ?></div>
				<div class="page-desc">
					<?php esc_html_e('Track waitlists, alerts and conversions in real time', 'restockx'); ?>
				</div>
			</div>
		</div>

		<div class="kpi-row">
			<?php if ( ! empty( $dashboard_stats ) && is_array( $dashboard_stats ) ) : ?>
				<?php foreach ( $dashboard_stats as $restockx_stat ) : ?>
					<div class="kpi-card">
						<div class="kpi-top">
							<div class="kpi-value" <?php
								if ( ! empty( $restockx_stat['data_count'] ) ) {
									echo 'data-count="' . esc_attr( $restockx_stat['data_count'] ) . '"';
								}
								if ( ! empty( $restockx_stat['data_suffix'] ) ) {
									echo ' data-suffix="' . esc_attr( $restockx_stat['data_suffix'] ) . '"';
								}
							?>>
								<?php echo esc_html( $restockx_stat['value'] ); ?>
							</div>

							<div class="kpi-icon <?php echo esc_attr( $restockx_stat['icon_class'] ?? 'c1' ); ?>">
								<?php echo $restockx_stat['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
						</div>

						<div class="kpi-bottom">
							<div class="kpi-label"><?php echo esc_html( $restockx_stat['label'] ); ?></div>
							<?php if ( ! empty( $restockx_stat['sparkline'] ) ) : ?>
								<svg class="kpi-spark" width="72" height="26" viewBox="0 0 72 26">
									<?php echo $restockx_stat['sparkline']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</svg>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<!-- Subscribers -->
		<div class="grid-50">
			<section class="section pulse-section">
				<div class="section-head">
					<div>
						<div class="section-title">
							<?php esc_html_e( 'Demand ranking', 'restockx' ); ?>
						</div>
						<div class="section-desc">
							<?php esc_html_e( 'Out-of-stock products with the most people waiting', 'restockx' ); ?>
						</div>
					</div>
					<div class="pulse-live">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=restockx-subscribers' ) ); ?>" class="section-action">
							<?php esc_html_e( 'View subscribers →', 'restockx' ); ?>
						</a>
					</div>
				</div>

				<div class="pulse-list" id="pulseList">
					<?php if ( ! empty( $demand_ranking ) ) : ?>
						<?php foreach ( $demand_ranking as $restockx_item ) : ?>
							<div class="pulse-row" data-max="<?php echo esc_attr( $restockx_item['max_count'] ); ?>">
								<div class="pulse-rank"><?php echo esc_html( $restockx_item['rank'] ); ?></div>
								<div class="pulse-info">
									<div class="pname"><?php echo esc_html( $restockx_item['product_name'] ); ?></div>
									<div class="pvariant"><?php echo esc_html( $restockx_item['variant_name'] ); ?></div>
								</div>
								<div class="pulse-track">
									<div class="pulse-fill" style="width:<?php echo esc_attr( $restockx_item['percentage'] ); ?>%">
										<span class="pulse-dot"></span>
									</div>
								</div>
								<div class="pulse-count">
									<div class="num" data-target="<?php echo esc_attr( $restockx_item['subscriber_count'] ); ?>"><?php echo esc_html( $restockx_item['subscriber_count'] ); ?></div>
									<div class="lbl"><?php esc_html_e( 'waiting', 'restockx' ); ?></div>
								</div>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<div class="pulse-row no-data">
							<div class="pulse-info">
								<div class="pname"><?php esc_html_e( 'No demand data available', 'restockx' ); ?></div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</section>

			<section class="section">
				<div class="section-head">
					<div>
						<div class="section-title">
							<?php esc_html_e( 'Recent activity', 'restockx' ); ?>
						</div>
						<div class="section-desc">
							<?php esc_html_e( 'Live from the notification queue', 'restockx' ); ?>
						</div>
					</div>
					<a class="section-action" href="<?php echo esc_url( admin_url( 'admin.php?page=restockx-subscribers' ) ); ?>">
						<?php esc_html_e( 'View all', 'restockx' ); ?>
						<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
							<path d="M9 6l6 6-6 6" />
						</svg>
					</a>
				</div>
				<div class="pulse-list activity-list">
					<?php if ( ! empty( $recent_activity ) ) : ?>
						<?php foreach ( $recent_activity as $restockx_activity ) : ?>
							<div class="pulse-row activity">
								<div class="recent-wrap">
									<div class="pulse-rank">
										<div class="mini-dot"></div>
									</div>
									<div class="pulse-info">
										<div class="pname"><?php echo esc_html( $restockx_activity['message'] ); ?></div>
										<div class="pvariant"><?php echo esc_html( $restockx_activity['time'] ); ?></div>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<div class="pulse-row no-data">
							<div class="pulse-info">
								<div class="pname"><?php esc_html_e( 'No recent activity', 'restockx' ); ?></div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</section>

			<section class="section campaign pulse-section">
				<div class="section-head">
					<div>
						<div class="section-title">
							<?php esc_html_e( 'Recent Campaign', 'restockx' ); ?>
						</div>
						<div class="section-desc">
							<?php esc_html_e( 'Your 5 latest email campaigns and their delivery status', 'restockx' ); ?>
						</div>
					</div>
					<?php if ( ! restockx_show_upgrade_cta() ) : ?>
						<div class="pulse-live">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=restockx-campaigns' ) ); ?>" class="section-action">
								<?php esc_html_e( 'View Campaign →', 'restockx' ); ?>
							</a>
						</div>
					<?php endif; ?>
				</div>

				<?php if ( restockx_show_upgrade_cta() ) : ?>
					<div class="blur">
						<div class="pulse-list">
							<div class="pulse-row no-data">
								<div class="pulse-info">
									<div class="pname">
										<img src="<?php echo esc_url( RESTOCKX_URL . 'assets/images/cam-list.jpg' ); ?>" alt="">
									</div>
								</div>
							</div>
						</div>

						<a class="go-premium go-premium-sm" href="https://contra.com/products/hgwSzl7o-restock-x-for-woo-commerce" target="_blank" rel="noopener">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<rect x="3" y="11" width="18" height="11" rx="2"></rect>
								<path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
							</svg>
							<?php esc_html_e( 'Go Premium', 'restockx' ); ?>
						</a>
					</div>
				<?php else : ?>
					<div class="pulse-list">
						<?php if ( ! empty( $recent_campaigns ) ) : ?>
							<?php foreach ( $recent_campaigns as $campaign ) : ?>
								<div class="pulse-row">
									<div class="pulse-rank"><?php echo esc_html( $campaign['rank'] ); ?></div>
									<div class="pulse-info">
										<div class="pname"><?php echo esc_html( $campaign['title'] ); ?></div>
										<div class="pvariant"><?php echo esc_html( $campaign['subject'] ); ?> · <?php echo esc_html( $campaign['status'] ); ?> · <?php echo esc_html( $campaign['time'] ); ?></div>
									</div>
									<div class="pulse-count">
										<div class="num" data-target="<?php echo esc_attr( $campaign['emails_sent'] ); ?>"><?php echo esc_html( $campaign['emails_sent'] ); ?></div>
										<div class="lbl"><?php esc_html_e( 'sent', 'restockx' ); ?></div>
									</div>
								</div>
							<?php endforeach; ?>
						<?php else : ?>
							<div class="pulse-row">
								<div class="pulse-info">
									<div class="pname"><?php esc_html_e( 'No campaigns yet', 'restockx' ); ?></div>
								</div>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</section>

			<section class="section recent-subscribers">
				<div class="section-head">
					<div>
						<div class="section-title">
							<?php esc_html_e( 'Recent Subscribers', 'restockx' ); ?>
						</div>
						<div class="section-desc">
							<?php esc_html_e( 'Latest stock alert subscriptions with their status', 'restockx' ); ?>
						</div>
					</div>
					<a class="section-action" href="<?php echo esc_url( admin_url( 'admin.php?page=restockx-subscribers' ) ); ?>">
						<?php esc_html_e( 'View all', 'restockx' ); ?>
						<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
							<path d="M9 6l6 6-6 6" />
						</svg>
					</a>
				</div>
				<div class="pulse-list activity-list">
					<?php if ( ! empty( $recent_subscribers ) ) : ?>
						<?php foreach ( $recent_subscribers as $restockx_subscriber ) : ?>
							<div class="pulse-row activity">
								<div class="recent-wrap">
									<div class="pulse-rank">
										<div class="mini-dot"></div>
									</div>
									<div class="pulse-info">
										<div class="pname"><?php echo esc_html( $restockx_subscriber['email'] ); ?></div>
										<div class="pvariant"><?php echo esc_html( $restockx_subscriber['product_name'] ); ?> · <?php echo esc_html( $restockx_subscriber['time'] ); ?></div>
									</div>
								</div>

								<span class="status-pill subscription-status status-<?php echo esc_attr( sanitize_html_class( strtolower( $restockx_subscriber['status'] ) ) ); ?>">
									<?php echo esc_html( ucfirst( $restockx_subscriber['status'] ) ); ?>
								</span>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<div class="pulse-row no-data">
							<div class="pulse-info">
								<div class="pname"><?php esc_html_e( 'No subscribers yet', 'restockx' ); ?></div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</section>
		</div>
	</main>
</div>
