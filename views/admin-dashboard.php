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
				<div class="alertx-admin-wrapper">
					<div>
						<div class="alertx-settings-header">
							<div class="alertx-header-left">
								<div class="alertx-admin-header">
									<img src="<?php echo ALERTX_URL . 'assets/src/img/logo.png'; ?>" alt="">
								</div>
							</div>

							<div class="alertx-header-right">
								<span>
									<?php
										// translators: %s: plugin version number
										$version_text = esc_html__( 'Current Version: %s', 'multi-order-tracker' );
										echo wp_kses_post( sprintf( $version_text, '<strong>' . esc_html( ALERTX_VERSION ) . '</strong>' ) );
									?>
								</span>
							</div>
						</div>

						<!-- Analytics counter wrapper -->
						<div class="alertx-analytics-counter-wrapper">
							<div>
								<div class="alertx-analytics-counter">
									<div class="counter-wrap">
										<span class="alertx-counter-icon">
											<svg xmlns="http://www.w3.org/2000/svg" width="65" height="65" viewBox="0 0 24 24" fill="none" stroke="#FF6106" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-bag"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path><path d="M3 6h18"></path><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
										</span>
										<div>
											<span class="alertx-counter-number">0</span>
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
											<svg xmlns="http://www.w3.org/2000/svg" width="65" height="65" viewBox="0 0 24 24" fill="none" stroke="#FF6106" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-bag"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path><path d="M3 6h18"></path><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
										</span>
										<div>
											<span class="alertx-counter-number">0</span>
											<span class="alertx-counter-label">
												<?php esc_html_e( 'Total Out of Stock Products', 'multi-order-tracker' ); ?>
											</span>
										</div>
									</div>
								</div>
							</div>

							<div>
								<div class="alertx-analytics-counter">
									<div class="counter-wrap">
										<span class="alertx-counter-icon">
											<svg xmlns="http://www.w3.org/2000/svg" width="65" height="65" viewBox="0 0 24 24" fill="none" stroke="#FF6106" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
										</span>
										<div>
											<span class="alertx-counter-number">0</span>
											<span class="alertx-counter-label">
												<?php esc_html_e( 'Total Subscribe Customers', 'multi-order-tracker' ); ?>
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
									<div class="multi-order-form alertx-tabs-wrapper">
										<div class="alertx-tab-menu-wrapper">
											<ul class="alertx-tab-nav">
												<li class="alertx-tab-nav-item tab-templates" data-key="tab-templates">
													<span>
														<span class="dashicons dashicons-art"></span>
														<?php esc_html_e( 'Templates', 'multi-order-tracker' ); ?>
													</span>
												</li>
												<li class="alertx-tab-nav-item tab-advanced-settings" data-key="tab-advanced-settings">
													<span>
														<span class="dashicons dashicons-admin-settings"></span>
														<?php esc_html_e( 'Advanced Settings', 'multi-order-tracker' ); ?>
													</span>
												</li>
											</ul>
										</div>
										<div class="alertx-tab-content-wrapper">
											<div class="alertx-tab-flex">
												<div class="alertx-tab-contents">
													<div id="tab-templates" class="alertx-tab-content alertx-tab-tab-templates alertx-active">
														<div id="section-modules" class="alertx-control-section section-modules">
															<div class="alertx-section-title">
																<h4><?php esc_html_e( 'Templates', 'multi-order-tracker' ); ?></h4>
															</div>
															<div class="alertx-section-fields">
																<div class="alertx-toggle-wrapper alertx-control">
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
													src="<?php echo esc_url( MULTI_ORDER_TRACKER_ASSETS . "/images/love.svg" ); ?>"
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
													src="<?php echo esc_url( MULTI_ORDER_TRACKER_ASSETS . "/images/link.svg" ); ?>"
													alt="<?php esc_html_e( 'Leave a Review', 'multi-order-tracker' ); ?>">
											</a>
										</div>
										<div class="alertx-docs-content-wrapper alertx-content-details">
											<div class="img-wrap">
												<?php /* phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage */ ?>
												<img src="<?php echo esc_url( MULTI_ORDER_TRACKER_ASSETS . "/images/knowledgebase.svg" ); ?>" alt="<?php esc_html_e( 'Discover Our Knowledge Base', 'multi-order-tracker' ); ?>">
											</div>
											<h3><?php esc_html_e( 'Discover Our Knowledge Base', 'multi-order-tracker' ); ?></h3>
											<p><?php esc_html_e( 'Get started by spending some time with the documentation to familiarize yourself with Multi-Order Tracker and boost your website conversions immediately.', 'multi-order-tracker' ); ?></p>
											<a class="alertx-resource-link" target="_blank" href="<?php echo esc_url('https://thebitcraft.com/docs/multi-order-tracker/'); ?>" target="_blank"><?php esc_html_e( 'Documentation', 'multi-order-tracker' ); ?>
												<?php /* phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage */ ?>
												<img src="<?php echo esc_url( MULTI_ORDER_TRACKER_ASSETS . "/images/link.svg" ); ?>" alt="<?php esc_html_e( 'Discover Our Knowledge Base', 'multi-order-tracker' ); ?>">
											</a>
										</div>
										<div class="alertx-docs-content-wrapper alertx-content-details">
											<div class="img-wrap">
												<?php /* phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage */ ?>
												<img src="<?php echo esc_url( MULTI_ORDER_TRACKER_ASSETS . "/images/support.svg" ); ?>" alt="<?php esc_html_e( 'Need Help?', 'multi-order-tracker' ); ?>">
											</div>
											<h3><?php esc_html_e( 'Need Help?', 'multi-order-tracker' ); ?></h3>
											<p><?php esc_html_e( 'If you experience any issues or need help, please reach out to us or report problems on our support page.', 'multi-order-tracker' ); ?></p>
											<a class="alertx-resource-link" target="_blank" href="<?php echo esc_url('https://thebitcraft.com/support/'); ?>" target="_blank"><?php esc_html_e( 'Report a Bug', 'multi-order-tracker' ); ?>
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
