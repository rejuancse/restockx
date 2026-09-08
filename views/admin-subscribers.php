<?php
/**
 * Admin Subscribers template
 *
 * @version 1.0.0
 */
defined( 'ABSPATH' ) || exit;

// Allow-list for the hardcoded KPI SVG icons passed in $subscribers_stats.
$alertx_svg_allowed = array(
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

<div class="alertx wrap alartx-sections">
    <?php include_once ALERTX_PATH .'/views/global/header.php'; ?>

    <section class="page">
        <div class="page-header">
			<div>
				<div class="page-h1">
                    <?php esc_html_e( 'Subscribers', 'alertx' ); ?>
                </div>
				<div class="page-desc">
                    <?php esc_html_e( 'Track waitlists, alerts and conversions in real time', 'alertx' ); ?>
                </div>
			</div>
		</div>

        <div class="kpi-row">
            <?php if ( ! empty( $subscribers_stats ) ) : ?>
                <?php foreach ( $subscribers_stats as $alertx_stat ) : ?>
                    <div class="kpi-card">
                        <div class="kpi-top">
                            <div class="kpi-value"<?php echo isset( $alertx_stat['data_count'] ) ? ' data-count="' . esc_attr( $alertx_stat['data_count'] ) . '"' : ''; ?>>
                                <?php echo esc_html( $alertx_stat['value'] ); ?>
                            </div>
                            <div class="kpi-icon <?php echo esc_attr( $alertx_stat['icon_class'] ); ?>">
                                <?php echo wp_kses( $alertx_stat['icon'], $alertx_svg_allowed ); ?>
                            </div>
                        </div>
                        <div class="kpi-bottom">
                            <div class="kpi-label"><?php echo esc_html( $alertx_stat['label'] ); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="section">
            <form id="bulk-action-form" method="post">
                <!-- Notifications List -->
                <div class="notifications-list-tablenav">
                    <?php wp_nonce_field( 'alertxwc_bulk_action', 'bulk_action_nonce' ); ?>
                    <div class="bulk-actions">
                        <select name="bulk_action" id="bulk-action-selector">
                            <option value=""><?php esc_html_e( 'Bulk Actions', 'alertx' ); ?></option>
                            <option value="delete"><?php esc_html_e( 'Delete', 'alertx' ); ?></option>
                        </select>
                        <input type="submit" name="submit_bulk_action" class="action btn btn-secondary" value="<?php esc_attr_e( 'Apply', 'alertx' ); ?>">
                    </div>

                    <div class="notification-count">
                        <span class="displaying-num">
                            <?php
                                // Ensure $total_notifications is a positive integer
                                $total_notifications = absint( $total_notifications ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

                                // Translators: %d is the number of notifications
                                echo esc_html( sprintf( _n( '%d item', '%d items', $total_notifications, 'alertx' ),
                                    $total_notifications )
                                );
                            ?>
                        </span>

                        <div class="export_csv">
                            <?php wp_nonce_field( 'alertxwc_export', 'alertxwc_export_nonce' ); ?>
                            <button type="submit" name="export_csv" class="btn btn-secondary">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <path d="M7 10l5 5 5-5" />
                                    <path d="M12 15V3" />
                                </svg>
                                <?php esc_attr_e( 'Export to CSV', 'alertx' ); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <table class="wp-list-table widefat striped table-view-list posts">
                    <thead>
                        <tr>
                            <td id="cb" class="manage-column column-cb check-column">
                                <input id="cb-select-all" type="checkbox">
                            </td>
                            <th><?php esc_html_e( 'Product', 'alertx' ); ?></th>
                            <th><?php esc_html_e( 'Email', 'alertx' ); ?></th>
                            <th><?php esc_html_e( 'Date', 'alertx' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'alertx' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( ! empty( $notifications ) && function_exists( 'wc_get_product' ) ) : ?>
                            <?php foreach ( $notifications as $notification ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
                                <?php
                                $product = wc_get_product( $notification->product_id ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

                                // Get status with proper fallback
                                $status = isset( $notification->status ) ? ucfirst( $notification->status ) : 'Unknown';
                                $alertx_status_class = 'status-' . sanitize_html_class( strtolower( $status ) );
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
                                                        $alertx_parent_product = wc_get_product( $product->get_parent_id() );
                                                        if ( $alertx_parent_product ) {
                                                            echo esc_html( $alertx_parent_product->get_name() );
                                                        } else {
                                                            echo esc_html( $product->get_name() );
                                                        }
                                                        ?>
                                                    </a>
                                                </div>

                                                <?php
                                                    // Only show attribute summary for product variations
                                                    if ( $product->is_type( 'variation' ) && method_exists( $product, 'get_attribute_summary' ) ) {
                                                        $alertx_attribute_summary = $product->get_attribute_summary();
                                                        ?>
                                                        <div class="pvariant">
                                                            <?php echo esc_html( $alertx_attribute_summary ); ?>
                                                        </div>
                                                        <?php
                                                    }
                                                ?>
                                            </div>
                                        <?php else : ?>
                                            <?php esc_html_e( 'Product not found', 'alertx' ); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="mailto:<?php echo esc_html( $notification->email ); ?>"><?php echo esc_html( $notification->email ); ?></a>
                                    </td>
                                    <td>
                                        <?php echo esc_html( wp_date( 'd M, Y - h:i A', strtotime( $notification->date_added ) ) ); ?>
                                    </td>
                                    <td>
                                        <span class="status-pill subscription-status <?php echo esc_attr( $alertx_status_class ); ?>">
                                            <?php echo esc_html( $status ); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5" class="no-info">
                                    <?php esc_html_e( 'No notifications found!', 'alertx' ); ?>
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
                        'prev_text' => __( '&laquo; Previous', 'alertx'),
                        'next_text' => __( 'Next &raquo;', 'alertx'),
                        'total' => ceil( $total_notifications / $alertx_items_per_page ),
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
