<?php

defined( 'ABSPATH' ) || exit;

/**
 * Admin page template for stock notifications
 *
 * Variables passed from Alertx_Menu::admin_page():
 * @var int $total_notifications Total number of notifications
 * @var array $notifications Array of notification objects
 * @var int $items_per_page Number of items per page
 */
?>

<div id="alertx" class="alertx main wrap stock-notifications">
    <?php include_once ALERTX_PATH .'/views/global/header.php'; ?>

    <main class="page">
        <section class="kpi-row">
            <div class="kpi-card mini-stat">
                <div class="kpi-icon c1">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8">
                        <path d="M22 2 11 13"></path>
                        <path d="m22 2-7 20-4-9-9-4Z"></path>
                    </svg>
                </div>

                <div class="mini-stat-content">
                    <div class="val" data-count="12847">1,198</div>
                    <div class="kpi-bottom">
                        <div class="lbl">Sent today</div>
                    </div>
                </div>
            </div>

            <div class="kpi-card mini-stat">
                <div class="kpi-icon c1">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M12 7v5l3 2"></path>
                    </svg>
                </div>

                <div class="mini-stat-content">
                    <div class="val" data-count="12847">862</div>
                    <div class="kpi-bottom">
                        <div class="lbl">Queued</div>
                    </div>
                </div>
            </div>

            <div class="kpi-card mini-stat">
                <div class="kpi-icon c3">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                        <path d="M21 12a9 9 0 1 1-2.64-6.36"></path>
                        <path d="M21 3v6h-6"></path>
                    </svg>
                </div>

                <div class="mini-stat-content">
                    <div class="val" data-count="12847">31</div>
                    <div class="kpi-bottom">
                        <div class="lbl">Retrying</div>
                    </div>
                </div>
            </div>

            <div class="kpi-card mini-stat">
                <div class="kpi-icon c4">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="m15 9-6 6"></path>
                        <path d="m9 9 6 6"></path>
                    </svg>
                </div>

                <div class="mini-stat-content">
                    <div class="val" data-count="12847">11</div>
                    <div class="kpi-bottom">
                        <div class="lbl">Failed permanently</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="page section" id="page-notifications">
            <form id="bulk-action-form" method="post">
                <!-- Notifications List -->
                <div class="notifications-list-tablenav">
                    <?php wp_nonce_field( 'bulk_action', 'bulk_action_nonce' ); ?>

                    <div class="section-head">
                        <div class="bulk-actions">
                            <select name="bulk_action" id="bulk-action-selector">
                                <option value=""><?php esc_html_e( 'Bulk Actions', 'alertx' ); ?></option>
                                <option value="delete"><?php esc_html_e( 'Delete', 'alertx' ); ?></option>
                            </select>
                            <input type="submit" name="submit_bulk_action" class="button action" value="<?php esc_attr_e( 'Apply', 'alertx' ); ?>">
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
                                <?php wp_nonce_field( 'stock_notification_export', 'stock_notification_export_nonce' ); ?>
                                <input type="submit" name="export_csv" class="button button-primary" value="<?php esc_attr_e( 'Export to CSV', 'alertx' ); ?>">
                            </div>
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
                                    $date = $notification->date_added;
                                    $formatted_date = wp_date(
                                                        'd F, Y - h:i A',
                                                        strtotime($date)
                                                    );

                                ?>
                                <tr>
                                    <th scope="row" class="check-column">
                                        <input type="checkbox" name="notifications[]" value="<?php echo esc_attr( $notification->id ); ?>" />
                                    </th>
                                    <td>
                                        <a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>">
                                            <?php
                                            $image_id = $product->get_image_id(); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                                            $image_url = wp_get_attachment_image_url( $image_id, 'thumbnail' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                                            if ($image_url) {
                                                echo '<img src="' . esc_url( $image_url ) . '"
                                                alt="' . esc_attr( $product->get_name() ) . '" width="44" height="44" />';
                                            }
                                            echo $product ? esc_html( $product->get_name() ) : esc_html__( 'Product not found', 'alertx' );
                                            ?>
                                        </a>
                                    </td>
                                    <td><?php echo esc_html( $notification->email ); ?></td>
                                    <td><?php echo esc_html( $formatted_date ); ?></td>
                                    <td class="subscribed"><?php esc_html_e( 'Subscribed', 'alertx' ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5" class="no-info"><?php esc_html_e( 'No notifications found.', 'alertx' ); ?></td>
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
                        'total' => ceil( $total_notifications / $items_per_page ),
                        'current' => $paged,
                    );

                    if ( $pagination_args['total'] > 1 ) {
                        echo '<div class="pagination-container">';
                            echo wp_kses_post( paginate_links( $pagination_args ) );
                        echo '</div>';
                    }
                ?>
            </form>
        </section>
    </main>
</div>
