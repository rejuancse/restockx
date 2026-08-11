<?php
/**
 * Admin settings template
 *
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div id="alertx" class="main wrap">
	<header class="alertx-settings-header">
		<div class="alertx-header-left">
			<div class="alertx-admin-header">
				<img src="<?php echo ALERTX_URL . 'assets/src/img/logo.png'; ?>" alt="">
			</div>
		</div>

		<div class="alertx-header-right">
			<button class="icon-btn">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
					<path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
					<path d="M13.7 21a2 2 0 0 1-3.4 0" />
				</svg>
				<span class="dot"></span>
			</button>
			<span>
				<?php
					// translators: %s: plugin version number
					$version_text = esc_html__( 'Current Version: %s', 'multi-order-tracker' );
					echo wp_kses_post( sprintf( $version_text, '<strong>' . esc_html( ALERTX_VERSION ) . '</strong>' ) );
				?>
			</span>
		</div>
	</header>

	<section class="page active" id="page-dashboard">
		<div class="page-header">
			<div>
				<div class="page-eyebrow">Dashboard</div>
				<div class="page-h1">Good afternoon, Sarah</div>
				<div class="page-desc">Here's how your waitlists and alerts are performing today.</div>
			</div>
			<div class="page-actions">
				<button class="btn btn-secondary">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
						<path d="M7 10l5 5 5-5" />
						<path d="M12 15V3" />
					</svg> Export report </button>
				<button class="btn btn-primary">
					<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
						<path d="M12 5v14M5 12h14" />
					</svg> New alert rule </button>
			</div>
		</div>
		<section class="kpi-row">
			<div class="kpi-card">
				<div class="kpi-top">
					<div class="kpi-icon c1">
						<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
							<path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" />
							<circle cx="10" cy="7" r="4" />
						</svg>
					</div>
					<span class="kpi-delta up">▲ 8.2%</span>
				</div>
				<div class="kpi-value" data-count="12847">0</div>
				<div class="kpi-bottom">
					<div class="kpi-label">Total subscribers</div>
					<svg class="kpi-spark" width="72" height="26" viewBox="0 0 72 26">
						<polyline points="0,20 12,17 24,19 36,12 48,14 60,6 72,4" fill="none" stroke="var(--alertx-violet)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</div>
			</div>
			<div class="kpi-card">
				<div class="kpi-top">
					<div class="kpi-icon c2">
						<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
							<path d="M22 2 11 13" />
							<path d="M22 2 15 22l-4-9-9-4 20-7Z" />
						</svg>
					</div>
					<span class="kpi-delta up">▲ 14.6%</span>
				</div>
				<div class="kpi-value" data-count="3204">0</div>
				<div class="kpi-bottom">
					<div class="kpi-label">Notifications sent</div>
					<svg class="kpi-spark" width="72" height="26" viewBox="0 0 72 26">
						<polyline points="0,18 12,19 24,14 36,16 48,9 60,11 72,3" fill="none" stroke="var(--alertx-magenta)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</div>
			</div>
			<div class="kpi-card">
				<div class="kpi-top">
					<div class="kpi-icon c3">
						<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
							<path d="M3 8l9 6 9-6" />
							<rect x="3" y="5" width="18" height="14" rx="2" />
						</svg>
					</div>
					<span class="kpi-delta up">▲ 3.1%</span>
				</div>
				<div class="kpi-value" data-count="42" data-suffix="%">0%</div>
				<div class="kpi-bottom">
					<div class="kpi-label">Email open rate</div>
					<svg class="kpi-spark" width="72" height="26" viewBox="0 0 72 26">
						<polyline points="0,10 12,13 24,8 36,11 48,7 60,9 72,5" fill="none" stroke="var(--alertx-red)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</div>
			</div>
			<div class="kpi-card">
				<div class="kpi-top">
					<div class="kpi-icon c4">
						<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
							<circle cx="12" cy="12" r="9" />
							<path d="M12 7v5l3 3" />
						</svg>
					</div>
					<span class="kpi-delta down">▼ 1.4%</span>
				</div>
				<div class="kpi-value" data-count="18" data-suffix="%">0%</div>
				<div class="kpi-bottom">
					<div class="kpi-label">Conversion after alert</div>
					<svg class="kpi-spark" width="72" height="26" viewBox="0 0 72 26">
						<polyline points="0,6 12,9 24,8 36,13 48,12 60,16 72,15" fill="none" stroke="var(--alertx-orange)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</div>
			</div>
		</section>
		<section class="section pulse-section">
			<div class="section-head">
				<div>
					<div class="section-eyebrow">Signature insight</div>
					<div class="section-title">Demand Pulse</div>
					<div class="section-desc">Live waitlist size per product — the more people waiting, the hotter the restock priority.</div>
				</div>
				<div class="pulse-live">
					<span class="blip"></span>Updating live
				</div>
			</div>
			<div class="pulse-list" id="pulseList">
				<div class="pulse-row" data-max="247">
					<div class="pulse-rank">01</div>
					<div class="pulse-info">
						<div class="pname">iPhone 15 Pro Silicone Case</div>
						<div class="pvariant">All variations combined</div>
					</div>
					<div class="pulse-track">
						<div class="pulse-fill" style="width:0%">
							<span class="pulse-dot"></span>
						</div>
					</div>
					<div class="pulse-count">
						<div class="num" data-target="247">0</div>
						<div class="lbl">waiting</div>
					</div>
					<div class="heat-tag hot">🔥 Hot</div>
				</div>
				<div class="pulse-row" data-max="89">
					<div class="pulse-rank">02</div>
					<div class="pulse-info">
						<div class="pname">Vintage Denim Jacket</div>
						<div class="pvariant">Black / XL</div>
					</div>
					<div class="pulse-track">
						<div class="pulse-fill" style="width:0%">
							<span class="pulse-dot"></span>
						</div>
					</div>
					<div class="pulse-count">
						<div class="num" data-target="89">0</div>
						<div class="lbl">waiting</div>
					</div>
					<div class="heat-tag rising">↑ Rising</div>
				</div>
				<div class="pulse-row" data-max="42">
					<div class="pulse-rank">03</div>
					<div class="pulse-info">
						<div class="pname">Vintage Denim Jacket</div>
						<div class="pvariant">Red / L</div>
					</div>
					<div class="pulse-track">
						<div class="pulse-fill" style="width:0%">
							<span class="pulse-dot"></span>
						</div>
					</div>
					<div class="pulse-count">
						<div class="num" data-target="42">0</div>
						<div class="lbl">waiting</div>
					</div>
					<div class="heat-tag rising">↑ Rising</div>
				</div>
				<div class="pulse-row" data-max="26">
					<div class="pulse-rank">04</div>
					<div class="pulse-info">
						<div class="pname">Wireless Earbuds Pro</div>
						<div class="pvariant">Matte Black</div>
					</div>
					<div class="pulse-track">
						<div class="pulse-fill" style="width:0%">
							<span class="pulse-dot"></span>
						</div>
					</div>
					<div class="pulse-count">
						<div class="num" data-target="26">0</div>
						<div class="lbl">waiting</div>
					</div>
					<div class="heat-tag steady">Steady</div>
				</div>
			</div>
		</section>
		<div class="grid-2">
			<section class="section">
				<div class="section-head">
					<div>
						<div class="section-eyebrow">Trend</div>
						<div class="section-title">Notifications sent — last 14 days</div>
					</div>
				</div>
				<div class="trend-legend">
					<div class="trend-legend-item">
						<span class="trend-legend-dot" style="background:var(--alertx-violet)"></span>Sent
					</div>
					<div class="trend-legend-item">
						<span class="trend-legend-dot" style="background:var(--alertx-red)"></span>Failed
					</div>
				</div>
				<div class="trend-wrap">
					<svg viewBox="0 0 560 180" width="100%" height="180" preserveAspectRatio="none">
						<defs>
							<linearGradient id="areaGrad" x1="0" y1="0" x2="0" y2="1">
								<stop offset="0%" stop-color="#5212E8" stop-opacity="0.28" />
								<stop offset="100%" stop-color="#5212E8" stop-opacity="0" />
							</linearGradient>
							<linearGradient id="lineGrad" x1="0" y1="0" x2="1" y2="0">
								<stop offset="0%" stop-color="#3C06C5" />
								<stop offset="100%" stop-color="#D90DD9" />
							</linearGradient>
						</defs>
						<g stroke="#EFEBFA" stroke-width="1">
							<line x1="0" y1="20" x2="560" y2="20" />
							<line x1="0" y1="65" x2="560" y2="65" />
							<line x1="0" y1="110" x2="560" y2="110" />
							<line x1="0" y1="155" x2="560" y2="155" />
						</g>
						<path d="M0,140 L40,120 L80,128 L120,95 L160,105 L200,80 L240,88 L280,60 L320,70 L360,45 L400,55 L440,35 L480,42 L520,20 L560,25 L560,180 L0,180 Z" fill="url(#areaGrad)" />
						<polyline points="0,140 40,120 80,128 120,95 160,105 200,80 240,88 280,60 320,70 360,45 400,55 440,35 480,42 520,20 560,25" fill="none" stroke="url(#lineGrad)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
						<polyline points="0,168 40,166 80,170 120,162 160,169 200,158 240,171 280,150 320,165 360,145 400,168 440,140 480,162 520,138 560,155" fill="none" stroke="#FC301D" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" opacity="0.7" />
					</svg>
				</div>
			</section>
			<section class="section">
				<div class="section-head">
					<div>
						<div class="section-eyebrow">Live feed</div>
						<div class="section-title">Recent activity</div>
					</div>
					<a class="section-action" href="#" data-goto="subscribers">View all <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
							<path d="M9 6l6 6-6 6" />
						</svg>
					</a>
				</div>
				<div class="activity-list">
					<div class="activity-item">
						<div class="activity-icon subscribed">
							<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2">
								<path d="M12 5v14M5 12h14" />
							</svg>
						</div>
						<div>
							<div class="activity-text">
								<b>t***a@gmail.com</b> joined the waitlist for iPhone 15 Pro Case
							</div>
							<div class="activity-time">2 hours ago</div>
						</div>
					</div>
					<div class="activity-item">
						<div class="activity-icon notified">
							<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2">
								<path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
							</svg>
						</div>
						<div>
							<div class="activity-text">Back-in-stock email sent for <b>Denim Jacket · Black / XL</b>
							</div>
							<div class="activity-time">5 hours ago</div>
						</div>
					</div>
					<div class="activity-item">
						<div class="activity-icon converted">
							<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5">
								<path d="M20 6 9 17l-5-5" />
							</svg>
						</div>
						<div>
							<div class="activity-text">
								<b>m***h@outlook.com</b> purchased Wireless Earbuds Pro after alert
							</div>
							<div class="activity-time">Yesterday</div>
						</div>
					</div>
					<div class="activity-item">
						<div class="activity-icon failed">
							<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2">
								<path d="M12 9v4M12 17h.01" />
								<circle cx="12" cy="12" r="9" />
							</svg>
						</div>
						<div>
							<div class="activity-text">Delivery failed for 3 subscribers — retrying automatically</div>
							<div class="activity-time">Yesterday</div>
						</div>
					</div>
					<div class="activity-item">
						<div class="activity-icon subscribed">
							<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2">
								<path d="M12 5v14M5 12h14" />
							</svg>
						</div>
						<div>
							<div class="activity-text">
								<b>f***a@gmail.com</b> joined the waitlist for Ceramic Pour-Over Set
							</div>
							<div class="activity-time">2 days ago</div>
						</div>
					</div>
				</div>
			</section>
		</div>
	</section>
	<!--PAGE:DASHBOARD-->
</div>

























<div class="wrap">
	<div class="alertx-admin">
		<div id="alertx">
			<div class="alertx-main">
				<div class="alertx-admin-wrapper">
					<div>





						<!-- Analytics counter wrapper -->
						<div class="alertx-analytics-counter-wrapper">
							<div>
								<div class="alertx-analytics-counter">
									<div class="counter-wrap">
										<span class="alertx-counter-icon">
											<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#fe3627" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
											<path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5v-9Z"/>
											<path d="m3 7.5 9 4.5 9-4.5M12 12v9"/>
											</svg>
										</span>
										<div>
											<span class="alertx-counter-number alertx-stat-value">
												<?php echo esc_html( $admin_obj->get_total_products() ); ?>
											</span>
											<span class="alertx-counter-label">
												<?php esc_html_e( 'Total Products', 'multi-order-tracker' ); ?>
											</span>
										</div>
									</div>
								</div>
							</div>

							<div>
								<div class="alertx-analytics-counter">
									<div class="counter-wrap">
										<span class="alertx-counter-icon">
											<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#fe3627" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16v13H4z"/><path d="M8 7V4h8v3"/><path d="m9 11 6 6M15 11l-6 6"/></svg>
										</span>
										<div>
											<span class="alertx-counter-number alertx-stat-value">
												<?php echo esc_html( $admin_obj->get_out_of_stock_products() ); ?>
											</span>
											<span class="alertx-counter-label">
												<?php esc_html_e( 'Out of Stock', 'multi-order-tracker' ); ?>
											</span>
										</div>
									</div>
								</div>
							</div>

							<div>
								<div class="alertx-analytics-counter">
									<div class="counter-wrap">
										<span class="alertx-counter-icon">
											<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#fe3627" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
												<circle cx="9" cy="8" r="3"/>
												<path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/>
												<path d="M16 11a3 3 0 1 0 0-6"/>
												<path d="M17 14.2c2.3.6 4 2.7 4 5.2"/>
											</svg>
										</span>
										<div>
											<span class="alertx-counter-number alertx-stat-value">
												<?php echo esc_html( $admin_obj->get_unique_emails() ); ?>
											</span>
											<span class="alertx-counter-label">
												<?php esc_html_e( 'Subscribers', 'multi-order-tracker' ); ?>
											</span>
										</div>
									</div>
								</div>
							</div>

							<div>
								<div class="alertx-analytics-counter">
									<div class="counter-wrap">
										<span class="alertx-counter-icon">
											<svg xmlns="http://www.w3.org/2000/svg" width="65" height="65" viewBox="0 0 24 24" fill="none" stroke="#fe3627" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
										</span>
										<div>
											<span class="alertx-counter-number alertx-stat-value">
												<?php echo esc_html( $admin_obj->get_total_notifications() ); ?>
											</span>
											<span class="alertx-counter-label">
												<?php esc_html_e( 'Notifications', 'multi-order-tracker' ); ?>
											</span>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- Settings -->
						<div class="alertx-settings">
							<div class="alertx-settings-content">
								<div class="alertx-settings-form-wrapper">
									<div class="alertx-form alertx-tabs-wrapper">

										<div class="alertx-tab-content-wrapper">
											<div class="alertx-tab-flex">
												<div class="alertx-tab-contents">
													<div id="tab-templates" class="alertx-tab-content alertx-tab-tab-templates alertx-active">
														<div id="section-modules" class="alertx-control-section section-modules">

														</div>
													</div>
													<div id="tab-advanced-settings" class="alertx-tab-content alertx-tab-tab-advanced-settings">
														<div id="heading_management" class="alertx-control-section heading_management">
															<div class="alertx-section-title">
																<h4><?php esc_html_e( 'Heading', 'multi-order-tracker' ); ?></h4>
															</div>
															<div class="alertx-section-fields">
																<div class="alertx-control-wrapper alertx-type-text alertx-inline-label alertx-name-heading">
																	<div class="alertx-control-label">
																		<label for="heading">
																			<?php esc_html_e( 'Heading', 'multi-order-tracker' ); ?>
																		</label>
																	</div>
																	<div class="alertx-control-field">
																		<input name="multi_order_track[heading]" type="text" placeholder="<?php esc_html_e( 'Heading', 'multi-order-tracker' ); ?>" id="heading" value="<?php echo esc_attr( $settings['heading'] ); ?>">
																	</div>
																</div>
																<div class="alertx-control-wrapper alertx-type-text alertx-inline-label alertx-name-sub_heading">
																	<div class="alertx-control-label">
																		<label for="sub_heading">
																			<?php esc_html_e( 'Sub Heading', 'multi-order-tracker' ); ?>
																		</label>
																	</div>
																	<div class="alertx-control-field">
																		<input name="multi_order_track[sub_heading]" type="text" placeholder="<?php esc_html_e( 'Sub Heading', 'multi-order-tracker' ); ?>" id="sub_heading" value="<?php echo esc_attr( $settings['sub_heading'] ); ?>">
																	</div>
																</div>
															</div>
														</div>
														<div id="font_size" class="alertx-control-section font_size">
															<div class="alertx-section-title">
																<h4><?php esc_html_e( 'Font Size', 'multi-order-tracker' ); ?></h4>
															</div>
															<div class="alertx-section-fields">
																<div class="alertx-control-wrapper alertx-type-text alertx-inline-label alertx-name-heading_font_size">
																	<div class="alertx-control-label">
																		<label for="heading_font_size">
																			<?php esc_html_e( 'Heading Font Size', 'multi-order-tracker' ); ?>
																		</label>
																	</div>
																	<div class="alertx-control-field">
																		<input name="multi_order_track[heading_font_size]" type="number" min=1 max=72 step=1 placeholder="<?php esc_html_e( 'Heading Font Size', 'multi-order-tracker' ); ?>" id="heading_font_size" value="<?php echo esc_attr( $settings['heading_font_size'] ); ?>">
																	</div>
																</div>
																<div class="alertx-control-wrapper alertx-type-text alertx-inline-label alertx-name-sub_heading_font_size">
																	<div class="alertx-control-label">
																		<label for="sub_heading_font_size">
																			<?php esc_html_e( 'Sub Heading Font Size (px)', 'multi-order-tracker' ); ?>
																		</label>
																	</div>
																	<div class="alertx-control-field">
																		<input name="multi_order_track[sub_heading_font_size]" type="number" min=1 max=72 step=1 placeholder="<?php esc_html_e( 'Sub Heading Font Size', 'multi-order-tracker' ); ?>" id="sub_heading_font_size" value="<?php echo esc_attr( $settings['sub_heading_font_size'] ); ?>">
																	</div>
																</div>
															</div>
														</div>
														<div id="color_management" class="alertx-control-section color_management">
															<div class="alertx-section-title">
																<h4><?php esc_html_e( 'Color', 'multi-order-tracker' ); ?></h4>
															</div>
															<div class="alertx-section-fields">
																<div class="alertx-control-wrapper alertx-type-text alertx-inline-label alertx-name-primary_color">
																	<div class="alertx-control-label">
																		<label for="primary_color">
																			<?php esc_html_e( 'PopUp Primary Color', 'multi-order-tracker' ); ?>
																		</label>
																	</div>
																	<div class="alertx-control-field">
																		<input name="multi_order_track[primary_color]" type="text" id="primary_color" class="color-field" value="<?php echo esc_attr( $settings['primary_color'] ); ?>">
																	</div>
																</div>
																<div class="alertx-control-wrapper alertx-type-text alertx-inline-label alertx-name-secondary_color">
																	<div class="alertx-control-label">
																		<label for="secondary_color">
																			<?php esc_html_e( 'PopUp Secondary Color', 'multi-order-tracker' ); ?>
																		</label>
																	</div>
																	<div class="alertx-control-field">
																		<input name="multi_order_track[secondary_color]" type="text" id="secondary_color" class="color-field" value="<?php echo esc_attr( $settings['secondary_color'] ); ?>">
																	</div>
																</div>
																<div class="alertx-control-wrapper alertx-type-text alertx-inline-label alertx-name-heading_color">
																	<div class="alertx-control-label">
																		<label for="heading_color">
																			<?php esc_html_e( 'Header Text Color', 'multi-order-tracker' ); ?>
																		</label>
																	</div>
																	<div class="alertx-control-field">
																		<input name="multi_order_track[heading_color]" type="text" id="heading_color" class="color-field" value="<?php echo esc_attr( $settings['heading_color']); ?>">
																	</div>
																</div>
																<div class="alertx-control-wrapper alertx-type-text alertx-inline-label alertx-name-sub_heading_color">
																	<div class="alertx-control-label">
																		<label for="sub_heading_color">
																			<?php esc_html_e( 'Sub Heading Text Color', 'multi-order-tracker' ); ?>
																		</label>
																	</div>
																	<div class="alertx-control-field">
																		<input name="multi_order_track[sub_heading_color]" type="text" id="sub_heading_color" class="color-field" value="<?php echo esc_attr( $settings['sub_heading_color'] ); ?>">
																	</div>
																</div>

																<div class="alertx-control-wrapper alertx-type-text alertx-inline-label alertx-name-sub_heading_color">
																	<div class="alertx-control-label">
																		<label for="footer_background_color">
																			<?php esc_html_e( 'Footer Background Color', 'multi-order-tracker' ); ?>
																		</label>
																	</div>
																	<div class="alertx-control-field">
																		<input name="multi_order_track[footer_background_color]" type="text" id="footer_background_color" class="color-field" value="<?php echo esc_attr( $settings['footer_background_color'] ); ?>">
																	</div>
																</div>

																<div class="alertx-control-wrapper alertx-type-text alertx-inline-label alertx-name-sub_heading_color">
																	<div class="alertx-control-label">
																		<label for="footer_text_color">
																			<?php esc_html_e( 'Footer Text Color', 'multi-order-tracker' ); ?>
																		</label>
																	</div>
																	<div class="alertx-control-field">
																		<input name="multi_order_track[footer_text_color]" type="text" id="footer_text_color" class="color-field" value="<?php echo esc_attr( $settings['footer_text_color'] ); ?>">
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
												<div class="alertx-settings-right">
													<div class="alertx-sidebar">
														<div class="alertx-sidebar-block">
															<div class="alertx-admin-sidebar-logo"></div>
															<div class="alertx-admin-sidebar-cta">
																<a href="https://thebitcraft.com/support/" rel="nofollow" target="_blank">
																	<span class="dashicons dashicons-email"></span>
																	<?php esc_html_e( 'Support', 'multi-order-tracker' ); ?>
																</a>
															</div>
														</div>
														<div class="alertx-sidebar-block alertx-license-block"></div>
													</div>
												</div>
											</div>
											<div class="alertx-submit alertx-control">
												<button type="button" class="components-button alertx-submit-button" id="motfw-save-settings-btn">
													<span class="dashicons dashicons-database-add"></span>
													<?php esc_html_e( 'Save Settings', 'multi-order-tracker' ); ?>
												</button>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="alertx-settings-documentation">
								<div class="alertx-settings-row">
									<div class="alertx-more-docs-wrapper">
										<div class="alertx-docs-content-wrapper alertx-content-details">
											<div class="img-wrap">
												<?php /* phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage */ ?>
												<img
													src="<?php echo esc_url( ALERTX_ASSETS . "/images/love.svg" ); ?>"
													alt="<?php esc_html_e( 'Leave a Review', 'multi-order-tracker' ); ?>">
											</div>

											<h3><?php esc_html_e( 'Express Your Love', 'multi-order-tracker' ); ?></h3>
											<p><?php esc_html_e( 'Welcome to Multi-Order Tracker! Boost your e-commerce efficiency with our evolving plugin. Please take a moment to review and share your experience. Your feedback drives improvement and helps fellow sellers maximize conversions. Thanks for choosing us!', 'multi-order-tracker' ); ?></p>
											<a
												class="alertx-resource-link"
												target="_blank"
												href="<?php echo esc_url('https://thebitcraft.com/support/'); ?>"
												target="_blank"><?php esc_html_e( 'Leave a Review', 'multi-order-tracker' ); ?>
												<?php /* phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage */ ?>
												<img
													src="<?php echo esc_url( ALERTX_ASSETS . "/images/link.svg" ); ?>"
													alt="<?php esc_html_e( 'Leave a Review', 'multi-order-tracker' ); ?>">
											</a>
										</div>
										<div class="alertx-docs-content-wrapper alertx-content-details">
											<div class="img-wrap">
												<?php /* phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage */ ?>
												<img src="<?php echo esc_url( ALERTX_ASSETS . "/images/knowledgebase.svg" ); ?>" alt="<?php esc_html_e( 'Discover Our Knowledge Base', 'multi-order-tracker' ); ?>">
											</div>
											<h3><?php esc_html_e( 'Discover Our Knowledge Base', 'multi-order-tracker' ); ?></h3>
											<p><?php esc_html_e( 'Get started by spending some time with the documentation to familiarize yourself with Multi-Order Tracker and boost your website conversions immediately.', 'multi-order-tracker' ); ?></p>
											<a class="alertx-resource-link" target="_blank" href="<?php echo esc_url('https://thebitcraft.com/docs/multi-order-tracker/'); ?>" target="_blank"><?php esc_html_e( 'Documentation', 'multi-order-tracker' ); ?>
												<?php /* phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage */ ?>
												<img src="<?php echo esc_url( ALERTX_ASSETS . "/images/link.svg" ); ?>" alt="<?php esc_html_e( 'Discover Our Knowledge Base', 'multi-order-tracker' ); ?>">
											</a>
										</div>
										<div class="alertx-docs-content-wrapper alertx-content-details">
											<div class="img-wrap">
												<?php /* phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage */ ?>
												<img src="<?php echo esc_url( ALERTX_ASSETS . "/images/support.svg" ); ?>" alt="<?php esc_html_e( 'Need Help?', 'multi-order-tracker' ); ?>">
											</div>
											<h3><?php esc_html_e( 'Need Help?', 'multi-order-tracker' ); ?></h3>
											<p><?php esc_html_e( 'If you experience any issues or need help, please reach out to us or report problems on our support page.', 'multi-order-tracker' ); ?></p>
											<a class="alertx-resource-link" target="_blank" href="<?php echo esc_url('https://thebitcraft.com/support/'); ?>" target="_blank"><?php esc_html_e( 'Report a Bug', 'multi-order-tracker' ); ?>
												<?php /* phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage */ ?>
												<img src="<?php echo esc_url( ALERTX_ASSETS . "/images/link.svg" ); ?>" alt="<?php esc_html_e( 'Need Help?', 'multi-order-tracker' ); ?>">
											</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
