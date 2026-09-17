<?php
/**
 * Admin Subscribers template
 *
 * @version 1.0.0
 */
defined( 'ABSPATH' ) || exit;

// Allow-list for the hardcoded KPI SVG icons passed in $subscribers_stats.
$restockx_svg_allowed = array(
    'svg'      => array(
        'width'             => true,
        'height'            => true,
        'viewbox'           => true,
        'fill'              => true,
        'stroke'            => true,
        'stroke-width'      => true,
        'stroke-linecap'    => true,
        'stroke-linejoin'   => true,
        'xmlns'             => true,
        'class'             => true,
        'xml:space'         => true,
    ),
    'g'         => array( 'fill' => true, 'stroke' => true ),
    'path'      => array(
        'd'         => true,
        'fill'      => true,
        'stroke'    => true,
        'stroke-width' => true,
    ),
    'circle'    => array( 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ),
    'rect'      => array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ),
    'line'      => array( 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true ),
    'polyline'  => array( 'points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true ),
    'polygon'   => array( 'points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linejoin' => true ),
);
?>

<div class="restockx wrap alartx-sections">
    <?php include_once RESTOCKX_PATH .'/views/global/header.php'; ?>

    <section class="page">
        <div class="page-header">
			<div>
				<div class="page-h1">
                    <?php esc_html_e( 'Subscribers', 'restockx' ); ?>
                </div>
				<div class="page-desc">
                    <?php esc_html_e( 'Track waitlists, alerts and conversions in real time', 'restockx' ); ?>
                </div>
			</div>
		</div>

        <div class="kpi-row">
            <?php if ( ! empty( $subscribers_stats ) ) : ?>
                <?php foreach ( $subscribers_stats as $restockx_stat ) : ?>
                    <?php $restockx_stat_locked = ! empty( $restockx_stat['pro'] ) && restockx_show_upgrade_cta(); ?>
                    <div class="kpi-card<?php echo $restockx_stat_locked ? ' is-locked' : ''; ?>">
                        <div class="kpi-top">
                            <?php if ( $restockx_stat_locked ) : ?>
                                <div class="kpi-value kpi-value-locked">
                                    <span class="kpi-lock" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="11" width="18" height="11" rx="2" />
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                        </svg>
                                    </span>
                                </div>
                            <?php else : ?>
                                <div class="kpi-value"<?php echo isset( $restockx_stat['data_count'] ) ? ' data-count="' . esc_attr( $restockx_stat['data_count'] ) . '"' : ''; ?>>
                                    <?php echo esc_html( isset( $restockx_stat['value'] ) ? $restockx_stat['value'] : 0 ); ?>
                                </div>
                            <?php endif; ?>
                            <div class="kpi-icon <?php echo esc_attr( $restockx_stat['icon_class'] ); ?>">
                                <?php echo wp_kses( $restockx_stat['icon'], $restockx_svg_allowed ); ?>
                            </div>
                        </div>
                        <div class="kpi-bottom">
                            <div class="kpi-label"><?php echo esc_html( $restockx_stat['label'] ); ?></div>
                            <?php if ( ! empty( $restockx_stat['pro'] ) && restockx_show_upgrade_cta() ) : ?>
                                <a class="go-premium go-premium-sm" href="https://example.com/upgrade" target="_blank" rel="noopener">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="11" width="18" height="11" rx="2" />
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                    </svg>
                                    <?php esc_html_e( 'Go Premium', 'restockx' ); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="section">
            <form id="bulk-action-form" method="post">
                <!-- Notifications List -->
                <div class="notifications-list-tablenav">
                    <?php wp_nonce_field( 'restockx_bulk_action', 'bulk_action_nonce' ); ?>
                    <div class="bulk-actions">
                        <select name="bulk_action" id="bulk-action-selector">
                            <option value=""><?php esc_html_e( 'Bulk Actions', 'restockx' ); ?></option>
                            <option value="delete"><?php esc_html_e( 'Delete', 'restockx' ); ?></option>
                        </select>
                        <input type="submit" name="submit_bulk_action" class="action btn btn-secondary" value="<?php esc_attr_e( 'Apply', 'restockx' ); ?>">
                    </div>

                    <div class="notification-count">
                        <span class="displaying-num">
                            <?php
                                // Ensure $total_notifications is a positive integer
                                $total_notifications = absint( $total_notifications ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

                                // Translators: %d is the number of notifications
                                echo esc_html( sprintf( _n( '%d item', '%d items', $total_notifications, 'restockx' ),
                                    $total_notifications )
                                );
                            ?>
                        </span>

                        <div class="export_csv pro-export-wrap">
                            <?php if ( restockx_show_upgrade_cta() ) : ?>
                                <button type="button" class="btn btn-secondary is-locked" disabled>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                        <path d="M7 10l5 5 5-5" />
                                        <path d="M12 15V3" />
                                    </svg>
                                    <?php esc_attr_e( 'Export to CSV', 'restockx' ); ?>
                                </button>
                                <span class="pro-lock">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="11" width="18" height="11" rx="2" />
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                    </svg>
                                </span>
                            <?php else : ?>
                                <button type="submit" name="export_csv" value="1" class="btn btn-secondary">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                        <path d="M7 10l5 5 5-5" />
                                        <path d="M12 15V3" />
                                    </svg>
                                    <?php esc_attr_e( 'Export to CSV', 'restockx' ); ?>
                                </button>
                            <?php endif; ?>
                        </div>

                        <?php if ( restockx_show_upgrade_cta() ) : ?>
                            <a class="go-premium go-premium-sm" href="https://example.com/upgrade" target="_blank" rel="noopener">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                                <?php esc_html_e( 'Go Premium', 'restockx' ); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <table class="wp-list-table widefat striped table-view-list posts">
                    <thead>
                        <tr>
                            <td id="cb" class="manage-column column-cb check-column">
                                <input id="cb-select-all" type="checkbox">
                            </td>
                            <th><?php esc_html_e( 'Product', 'restockx' ); ?></th>
                            <th><?php esc_html_e( 'Email', 'restockx' ); ?></th>
                            <th><?php esc_html_e( 'Date', 'restockx' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'restockx' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( ! empty( $notifications ) && function_exists( 'wc_get_product' ) ) : ?>
                            <?php foreach ( $notifications as $notification ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
                                <?php
                                $product = wc_get_product( $notification->product_id ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

                                // Get status with proper fallback
                                $status = isset( $notification->status ) ? ucfirst( $notification->status ) : 'Unknown';
                                $restockx_status_class = 'status-' . sanitize_html_class( strtolower( $status ) );
                                ?>
                                <tr>
                                    <th scope="row" class="check-column">
                                        <input type="checkbox" name="notifications[]" value="<?php echo esc_attr( $notification->id ); ?>" />
                                    </th>
                                    <td>
                                        <?php if ( $product ) : ?>
                                            <div class="pulse-info">
                                                <div class="pname">
                                                    <a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>">
                                                        <?php
                                                        $image_id = $product->get_image_id(); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                                                        $image_url = wp_get_attachment_image_url( $image_id, 'thumbnail' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                                                        if ( $image_url ) {
                                                            echo '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $product->get_name() ) . '" width="44" height="44" style="vertical-align: middle; margin-right: 8px; border-radius: 4px;" />';
                                                        }
                                                        $restockx_parent_product = wc_get_product( $product->get_parent_id() );
                                                        if ( $restockx_parent_product ) {
                                                            echo esc_html( $restockx_parent_product->get_name() );
                                                        } else {
                                                            echo esc_html( $product->get_name() );
                                                        }
                                                        ?>
                                                    </a>
                                                </div>

                                                <?php
                                                    // Only show attribute summary for product variations
                                                    if ( $product->is_type( 'variation' ) && method_exists( $product, 'get_attribute_summary' ) ) {
                                                        $restockx_attribute_summary = $product->get_attribute_summary();
                                                        ?>
                                                        <div class="pvariant">
                                                            <?php echo esc_html( $restockx_attribute_summary ); ?>
                                                        </div>
                                                        <?php
                                                    }
                                                ?>
                                            </div>
                                        <?php else : ?>
                                            <?php esc_html_e( 'Product not found', 'restockx' ); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="mailto:<?php echo esc_html( $notification->email ); ?>"><?php echo esc_html( $notification->email ); ?></a>
                                    </td>
                                    <td>
                                        <?php echo esc_html( wp_date( 'd M, Y - h:i A', strtotime( $notification->date_added ) ) ); ?>
                                    </td>
                                    <td>
                                        <span class="status-pill subscription-status <?php echo esc_attr( $restockx_status_class ); ?>">
                                            <?php echo esc_html( $status ); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5" class="no-info">
                                    <?php esc_html_e( 'No notifications found!', 'restockx' ); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php
                    // Pagination links
                    $pagination_args = array( // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                        'base' => add_query_arg( 'paged', '%#%'),
                        'format' => '',
                        'prev_text' => __( '&laquo; Previous', 'restockx'),
                        'next_text' => __( 'Next &raquo;', 'restockx'),
                        'total' => ceil( $total_notifications / $restockx_items_per_page ),
                        'current' => $paged,
                    );

                    if ( $pagination_args['total'] > 1 ) {
                        echo '<div class="pagination-container">';
                            echo wp_kses_post( paginate_links( $pagination_args ) );
                        echo '</div>';
                    }
                ?>
            </form>
        </div>
    </section>
</div>
