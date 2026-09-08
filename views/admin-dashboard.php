<?php
/**
 * Admin Dashboard template
 *
 * @version 1.0.0
 */
defined( 'ABSPATH' ) || exit;
?>

<div id="alertx" class="alertx wrap alartx-sections">
	<?php include_once ALERTX_PATH .'/views/global/header.php'; ?>

	<main class="page page-dashboard">
		<div class="page-header">
			<div>
				<div class="page-h1"><?php esc_html_e('Dashboard', 'alertx'); ?></div>
				<div class="page-desc">
					<?php esc_html_e('Track waitlists, alerts and conversions in real time', 'alertx'); ?>
				</div>
			</div>
		</div>

		<div class="kpi-row">
			<?php if ( ! empty( $dashboard_stats ) && is_array( $dashboard_stats ) ) : ?>
				<?php foreach ( $dashboard_stats as $alertx_stat ) : ?>
					<div class="kpi-card">
						<div class="kpi-top">
							<div class="kpi-value" <?php
								if ( ! empty( $alertx_stat['data_count'] ) ) {
									echo 'data-count="' . esc_attr( $alertx_stat['data_count'] ) . '"';
								}
								if ( ! empty( $alertx_stat['data_suffix'] ) ) {
									echo ' data-suffix="' . esc_attr( $alertx_stat['data_suffix'] ) . '"';
								}
							?>>
								<?php echo esc_html( $alertx_stat['value'] ); ?>
							</div>

							<div class="kpi-icon <?php echo esc_attr( $alertx_stat['icon_class'] ?? 'c1' ); ?>">
								<?php echo $alertx_stat['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
						</div>

						<div class="kpi-bottom">
							<div class="kpi-label"><?php echo esc_html( $alertx_stat['label'] ); ?></div>
							<?php if ( ! empty( $alertx_stat['sparkline'] ) ) : ?>
								<svg class="kpi-spark" width="72" height="26" viewBox="0 0 72 26">
									<?php echo $alertx_stat['sparkline']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
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
							<?php esc_html_e( 'Demand ranking', 'alertx' ); ?>
						</div>
						<div class="section-desc">
							<?php esc_html_e( 'Out-of-stock products with the most people waiting', 'alertx' ); ?>
						</div>
					</div>
					<div class="pulse-live">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=alertxwc-subscribers' ) ); ?>" class="section-action">
							<?php esc_html_e( 'View subscribers →', 'alertx' ); ?>
						</a>
					</div>
				</div>
				<div class="pulse-list" id="pulseList">
					<?php if ( ! empty( $demand_ranking ) ) : ?>
						<?php foreach ( $demand_ranking as $alertx_item ) : ?>
							<div class="pulse-row" data-max="<?php echo esc_attr( $alertx_item['max_count'] ); ?>">
								<div class="pulse-rank"><?php echo esc_html( $alertx_item['rank'] ); ?></div>
								<div class="pulse-info">
									<div class="pname"><?php echo esc_html( $alertx_item['product_name'] ); ?></div>
									<div class="pvariant"><?php echo esc_html( $alertx_item['variant_name'] ); ?></div>
								</div>
								<div class="pulse-track">
									<div class="pulse-fill" style="width:<?php echo esc_attr( $alertx_item['percentage'] ); ?>%">
										<span class="pulse-dot"></span>
									</div>
								</div>
								<div class="pulse-count">
									<div class="num" data-target="<?php echo esc_attr( $alertx_item['subscriber_count'] ); ?>"><?php echo esc_html( $alertx_item['subscriber_count'] ); ?></div>
									<div class="lbl"><?php esc_html_e( 'waiting', 'alertx' ); ?></div>
								</div>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<div class="pulse-row no-data">
							<div class="pulse-info">
								<div class="pname"><?php esc_html_e( 'No demand data available', 'alertx' ); ?></div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</section>

			<section class="section">
				<div class="section-head">
					<div>
						<div class="section-title">
							<?php esc_html_e( 'Recent activity', 'alertx' ); ?>
						</div>
						<div class="section-desc">
							<?php esc_html_e( 'Live from the notification queue', 'alertx' ); ?>
						</div>
					</div>
					<a class="section-action" href="<?php echo esc_url( admin_url( 'admin.php?page=alertxwc-subscribers' ) ); ?>">
						<?php esc_html_e( 'View all', 'alertx' ); ?>
						<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
							<path d="M9 6l6 6-6 6" />
						</svg>
					</a>
				</div>
				<div class="pulse-list activity-list">
					<?php if ( ! empty( $recent_activity ) ) : ?>
						<?php foreach ( $recent_activity as $alertx_activity ) : ?>
							<div class="pulse-row activity">
								<div class="recent-wrap">
									<div class="pulse-rank">
										<div class="mini-dot"></div>
									</div>
									<div class="pulse-info">
										<div class="pname"><?php echo esc_html( $alertx_activity['message'] ); ?></div>
										<div class="pvariant"><?php echo esc_html( $alertx_activity['time'] ); ?></div>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<div class="pulse-row no-data">
							<div class="pulse-info">
								<div class="pname"><?php esc_html_e( 'No recent activity', 'alertx' ); ?></div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</section>

			<section class="section recent-subscribers">
				<div class="section-head">
					<div>
						<div class="section-title">
							<?php esc_html_e( 'Recent Subscribers', 'alertx' ); ?>
						</div>
						<div class="section-desc">
							<?php esc_html_e( 'Latest stock alert subscriptions with their status', 'alertx' ); ?>
						</div>
					</div>
					<a class="section-action" href="<?php echo esc_url( admin_url( 'admin.php?page=alertxwc-subscribers' ) ); ?>">
						<?php esc_html_e( 'View all', 'alertx' ); ?>
						<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
							<path d="M9 6l6 6-6 6" />
						</svg>
					</a>
				</div>
				<div class="pulse-list activity-list">
					<?php if ( ! empty( $recent_subscribers ) ) : ?>
						<?php foreach ( $recent_subscribers as $alertx_subscriber ) : ?>
							<div class="pulse-row activity">
								<div class="recent-wrap">
									<div class="pulse-rank">
										<div class="mini-dot"></div>
									</div>
									<div class="pulse-info">
										<div class="pname"><?php echo esc_html( $alertx_subscriber['email'] ); ?></div>
										<div class="pvariant"><?php echo esc_html( $alertx_subscriber['product_name'] ); ?> · <?php echo esc_html( $alertx_subscriber['time'] ); ?></div>
									</div>
								</div>

								<span class="status-pill subscription-status status-<?php echo esc_attr( sanitize_html_class( strtolower( $alertx_subscriber['status'] ) ) ); ?>">
									<?php echo esc_html( ucfirst( $alertx_subscriber['status'] ) ); ?>
								</span>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<div class="pulse-row no-data">
							<div class="pulse-info">
								<div class="pname"><?php esc_html_e( 'No subscribers yet', 'alertx' ); ?></div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</section>
		</div>
	</main>
</div>
