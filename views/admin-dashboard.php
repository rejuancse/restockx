<?php
/**
 * Admin settings template
 *
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="wrap">
	<div class="multiorder-admin">
		<div id="multiorder">
			<div class="multiorder-main">
				<div class="mot-admin-wrapper">
					<div>
						<div class="mot-settings-header">
							<div class="mot-header-left">
								<div class="mot-admin-header">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 120">
										<!-- Logo -->
										<g transform="translate(10, 10) scale(0.05)">
											<path fill="#FF6106" d="M910.001 203.918C910.001 163.587 921.96 124.161 944.366 90.6273 966.773 57.0931 998.621 30.9565 1035.88 15.5225 1073.14.0883516 1114.15-3.9499 1153.7 3.91831 1193.26 11.7866 1229.59 31.2078 1258.11 59.7263 1286.63 88.2447 1306.05 124.579 1313.92 164.135 1321.78 203.692 1317.75 244.693 1302.32 281.954 1286.88 319.215 1260.74 351.062 1227.21 373.47 1193.68 395.876 1154.25 407.836 1113.92 407.836 1087.13 407.869 1060.6 402.617 1035.84 392.38 1011.09 382.144 988.596 367.124 969.654 348.182 950.712 329.241 935.692 306.748 925.457 281.993 915.22 257.237 909.967 230.706 910.001 203.918ZM167.424 913.308 458.311 622.422H1045.04L460.789 1206.68C421.886 1245.58 369.124 1267.44 314.107 1267.44 259.09 1267.44 206.327 1245.58 167.424 1206.68 128.522 1167.77 106.667 1115.01 106.667 1059.99 106.667 1004.97 128.522 952.211 167.424 913.308ZM1738.81 1027.77 1454.11 1313.21H867.136L1444.7 734.406C1483.6 695.405 1536.4 673.455 1591.49 673.386 1646.57 673.316 1699.43 695.132 1738.43 734.034 1777.43 772.936 1799.38 825.739 1799.45 880.826 1799.52 935.911 1777.71 988.769 1738.81 1027.77ZM587.075 1602.79C564.668 1636.33 552.709 1675.75 552.709 1716.08 552.709 1770.16 574.192 1822.02 612.435 1860.27 650.677 1898.51 702.543 1920 756.627 1920 796.957 1920 836.382 1908.04 869.917 1885.63 903.451 1863.23 929.588 1831.38 945.021 1794.12 960.456 1756.85 964.494 1715.85 956.625 1676.29 948.758 1636.74 929.336 1600.4 900.817 1571.89 872.299 1543.37 835.965 1523.94 796.409 1516.08 756.853 1508.21 715.851 1512.25 678.59 1527.69 641.329 1543.12 609.481 1569.25 587.075 1602.79Z"/>
										</g>

										<!-- Text -->
										<text x="120" y="70" font-size="36" class="logo-text" fill="#6a5c55">
											<?php esc_html_e('Multi-Order Tracker', 'multi-order-tracker'); ?>
										</text>

										<!-- Decorative elements -->
										<path d="M120,95 Q290,80 473,95" stroke="#FF6106" stroke-width="2" fill="none"/>
										<circle cx="296" cy="88" r="6" fill="#FF6106"/>
									</svg>
								</div>
							</div>
							<div class="mot-header-right">
								<span>
									<?php
										// translators: %s: plugin version number
										$version_text = esc_html__( 'Current Version: %s', 'multi-order-tracker' );
										echo wp_kses_post( sprintf( $version_text, '<strong>' . esc_html( MULTI_ORDER_TRACKER_VERSION ) . '</strong>' ) );
									?>
								</span>
							</div>
						</div>

						<!-- Analytics counter wrapper -->
						<div class="mot-analytics-counter-wrapper">
							<div>
								<div class="mot-analytics-counter">
									<div class="counter-wrap">
										<span class="mot-counter-icon">
											<svg xmlns="http://www.w3.org/2000/svg" width="65" height="65" viewBox="0 0 24 24" fill="none" stroke="#FF6106" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-bag"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path><path d="M3 6h18"></path><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
										</span>
										<div>
											<span class="mot-counter-number">0</span>
											<span class="mot-counter-label">
												<?php esc_html_e( 'Total Orders', 'multi-order-tracker' ); ?>
											</span>
										</div>
									</div>
								</div>
							</div>
							<div>
								<div class="mot-analytics-counter">
									<div class="counter-wrap">
										<span class="mot-counter-icon">
											<svg xmlns="http://www.w3.org/2000/svg" width="65" height="65" viewBox="0 0 24 24" fill="none" stroke="#FF6106" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
										</span>
										<div>
											<span class="mot-counter-number">0</span>
											<span class="mot-counter-label">
												<?php esc_html_e( 'Total Customers', 'multi-order-tracker' ); ?>
											</span>
										</div>
									</div>
								</div>
							</div>
							<div>
								<div class="mot-analytics-counter">
									<div class="counter-wrap">
										<span class="mot-counter-icon">
											<svg xmlns="http://www.w3.org/2000/svg" width="65" height="65" viewBox="0 0 24 24" fill="none" stroke="#FF6106" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-dollar-sign"><line x1="12" x2="12" y1="2" y2="22"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
										</span>
										<div>
											<span class="mot-counter-number">0.00</span>
											<span class="mot-counter-label">
												<?php esc_html_e( 'Total Revenue', 'multi-order-tracker' ); ?>
											</span>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- Settings -->
						<div class="mot-settings">
							<div class="mot-settings-content">
								<div class="mot-settings-form-wrapper">
									<div class="multi-order-form mot-tabs-wrapper">
										<div class="mot-tab-menu-wrapper">
											<ul class="mot-tab-nav">
												<li class="mot-tab-nav-item tab-templates" data-key="tab-templates">
													<span>
														<span class="dashicons dashicons-art"></span>
														<?php esc_html_e( 'Templates', 'multi-order-tracker' ); ?>
													</span>
												</li>
												<li class="mot-tab-nav-item tab-advanced-settings" data-key="tab-advanced-settings">
													<span>
														<span class="dashicons dashicons-admin-settings"></span>
														<?php esc_html_e( 'Advanced Settings', 'multi-order-tracker' ); ?>
													</span>
												</li>
											</ul>
										</div>
										<div class="mot-tab-content-wrapper">
											<div class="mot-tab-flex">
												<div class="mot-tab-contents">
													<div id="tab-templates" class="mot-tab-content mot-tab-tab-templates mot-active">
														<div id="section-modules" class="mot-control-section section-modules">
															<div class="mot-section-title">
																<h4><?php esc_html_e( 'Templates', 'multi-order-tracker' ); ?></h4>
															</div>
															<div class="mot-section-fields">
																<div class="mot-toggle-wrapper mot-control">
																	<div class="template-style-container">
																		<div class="template-options">
																			<!-- Template One -->
																			<div class="template-option">
																				<input
																					type="radio"
																					name="multi_order_track[template]"
																					id="template_one" value="one"
																					<?php checked( $settings['template'], 'one' ); ?>
																				>
																				<label for="template_one" class="template-label">
																					<div class="template-preview">
																						<div class="preview-item">
																							<div class="preview-icon"></div>
																							<div class="preview-lines">
																								<div class="preview-line"></div>
																								<div class="preview-line"></div>
																							</div>
																						</div>
																						<div class="preview-item">
																							<div class="preview-icon"></div>
																							<div class="preview-lines">
																								<div class="preview-line"></div>
																								<div class="preview-line"></div>
																							</div>
																						</div>
																					</div>
																					<span class="template-name">
																						<?php esc_html_e( 'Template One', 'multi-order-tracker' ); ?>
																					</span>
																					<span class="checkmark"></span>
																				</label>
																			</div>

																			<!-- Template Two -->
																			<div class="template-option">
																				<input
																					type="radio"
																					name="multi_order_track[template]"
																					id="template_two" value="two"
																					<?php checked( $settings['template'], 'two' ); ?>
																				>
																				<label for="template_two" class="template-label">
																					<div class="template-preview">
																						<div class="preview-item">
																							<div class="preview-icon"></div>
																							<div class="preview-lines">
																								<div class="preview-line"></div>
																								<div class="preview-line"></div>
																							</div>
																						</div>
																						<div class="preview-item">
																							<div class="preview-icon"></div>
																							<div class="preview-lines">
																								<div class="preview-line"></div>
																								<div class="preview-line"></div>
																							</div>
																						</div>
																					</div>
																					<span class="template-name">
																						<?php esc_html_e( 'Template Two', 'multi-order-tracker' ); ?>
																					</span>
																					<span class="checkmark"></span>
																				</label>
																			</div>

																			<!-- Template Three -->
																			<div class="template-option">
																				<input
																					type="radio"
																					name="multi_order_track[template]"
																					id="template_three" value="three"
																					<?php checked( $settings['template'], 'three' ); ?>
																				>
																				<label for="template_three" class="template-label">
																					<div class="template-preview">
																						<div class="preview-item">
																							<div class="preview-icon"></div>
																							<div class="preview-lines">
																								<div class="preview-line"></div>
																								<div class="preview-line"></div>
																							</div>
																						</div>
																						<div class="preview-item">
																							<div class="preview-icon"></div>
																							<div class="preview-lines">
																								<div class="preview-line"></div>
																								<div class="preview-line"></div>
																							</div>
																						</div>
																					</div>
																					<span class="template-name">
																						<?php esc_html_e( 'Template Three', 'multi-order-tracker' ); ?>
																					</span>
																					<span class="checkmark"></span>
																				</label>
																			</div>

																			<!-- Temlate Four -->
																			<div class="template-option">
																				<input
																					type="radio"
																					name="multi_order_track[template]"
																					id="template_four" value="four"
																					<?php checked( $settings['template'], 'four' ); ?>
																				>
																				<label for="template_four" class="template-label">
																					<div class="template-preview">
																						<div class="preview-item">
																							<div class="preview-icon"></div>
																							<div class="preview-lines">
																								<div class="preview-line"></div>
																								<div class="preview-line"></div>
																							</div>
																						</div>
																						<div class="preview-item">
																							<div class="preview-icon"></div>
																							<div class="preview-lines">
																								<div class="preview-line"></div>
																								<div class="preview-line"></div>
																							</div>
																						</div>
																					</div>
																					<span class="template-name">
																						<?php esc_html_e( 'Template Four', 'multi-order-tracker' ); ?>
																					</span>
																					<span class="checkmark"></span>
																				</label>
																			</div>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div id="tab-advanced-settings" class="mot-tab-content mot-tab-tab-advanced-settings">
														<div id="heading_management" class="mot-control-section heading_management">
															<div class="mot-section-title">
																<h4><?php esc_html_e( 'Heading', 'multi-order-tracker' ); ?></h4>
															</div>
															<div class="mot-section-fields">
																<div class="mot-control-wrapper mot-type-text mot-inline-label mot-name-heading">
																	<div class="mot-control-label">
																		<label for="heading">
																			<?php esc_html_e( 'Heading', 'multi-order-tracker' ); ?>
																		</label>
																	</div>
																	<div class="mot-control-field">
																		<input name="multi_order_track[heading]" type="text" placeholder="<?php esc_html_e( 'Heading', 'multi-order-tracker' ); ?>" id="heading" value="<?php echo esc_attr( $settings['heading'] ); ?>">
																	</div>
																</div>
																<div class="mot-control-wrapper mot-type-text mot-inline-label mot-name-sub_heading">
																	<div class="mot-control-label">
																		<label for="sub_heading">
																			<?php esc_html_e( 'Sub Heading', 'multi-order-tracker' ); ?>
																		</label>
																	</div>
																	<div class="mot-control-field">
																		<input name="multi_order_track[sub_heading]" type="text" placeholder="<?php esc_html_e( 'Sub Heading', 'multi-order-tracker' ); ?>" id="sub_heading" value="<?php echo esc_attr( $settings['sub_heading'] ); ?>">
																	</div>
																</div>
															</div>
														</div>
														<div id="font_size" class="mot-control-section font_size">
															<div class="mot-section-title">
																<h4><?php esc_html_e( 'Font Size', 'multi-order-tracker' ); ?></h4>
															</div>
															<div class="mot-section-fields">
																<div class="mot-control-wrapper mot-type-text mot-inline-label mot-name-heading_font_size">
																	<div class="mot-control-label">
																		<label for="heading_font_size">
																			<?php esc_html_e( 'Heading Font Size', 'multi-order-tracker' ); ?>
																		</label>
																	</div>
																	<div class="mot-control-field">
																		<input name="multi_order_track[heading_font_size]" type="number" min=1 max=72 step=1 placeholder="<?php esc_html_e( 'Heading Font Size', 'multi-order-tracker' ); ?>" id="heading_font_size" value="<?php echo esc_attr( $settings['heading_font_size'] ); ?>">
																	</div>
																</div>
																<div class="mot-control-wrapper mot-type-text mot-inline-label mot-name-sub_heading_font_size">
																	<div class="mot-control-label">
																		<label for="sub_heading_font_size">
																			<?php esc_html_e( 'Sub Heading Font Size (px)', 'multi-order-tracker' ); ?>
																		</label>
																	</div>
																	<div class="mot-control-field">
																		<input name="multi_order_track[sub_heading_font_size]" type="number" min=1 max=72 step=1 placeholder="<?php esc_html_e( 'Sub Heading Font Size', 'multi-order-tracker' ); ?>" id="sub_heading_font_size" value="<?php echo esc_attr( $settings['sub_heading_font_size'] ); ?>">
																	</div>
																</div>
															</div>
														</div>
														<div id="color_management" class="mot-control-section color_management">
															<div class="mot-section-title">
																<h4><?php esc_html_e( 'Color', 'multi-order-tracker' ); ?></h4>
															</div>
															<div class="mot-section-fields">
																<div class="mot-control-wrapper mot-type-text mot-inline-label mot-name-primary_color">
																	<div class="mot-control-label">
																		<label for="primary_color">
																			<?php esc_html_e( 'PopUp Primary Color', 'multi-order-tracker' ); ?>
																		</label>
																	</div>
																	<div class="mot-control-field">
																		<input name="multi_order_track[primary_color]" type="text" id="primary_color" class="color-field" value="<?php echo esc_attr( $settings['primary_color'] ); ?>">
																	</div>
																</div>
																<div class="mot-control-wrapper mot-type-text mot-inline-label mot-name-secondary_color">
																	<div class="mot-control-label">
																		<label for="secondary_color">
																			<?php esc_html_e( 'PopUp Secondary Color', 'multi-order-tracker' ); ?>
																		</label>
																	</div>
																	<div class="mot-control-field">
																		<input name="multi_order_track[secondary_color]" type="text" id="secondary_color" class="color-field" value="<?php echo esc_attr( $settings['secondary_color'] ); ?>">
																	</div>
																</div>
																<div class="mot-control-wrapper mot-type-text mot-inline-label mot-name-heading_color">
																	<div class="mot-control-label">
																		<label for="heading_color">
																			<?php esc_html_e( 'Header Text Color', 'multi-order-tracker' ); ?>
																		</label>
																	</div>
																	<div class="mot-control-field">
																		<input name="multi_order_track[heading_color]" type="text" id="heading_color" class="color-field" value="<?php echo esc_attr( $settings['heading_color']); ?>">
																	</div>
																</div>
																<div class="mot-control-wrapper mot-type-text mot-inline-label mot-name-sub_heading_color">
																	<div class="mot-control-label">
																		<label for="sub_heading_color">
																			<?php esc_html_e( 'Sub Heading Text Color', 'multi-order-tracker' ); ?>
																		</label>
																	</div>
																	<div class="mot-control-field">
																		<input name="multi_order_track[sub_heading_color]" type="text" id="sub_heading_color" class="color-field" value="<?php echo esc_attr( $settings['sub_heading_color'] ); ?>">
																	</div>
																</div>

																<div class="mot-control-wrapper mot-type-text mot-inline-label mot-name-sub_heading_color">
																	<div class="mot-control-label">
																		<label for="footer_background_color">
																			<?php esc_html_e( 'Footer Background Color', 'multi-order-tracker' ); ?>
																		</label>
																	</div>
																	<div class="mot-control-field">
																		<input name="multi_order_track[footer_background_color]" type="text" id="footer_background_color" class="color-field" value="<?php echo esc_attr( $settings['footer_background_color'] ); ?>">
																	</div>
																</div>

																<div class="mot-control-wrapper mot-type-text mot-inline-label mot-name-sub_heading_color">
																	<div class="mot-control-label">
																		<label for="footer_text_color">
																			<?php esc_html_e( 'Footer Text Color', 'multi-order-tracker' ); ?>
																		</label>
																	</div>
																	<div class="mot-control-field">
																		<input name="multi_order_track[footer_text_color]" type="text" id="footer_text_color" class="color-field" value="<?php echo esc_attr( $settings['footer_text_color'] ); ?>">
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
												<div class="mot-settings-right">
													<div class="mot-sidebar">
														<div class="mot-sidebar-block">
															<div class="mot-admin-sidebar-logo"></div>
															<div class="mot-admin-sidebar-cta">
																<a href="https://thebitcraft.com/support/" rel="nofollow" target="_blank">
																	<span class="dashicons dashicons-email"></span>
																	<?php esc_html_e( 'Support', 'multi-order-tracker' ); ?>
																</a>
															</div>
														</div>
														<div class="mot-sidebar-block mot-license-block"></div>
													</div>
												</div>
											</div>
											<div class="mot-submit mot-control">
												<button type="button" class="components-button mot-submit-button" id="motfw-save-settings-btn">
													<span class="dashicons dashicons-database-add"></span>
													<?php esc_html_e( 'Save Settings', 'multi-order-tracker' ); ?>
												</button>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="mot-settings-documentation">
								<div class="mot-settings-row">
									<div class="mot-more-docs-wrapper">
										<div class="mot-docs-content-wrapper mot-content-details">
											<div class="img-wrap">
												<?php /* phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage */ ?>
												<img
													src="<?php echo esc_url( MULTI_ORDER_TRACKER_ASSETS . "/images/love.svg" ); ?>"
													alt="<?php esc_html_e( 'Leave a Review', 'multi-order-tracker' ); ?>">
											</div>
											<h3><?php esc_html_e( 'Express Your Love', 'multi-order-tracker' ); ?></h3>
											<p><?php esc_html_e( 'Welcome to Multi-Order Tracker! Boost your e-commerce efficiency with our evolving plugin. Please take a moment to review and share your experience. Your feedback drives improvement and helps fellow sellers maximize conversions. Thanks for choosing us!', 'multi-order-tracker' ); ?></p>
											<a
												class="mot-resource-link"
												target="_blank"
												href="<?php echo esc_url('https://thebitcraft.com/support/'); ?>"
												target="_blank"><?php esc_html_e( 'Leave a Review', 'multi-order-tracker' ); ?>
												<?php /* phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage */ ?>
												<img
													src="<?php echo esc_url( MULTI_ORDER_TRACKER_ASSETS . "/images/link.svg" ); ?>"
													alt="<?php esc_html_e( 'Leave a Review', 'multi-order-tracker' ); ?>">
											</a>
										</div>
										<div class="mot-docs-content-wrapper mot-content-details">
											<div class="img-wrap">
												<?php /* phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage */ ?>
												<img src="<?php echo esc_url( MULTI_ORDER_TRACKER_ASSETS . "/images/knowledgebase.svg" ); ?>" alt="<?php esc_html_e( 'Discover Our Knowledge Base', 'multi-order-tracker' ); ?>">
											</div>
											<h3><?php esc_html_e( 'Discover Our Knowledge Base', 'multi-order-tracker' ); ?></h3>
											<p><?php esc_html_e( 'Get started by spending some time with the documentation to familiarize yourself with Multi-Order Tracker and boost your website conversions immediately.', 'multi-order-tracker' ); ?></p>
											<a class="mot-resource-link" target="_blank" href="<?php echo esc_url('https://thebitcraft.com/docs/multi-order-tracker/'); ?>" target="_blank"><?php esc_html_e( 'Documentation', 'multi-order-tracker' ); ?>
												<?php /* phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage */ ?>
												<img src="<?php echo esc_url( MULTI_ORDER_TRACKER_ASSETS . "/images/link.svg" ); ?>" alt="<?php esc_html_e( 'Discover Our Knowledge Base', 'multi-order-tracker' ); ?>">
											</a>
										</div>
										<div class="mot-docs-content-wrapper mot-content-details">
											<div class="img-wrap">
												<?php /* phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage */ ?>
												<img src="<?php echo esc_url( MULTI_ORDER_TRACKER_ASSETS . "/images/support.svg" ); ?>" alt="<?php esc_html_e( 'Need Help?', 'multi-order-tracker' ); ?>">
											</div>
											<h3><?php esc_html_e( 'Need Help?', 'multi-order-tracker' ); ?></h3>
											<p><?php esc_html_e( 'If you experience any issues or need help, please reach out to us or report problems on our support page.', 'multi-order-tracker' ); ?></p>
											<a class="mot-resource-link" target="_blank" href="<?php echo esc_url('https://thebitcraft.com/support/'); ?>" target="_blank"><?php esc_html_e( 'Report a Bug', 'multi-order-tracker' ); ?>
												<?php /* phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage */ ?>
												<img src="<?php echo esc_url( MULTI_ORDER_TRACKER_ASSETS . "/images/link.svg" ); ?>" alt="<?php esc_html_e( 'Need Help?', 'multi-order-tracker' ); ?>">
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
