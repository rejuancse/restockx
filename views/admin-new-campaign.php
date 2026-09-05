<?php
/**
 * New Campaign Page Template
 *
 * @version 1.0.0
 */
defined( 'ABSPATH' ) || exit;

// Read-only "edit campaign" ID from the page URL; only used to load data,
// no action is performed, so no nonce applies.
$alertx_campaign_id = isset( $_GET['campaign'] ) ? intval( $_GET['campaign'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only GET param, cast to int, used only to load a campaign for editing.

?>

<div id="alertx" class="alertx wrap alartx-sections">
	<?php include_once ALERTX_PATH .'/views/global/header.php'; ?>

    <section class="page new-campaign">
        <div class="page-header">
			<div>
				<div class="page-h1"><?php echo $alertx_campaign_id ? esc_html__( 'Edit Campaign', 'alertx-pro' ) : esc_html__( 'Create New Campaign', 'alertx-pro' ); ?></div>
				<div class="page-desc"><?php esc_html_e( 'Send targeted email campaigns to your subscribers', 'alertx-pro' ); ?></div>
			</div>
			<div class="page-actions">
				<a class="btn btn-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=campaigns' ) ); ?>">
                    <?php esc_html_e( 'Back to Campaigns', 'alertx-pro' ); ?>
				</a>
			</div>
		</div>

        <div class="section p-20">
            <div id="status-message"></div>
            <form id="new-campaign-form" method="post">
                <?php wp_nonce_field( 'alertx_new_campaign', 'campaign_nonce' ); ?>
                <input type="hidden" name="campaign_id" value="<?php echo intval( $alertx_campaign_id ); ?>">

                <table class="form-table">
                    <tr>
                        <th>
                            <label for="campaign-title">
                                <?php esc_html_e( 'Campaign Title', 'alertx-pro' ); ?>
                                <span class="required">*</span>
                            </label>
                        </th>
                        <td>
                            <input type="text" id="campaign-title" name="campaign_title" class="regular-text" placeholder="<?php echo esc_attr( __( 'e.g., Summer Sale 2026', 'alertx-pro' ) ); ?>" required>
                            <p class="description"><?php esc_html_e( 'Internal name for this campaign (not shown to recipients)', 'alertx-pro' ); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="campaign-subject"><?php esc_html_e( 'Email Subject Line', 'alertx-pro' ); ?> <span class="required">*</span></label></th>
                        <td>
                            <input type="text" id="campaign-subject" name="campaign_subject" class="regular-text" placeholder="<?php echo esc_attr( __( 'e.g., Special Offer Just for You!', 'alertx-pro' ) ); ?>" required>
                            <p class="description"><?php esc_html_e( 'This will be the subject line of your email', 'alertx-pro' ); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th><?php esc_html_e( 'Select Recipients', 'alertx-pro' ); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="radio" name="recipient_type" value="subscribers" checked>
                                    <?php esc_html_e( 'Subscribers', 'alertx-pro' ); ?>
                                </label>
                                <p class="description"><?php esc_html_e( 'Send to all confirmed AlertX subscribers', 'alertx-pro' ); ?></p>

                                <label style="margin-top: 15px; display: block;">
                                    <input type="radio" name="recipient_type" value="csv">
                                    <?php esc_html_e( 'CSV Import', 'alertx-pro' ); ?>
                                </label>

                                <div id="csv-import-area" style="display: none; margin: 10px 0 0 20px;">
                                    <div class="csv-drop-zone" id="csv-drop-zone">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="17 8 12 3 7 8"></polyline>
                                            <line x1="12" y1="3" x2="12" y2="15"></line>
                                        </svg>
                                        <div><?php esc_html_e( 'Drop CSV file here or click to upload', 'alertx-pro' ); ?></div>
                                        <div class="description"><?php esc_html_e( 'File should contain email addresses', 'alertx-pro' ); ?></div>
                                        <input type="file" id="csv-file-input" accept=".csv" style="display: none;">
                                    </div>
                                    <div id="csv-import-status" style="display: none; margin-top: 10px; padding: 10px; background: #fff; border-left: 4px solid #46b450; box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);">
                                        <strong><?php esc_html_e( 'Import Successful!', 'alertx-pro' ); ?></strong>
                                        <span id="csv-count"></span>
                                    </div>
                                </div>

                                <label style="margin-top: 15px; display: block;">
                                    <input type="radio" name="recipient_type" value="specific">
                                    <?php esc_html_e( 'Specific Emails', 'alertx-pro' ); ?>
                                </label>

                                <div id="specific-emails-area" style="display: none; margin: 10px 0 0 20px;">
                                    <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                                        <input type="email" id="email-input" class="regular-text" placeholder="<?php echo esc_attr( __( 'Enter email address', 'alertx-pro' ) ); ?>">
                                        <button type="button" class="button" id="btn-add-email"><?php esc_html_e( 'Add', 'alertx-pro' ); ?></button>
                                    </div>
                                    <div class="email-list" id="email-list">
                                        <div class="description"><?php esc_html_e( 'No emails added yet', 'alertx-pro' ); ?></div>
                                    </div>
                                </div>
                            </fieldset>
                        </td>
                    </tr>

                    <tr>
                        <th><label><?php esc_html_e( 'Add Discount Code', 'alertx-pro' ); ?></label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="include-coupon" name="include_coupon">
                                <?php esc_html_e( 'Include discount code in email', 'alertx-pro' ); ?>
                            </label>

                            <div id="coupon-fields" style="display: none; margin: 10px 0 0 20px;">
                                <div class="form-field">
                                    <label for="coupon-code"><?php esc_html_e( 'Coupon Code', 'alertx-pro' ); ?>:</label>
                                    <input type="text" id="coupon-code" name="coupon_code" class="regular-text" placeholder="<?php echo esc_attr( __( 'e.g., SUMMER20', 'alertx-pro' ) ); ?>">
                                    <div id="coupon-validation" style="margin-top: 5px; font-size: 12px;"></div>
                                </div>

                                <div class="form-field" style="margin-top: 10px;">
                                    <label for="coupon-expiry"><?php esc_html_e( 'Expires After', 'alertx-pro' ); ?>:</label>
                                    <input type="date" id="coupon-expiry" name="coupon_expiry" class="regular-text">
                                </div>

                                <div id="coupon-products-list" style="margin-top: 12px;"></div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th><?php esc_html_e( 'Schedule Campaign', 'alertx-pro' ); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="radio" name="schedule_type" value="now" checked>
                                    <?php esc_html_e( 'Send Now', 'alertx-pro' ); ?>
                                </label>

                                <label style="margin-top: 15px; display: block;">
                                    <input type="radio" name="schedule_type" value="later">
                                    <?php esc_html_e( 'Schedule for Later', 'alertx-pro' ); ?>
                                </label>

                                <div id="schedule-fields" style="display: none; margin: 10px 0 0 20px;">
                                    <label for="scheduled-date"><?php esc_html_e( 'Schedule Date & Time', 'alertx-pro' ); ?>:</label>
                                    <input type="datetime-local" id="scheduled-date" name="scheduled_date" class="regular-text">
                                </div>
                            </fieldset>
                        </td>
                    </tr>

                    <tr>
                        <th><?php esc_html_e( 'Select Email Template', 'alertx-pro' ); ?></th>
                        <td>
                            <div class="template-grid" id="template-selector">
                                <div class="template-card active" data-template="default">
                                    <input type="radio" name="email_template" value="default" checked hidden>
                                    <div class="template-thumb">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="3" width="18" height="18" rx="2" />
                                            <path d="M3 9h18M9 21V9" />
                                        </svg>
                                    </div>
                                    <div class="template-meta">
                                        <div class="tname"><?php esc_html_e( 'Default Template', 'alertx-pro' ); ?></div>
                                        <div class="tdesc"><?php esc_html_e( 'Clean and professional design', 'alertx-pro' ); ?></div>
                                        <button type="button" class="template-preview-btn" data-template="default">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="template-card" data-template="modern">
                                    <input type="radio" name="email_template" value="modern" hidden>
                                    <div class="template-thumb">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                                        </svg>
                                    </div>
                                    <div class="template-meta">
                                        <div class="tname"><?php esc_html_e( 'Modern Template', 'alertx-pro' ); ?></div>
                                        <div class="tdesc"><?php esc_html_e( 'Bold and eye-catching design', 'alertx-pro' ); ?></div>
                                        <button type="button" class="template-preview-btn" data-template="modern">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="template-card" data-template="minimal">
                                    <input type="radio" name="email_template" value="minimal" hidden>
                                    <div class="template-thumb">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="4" y1="21" x2="4" y2="14" />
                                            <line x1="4" y1="10" x2="4" y2="3" />
                                            <line x1="12" y1="21" x2="12" y2="3" />
                                            <line x1="20" y1="21" x2="20" y2="14" />
                                            <line x1="20" y1="10" x2="20" y2="3" />
                                        </svg>
                                    </div>
                                    <div class="template-meta">
                                        <div class="tname"><?php esc_html_e( 'Minimal Template', 'alertx-pro' ); ?></div>
                                        <div class="tdesc"><?php esc_html_e( 'Simple text-focused design', 'alertx-pro' ); ?></div>
                                        <button type="button" class="template-preview-btn" data-template="minimal">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="template-card" data-template="product">
                                    <input type="radio" name="email_template" value="product" hidden>
                                    <div class="template-thumb">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="2" y="7" width="20" height="14" rx="2" />
                                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                                        </svg>
                                    </div>
                                    <div class="template-meta">
                                        <div class="tname"><?php esc_html_e( 'Product Showcase', 'alertx-pro' ); ?></div>
                                        <div class="tdesc"><?php esc_html_e( 'Highlight products with images', 'alertx-pro' ); ?></div>
                                        <button type="button" class="template-preview-btn" data-template="product">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="campaign-message"><?php esc_html_e( 'Email Message', 'alertx-pro' ); ?></label></th>
                        <td>
                            <?php
                            // Default template content - this will be overridden by JavaScript
                            $alertx_default_campaign_message = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">' . "\n";
                            $alertx_default_campaign_message .= '<h2 style="color: #3c06c5; margin-bottom: 20px;">{campaign_title}</h2>' . "\n";
                            $alertx_default_campaign_message .= '<p style="color: #333; line-height: 1.6; margin-bottom: 15px;">Hello {first_name}! We have an exciting offer just for you!</p>' . "\n";
                            $alertx_default_campaign_message .= '<p style="color: #555; line-height: 1.6; margin-bottom: 15px;">Check out our amazing products and take advantage of this special deal.</p>' . "\n";
                            $alertx_default_campaign_message .= '<div style="background: #f9f9f9; border: 2px dashed #ddd; border-radius: 8px; padding: 20px; text-align: center; margin: 25px 0;">' . "\n";
                            $alertx_default_campaign_message .= '<div style="color: #666; font-size: 12px; text-transform: uppercase; margin-bottom: 10px;">Your Discount Code</div>' . "\n";
                            $alertx_default_campaign_message .= '<div style="font-size: 28px; font-weight: 700; color: #3c06c5; letter-spacing: 2px; margin: 10px 0;">{discount_code}</div>' . "\n";
                            $alertx_default_campaign_message .= '<div style="color: #3c06c5; font-size: 15px; font-weight: 600; margin-top: 5px;">{discount_amount}</div>' . "\n";
                            $alertx_default_campaign_message .= '<div style="color: #777; font-size: 13px;">{discount_expiry}</div>' . "\n";
                            $alertx_default_campaign_message .= '</div>' . "\n";
                            $alertx_default_campaign_message .= '<div style="margin: 25px 0;">{product_list}</div>' . "\n";
                            $alertx_default_campaign_message .= '<div style="text-align: center; margin: 30px 0;">' . "\n";
                            $alertx_default_campaign_message .= '{cta_button}' . "\n";
                            $alertx_default_campaign_message .= '</div>' . "\n";
                            $alertx_default_campaign_message .= '<p style="color: #666; font-size: 13px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0;">Thank you for being part of our community!</p>' . "\n";
                            $alertx_default_campaign_message .= '</div>';

                            wp_editor(
                                $alertx_default_campaign_message,
                                'campaign-message',
                                array(
                                    'textarea_name' => 'campaign_message',
                                    'media_buttons' => true,
                                    'textarea_rows' => 23,
                                    'teeny' => false,
                                )
                            );
                            ?>
                            <p class="description">
                                <?php esc_html_e( 'Available placeholders: {campaign_title}, {first_name}, {discount_code}, {discount_amount}, {discount_expiry}, {product_list} (coupon products), {cta_button} (All Products Discount), {site_name}', 'alertx-pro' ); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="button" class="button" id="btn-cancel" onclick="if(confirm('<?php echo esc_js( __( 'Are you sure you want to cancel? Any unsaved changes will be lost.', 'alertx-pro' ) ); ?>')){ window.location.href='<?php echo esc_js( admin_url( 'admin.php?page=campaigns' ) ); ?>'; }">
                        <?php esc_html_e( 'Cancel', 'alertx-pro' ); ?>
                    </button>
                    <button type="button" class="button button-secondary" id="btn-save-draft">
                        <?php esc_html_e( 'Save Draft', 'alertx-pro' ); ?>
                    </button>
                    <button type="button" class="button button-primary" id="btn-send-campaign">
                        <?php esc_html_e( 'Send Campaign', 'alertx-pro' ); ?>
                    </button>
                </p>
            </form>
        </div>
    </section>

    <!-- Template Preview Modal -->
    <div id="template-preview-modal" style="display: none;">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h2><?php esc_html_e( 'Template Preview', 'alertx-pro' ); ?></h2>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Preview will be dynamically populated -->
                <div id="template-preview-content">
                    <div class="email-preview-wrapper">
                        <div class="email-preview-header">
                            <span class="preview-label"><?php esc_html_e( 'Subject:', 'alertx-pro' ); ?></span>
                            <span class="preview-subject" id="preview-subject-text"><?php esc_html_e( 'Your email subject will appear here', 'alertx-pro' ); ?></span>
                        </div>
                        <div class="email-preview-body" id="preview-body-content">
                            <!-- Dynamic content will be inserted here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
