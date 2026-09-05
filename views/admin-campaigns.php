<?php
/**
 * Admin settings template
 *
 * @version 1.0.0
 */
defined( 'ABSPATH' ) || exit;

// Fallback stats in case the template is rendered without data.
$alertx_campaign_stats = wp_parse_args(
    isset( $alertx_campaign_stats ) && is_array( $alertx_campaign_stats ) ? $alertx_campaign_stats : array(),
    array(
        'active'      => 0,
        'scheduled'   => 0,
        'emails_sent' => 0,
        'redemptions' => 0,
    )
);

// Fallback list data in case the template is rendered without the query results.
$alertx_campaigns       = isset( $alertx_campaigns ) && is_array( $alertx_campaigns ) ? $alertx_campaigns : array();
$alertx_total_campaigns = isset( $alertx_total_campaigns ) ? absint( $alertx_total_campaigns ) : 0;
$paged           = isset( $paged ) ? max( 1, absint( $paged ) ) : 1; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$alertx_items_per_page  = isset( $alertx_items_per_page ) && $alertx_items_per_page > 0 ? absint( $alertx_items_per_page ) : 10;
?>

<div id="alertx" class="alertx wrap alartx-sections">
	<?php include_once ALERTX_PATH .'/views/global/header.php'; ?>

    <section class="page page-campaigns">
        <div class="page-header">
            <div>
                <div class="page-h1"><?php esc_html_e('Campaigns', 'alertx-pro'); ?></div>
                <div class="page-desc">
                    <?php esc_html_e('Manual emails you\'ve sent to subscriber lists — restocks, discounts, updates.', 'alertx-pro'); ?>
                </div>
            </div>
            <div class="page-actions">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=new-campaign' ) ); ?>" class="btn btn-secondary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 2 11 13"></path><path d="M22 2 15 22l-4-9-9-4 20-7Z"></path></svg>
                    <?php esc_html_e('New campaign', 'alertx-pro'); ?>
                </a>
            </div>
        </div>

        <div class="kpi-row">
            <div class="kpi-card">
                <div class="kpi-top">
                    <div class="kpi-value" data-count="<?php echo esc_attr( $alertx_campaign_stats['active'] ); ?>"> <?php echo esc_html( $alertx_campaign_stats['active'] ); ?> </div>
                    <div class="kpi-icon c1">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 11v2a1 1 0 0 0 1 1h2l3.5 4.5a1 1 0 0 0 1.5-.8V6.3a1 1 0 0 0-1.5-.8L6 10H4a1 1 0 0 0-1 1z"></path>
                            <path d="M14 8a4 4 0 0 1 0 8"></path>
                            <path d="M17 5a8 8 0 0 1 0 14"></path>
                        </svg>
                    </div>
                </div>
                <div class="kpi-bottom">
                    <div class="kpi-label"><?php esc_html_e('Active Campaigns', 'alertx-pro'); ?></div>
                    <svg class="kpi-spark" width="72" height="26" viewBox="0 0 72 26">
                        <polyline points="0,20 12,17 24,19 36,12 48,14 60,6 72,4" fill="none" stroke="#3c06c5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></polyline>
                    </svg>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-top">
                    <div class="kpi-value" data-count="<?php echo esc_attr( $alertx_campaign_stats['scheduled'] ); ?>"> <?php echo esc_html( $alertx_campaign_stats['scheduled'] ); ?> </div>
                    <div class="kpi-icon c2">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="17" rx="2"></rect>
                            <path d="M3 9h18"></path>
                            <path d="M8 2v4"></path>
                            <path d="M16 2v4"></path>
                            <path d="M12 13v4l2.5 1.5"></path>
                        </svg>
                    </div>
                </div>
                <div class="kpi-bottom">
                    <div class="kpi-label"><?php esc_html_e('Scheduled', 'alertx-pro'); ?></div>
                    <svg class="kpi-spark" width="72" height="26" viewBox="0 0 72 26">
                        <polyline points="0,18 12,19 24,14 36,16 48,9 60,11 72,3" fill="none" stroke="#D90DD9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></polyline>
                    </svg>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-top">
                    <div class="kpi-value" data-count="<?php echo esc_attr( $alertx_campaign_stats['emails_sent'] ); ?>"> <?php echo esc_html( number_format_i18n( $alertx_campaign_stats['emails_sent'] ) ); ?> </div>
                    <div class="kpi-icon c3">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 2 11 13"></path>
                            <path d="M22 2 15 22l-4-9-9-4 20-7Z"></path>
                        </svg>
                    </div>
                </div>
                <div class="kpi-bottom">
                    <div class="kpi-label"><?php esc_html_e('Emails Delivered', 'alertx-pro'); ?></div>
                    <svg class="kpi-spark" width="72" height="26" viewBox="0 0 72 26">
                        <polyline points="0,10 12,13 24,8 36,11 48,7 60,9 72,5" fill="none" stroke="#FC301D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></polyline>
                    </svg>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-top">
                    <div class="kpi-value" data-count="<?php echo esc_attr( $alertx_campaign_stats['redemptions'] ); ?>"> <?php echo esc_html( number_format_i18n( $alertx_campaign_stats['redemptions'] ) ); ?> </div>
                    <div class="kpi-icon c4">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 12v7a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-7"></path>
                            <rect x="2" y="7" width="20" height="5" rx="1"></rect>
                            <path d="M12 7v13"></path>
                            <path d="M12 7c-1.5-3.5-6-4-6-1.2S8.5 7 12 7z"></path>
                            <path d="M12 7c1.5-3.5 6-4 6-1.2S15.5 7 12 7z"></path>
                        </svg>
                    </div>
                </div>
                <div class="kpi-bottom">
                    <div class="kpi-label"><?php esc_html_e('Coupon Uses This Month', 'alertx-pro'); ?></div>
                    <svg class="kpi-spark" width="72" height="26" viewBox="0 0 72 26">
                        <polyline points="0,10 12,13 24,8 36,11 48,7 60,9 72,5" fill="none" stroke="#FC301D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></polyline>
                    </svg>
                </div>
            </div>
        </div>

        <div class="section">
            <form id="bulk-action-form" method="post">
                <!-- Notifications List -->
                <div class="notifications-list-tablenav">
                    <?php wp_nonce_field( 'bulk_action', 'bulk_action_nonce' ); ?>
                    <div class="bulk-actions">
                        <select name="bulk_action" id="bulk-action-selector">
                            <option value=""><?php esc_html_e('Bulk Actions', 'alertx-pro'); ?></option>
                            <option value="delete"><?php esc_html_e('Delete', 'alertx-pro'); ?></option>
                        </select>
                        <input type="submit" name="submit_bulk_action" class="action btn btn-secondary" value="Apply">
                    </div>

                    <div class="notification-count">
                        <span class="displaying-num">
                            <?php
                                // Ensure $alertx_total_campaigns is a positive integer
                                $alertx_total_campaigns = absint( $alertx_total_campaigns );

                                // Translators: %d is the number of campaigns
                                echo esc_html( sprintf( _n( '%d item', '%d items', $alertx_total_campaigns, 'alertx-pro' ),
                                    $alertx_total_campaigns )
                                );
                            ?>
                        </span>
                    </div>
                </div>

                <table class="wp-list-table widefat striped table-view-list posts">
                    <thead>
                        <tr>
                            <td id="cb" class="manage-column column-cb check-column">
                                <input id="cb-select-all" type="checkbox">
                            </td>
                            <th><?php esc_html_e('Campaign', 'alertx-pro'); ?></th>
                            <th><?php esc_html_e('Recipients', 'alertx-pro'); ?></th>
                            <th><?php esc_html_e('Products', 'alertx-pro'); ?></th>
                            <th><?php esc_html_e('Discount', 'alertx-pro'); ?></th>
                            <th><?php esc_html_e('Schedule', 'alertx-pro'); ?></th>
                            <th><?php esc_html_e('Status', 'alertx-pro'); ?></th>
                            <th><?php esc_html_e('Statistics', 'alertx-pro'); ?></th>
                            <th><?php esc_html_e('Actions', 'alertx-pro'); ?></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ( ! empty( $alertx_campaigns ) ) : ?>
                            <?php foreach ( $alertx_campaigns as $alertx_campaign_row ) : ?>
                                <?php
                                // Recipients text based on recipient type.
                                $alertx_recipients_text = '-';
                                if ( 'subscribers' === $alertx_campaign_row->recipient_type ) {
                                    $alertx_recipients_text = intval( $alertx_campaign_row->total_recipients ) . ' ' . __( 'subscribers', 'alertx-pro' );
                                } elseif ( 'csv' === $alertx_campaign_row->recipient_type ) {
                                    $alertx_recipients_text = intval( $alertx_campaign_row->total_recipients ) . ' ' . __( 'from CSV', 'alertx-pro' );
                                } elseif ( 'specific' === $alertx_campaign_row->recipient_type ) {
                                    $alertx_recipients_text = intval( $alertx_campaign_row->total_recipients ) . ' ' . __( 'specific', 'alertx-pro' );
                                }

                                // Products text - authoritative count comes from the
                                // WooCommerce coupon, same products the email will show.
                                $alertx_coupon_product_count = isset( $alertx_campaign_row->coupon_product_count ) ? intval( $alertx_campaign_row->coupon_product_count ) : 0;
                                $alertx_products_text        = __( 'All products', 'alertx-pro' );

                                if ( $alertx_coupon_product_count > 0 ) {
                                    $alertx_products_text = $alertx_coupon_product_count . ' ' . _n( 'product', 'products', $alertx_coupon_product_count, 'alertx-pro' );
                                } elseif ( 'specific' === $alertx_campaign_row->product_selection ) {
                                    $alertx_selected_products = json_decode( isset( $alertx_campaign_row->selected_products ) ? $alertx_campaign_row->selected_products : '[]', true );

                                    if ( is_array( $alertx_selected_products ) && ! empty( $alertx_selected_products ) ) {
                                        $alertx_products_text = count( $alertx_selected_products ) . ' ' . _n( 'product', 'products', count( $alertx_selected_products ), 'alertx-pro' );
                                    }
                                }

                                // Schedule text - scheduled date or sent date.
                                // MySQL zero dates ('0000-00-00 00:00:00') pass an
                                // empty() check but must not be formatted, otherwise
                                // wp_date() renders "Nov 30, -0001, 12:00 AM".
                                $alertx_mysql_zero_date = '0000-00-00 00:00:00';
                                $alertx_schedule_text   = '-';
                                if ( 'scheduled' === $alertx_campaign_row->camp_status && ! empty( $alertx_campaign_row->scheduled_date ) && $alertx_mysql_zero_date !== $alertx_campaign_row->scheduled_date ) {
                                    $alertx_schedule_text = wp_date( 'M j, Y, g:i A', strtotime( $alertx_campaign_row->scheduled_date ) );
                                } elseif ( 'sent' === $alertx_campaign_row->camp_status && ! empty( $alertx_campaign_row->sent_at ) && $alertx_mysql_zero_date !== $alertx_campaign_row->sent_at ) {
                                    $alertx_schedule_text = wp_date( 'M j, Y, g:i A', strtotime( $alertx_campaign_row->sent_at ) );
                                }

                                // Statistics - only meaningful once the send started.
                                $alertx_stats_text = '-';
                                if ( in_array( $alertx_campaign_row->camp_status, array( 'sent', 'sending' ), true ) ) {
                                    $alertx_stats_text = '✓ ' . intval( $alertx_campaign_row->emails_sent );

                                    if ( ! empty( $alertx_campaign_row->emails_failed ) && intval( $alertx_campaign_row->emails_failed ) > 0 ) {
                                        $alertx_stats_text .= ' | ✗ ' . intval( $alertx_campaign_row->emails_failed );
                                    }
                                }

                                // Status badge data (label, background, color).
                                $alertx_status_badges = array(
                                    'draft'     => array( __( 'Draft', 'alertx-pro' ), '#f1f2f7', '#5b6478' ),
                                    'scheduled' => array( __( 'Scheduled', 'alertx-pro' ), '#fff4e5', '#ff9800' ),
                                    'sending'   => array( __( 'Sent', 'alertx-pro' ), '#e8f5e9', '#4caf50' ),
                                    'sent'      => array( __( 'Sent', 'alertx-pro' ), '#e8f5e9', '#4caf50' ),
                                    'failed'    => array( __( 'Failed', 'alertx-pro' ), '#ffebee', '#f44336' ),
                                    'cancelled' => array( __( 'Cancelled', 'alertx-pro' ), '#f5f5f5', '#9e9e9e' ),
                                );

                                $alertx_current_status = isset( $alertx_status_badges[ $alertx_campaign_row->camp_status ] ) ? $alertx_status_badges[ $alertx_campaign_row->camp_status ] : array( ucfirst( $alertx_campaign_row->camp_status ), '#f1f2f7', '#5b6478' );
                                ?>
                                <tr data-campaign-id="<?php echo esc_attr( $alertx_campaign_row->id ); ?>">
                                    <th scope="row" class="check-column">
                                        <input type="checkbox" name="campaign_ids[]" value="<?php echo esc_attr( $alertx_campaign_row->id ); ?>" />
                                    </th>
                                    <td>
                                        <strong><?php echo esc_html( $alertx_campaign_row->camp_title ); ?></strong><br>
                                        <small style="color: #666;"><?php echo esc_html( $alertx_campaign_row->camp_subject ); ?></small>
                                    </td>
                                    <td><?php echo esc_html( $alertx_recipients_text ); ?></td>
                                    <td><?php echo esc_html( $alertx_products_text ); ?></td>
                                    <td>
                                        <?php if ( ! empty( $alertx_campaign_row->discount_code ) ) : ?>
                                            <span class="code-pill"><?php echo esc_html( $alertx_campaign_row->discount_code ); ?></span>
                                        <?php else : ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html( $alertx_schedule_text ); ?></td>
                                    <td>
                                        <?php
                                        // Draft/sent campaigns get the on/off switcher -
                                        // scheduled/sending/failed must not be interrupted.
                                        if ( in_array( $alertx_campaign_row->camp_status, array( 'draft', 'sent' ), true ) ) :
                                            $alertx_switch_title = 'draft' === $alertx_campaign_row->camp_status ? __( 'Publish campaign', 'alertx-pro' ) : __( 'Move back to draft', 'alertx-pro' );
                                            ?>
                                            <div class="status">
                                                <span class="badge" style="background: <?php echo esc_attr( $alertx_current_status[1] ); ?>; color: <?php echo esc_attr( $alertx_current_status[2] ); ?>;">
                                                    <?php echo esc_html( $alertx_current_status[0] ); ?>
                                                </span>
                                                <label class="switch" title="<?php echo esc_attr( $alertx_switch_title ); ?>">
                                                    <input type="checkbox" class="status-toggle"<?php checked( 'sent', $alertx_campaign_row->camp_status ); ?>>
                                                    <span class="slider"></span>
                                                </label>
                                            </div>
                                        <?php else : ?>
                                            <span class="badge" style="background: <?php echo esc_attr( $alertx_current_status[1] ); ?>; color: <?php echo esc_attr( $alertx_current_status[2] ); ?>;"><?php echo esc_html( $alertx_current_status[0] ); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html( $alertx_stats_text ); ?></td>
                                    <td class="actions">
                                        <button type="button" class="kebab" data-action="menu" title="Actions">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="none"><circle cx="12" cy="5" r="1.8"></circle><circle cx="12" cy="12" r="1.8"></circle><circle cx="12" cy="19" r="1.8"></circle></svg>
                                        </button>
                                        <div class="menu">
                                            <button type="button" data-action="view">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                <?php esc_html_e( 'Quick view', 'alertx-pro' ); ?>
                                            </button>
                                            <button type="button" data-action="edit">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                                <?php esc_html_e( 'Edit', 'alertx-pro' ); ?>
                                            </button>
                                            <button type="button" data-action="duplicate">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                                <?php esc_html_e( 'Duplicate', 'alertx-pro' ); ?>
                                            </button>
                                            <button type="button" data-action="delete" class="danger">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                <?php esc_html_e( 'Delete', 'alertx-pro' ); ?>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="9" class="no-info">
                                    <?php esc_html_e( 'No campaigns found. Create your first campaign!', 'alertx-pro' ); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php
                    // Pagination links
                    $alertx_pagination_args = array(
                        'base' => add_query_arg( 'paged', '%#%'),
                        'format' => '',
                        'prev_text' => __( '&laquo; Previous', 'alertx-pro'),
                        'next_text' => __( 'Next &raquo;', 'alertx-pro'),
                        'total' => ceil( $alertx_total_campaigns / $alertx_items_per_page ),
                        'current' => $paged,
                    );

                    if ( $alertx_pagination_args['total'] > 1 ) {
                        echo '<div class="pagination-container">';
                            echo wp_kses_post( paginate_links( $alertx_pagination_args ) );
                        echo '</div>';
                    }
                ?>
            </form>
        </div>
    </section>

    <!-- Campaign Quick View Modal (email preview) -->
    <div class="overlay preview-overlay" id="campaign-preview-overlay">
        <div class="modal preview-modal">
            <div class="modal-head">
                <div>
                    <h3 id="preview-modal-title"><?php esc_html_e( 'Campaign Preview', 'alertx-pro' ); ?></h3>
                    <p id="preview-modal-subject"></p>
                </div>
                <button type="button" class="modal-close" id="preview-modal-close">&times;</button>
            </div>
            <div class="modal-body preview-body">
                <div class="email-preview-container" id="preview-email-container"></div>
            </div>
            <div class="modal-foot">
                <div class="preview-info">
                    <div class="preview-info-item">
                        <span class="preview-info-label"><?php esc_html_e( 'Note', 'alertx-pro' ); ?></span>
                        <span><?php esc_html_e( 'This is how the email will appear to your customers.', 'alertx-pro' ); ?></span>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary" id="preview-modal-close-footer"><?php esc_html_e( 'Close', 'alertx-pro' ); ?></button>
            </div>
        </div>
    </div>
</div>
