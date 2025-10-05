<?php

namespace Alertx\Admin;

use WP_List_Table;

/**
 * Custom WP_List_Table implementation for managing unused media attachments.
 */
class Unused_Media_List extends WP_List_Table {

    /**
     * The search term used to filter media items.
     *
     * @var string
     */
    public $search;

    /**
     * Author ID for filtering unused media attachments.
     *
     * @var int
     */
    public $author_id;

    /**
     * Constructor method to initialize the list table.
     *
     * @param string   $search    The search term for filtering media.
     * @param int|null $author_id Optional. Author ID to filter media by author.
     */
    public function __construct( $search, $author_id = null ) {
        parent::__construct( array(
            'singular' => 'media',
            'plural'   => 'media',
            'ajax'     => false,
         ) );

        $this->search    = $search;
        $this->author_id = $author_id;
        $this->process_bulk_action();
    }

    /**
     * Define the columns that should be displayed in the table.
     *
     * @return array
     */
    public function get_columns() {
        return array(
            'cb'          => '<input type="checkbox" />',
            'post_title'  => __( 'File', 'alertx' ),
            'post_author' => __( 'Author', 'alertx' ),
            'size'        => __( 'Size', 'alertx' ),
            'post_date'   => __( 'Date', 'alertx' ),
        );
    }

    /**
     * Render default column output.
     *
     * @param object $item        The current item being displayed.
     * @param string $column_name The name of the column.
     * @return string HTML output for the column.
     */
    protected function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'post_title':
                $edit_link     = get_edit_post_link( $item->ID );
                $delete_link   = get_delete_post_link( $item->ID, '', true );
                $view_link     = wp_get_attachment_url( $item->ID );
                $thumbnail     = wp_get_attachment_image( $item->ID, [ 60, 60 ], true );
                $file_path     = get_attached_file( $item->ID );
                $file_name     = $item->post_title;
                $file_extension = $file_path ? pathinfo( $file_path, PATHINFO_EXTENSION ) : '';
                $full_file_name = $file_name . '.' . $file_extension;

                // Define row actions
                $actions = [
                    'edit'    => '<span class="edit"><a href="' . esc_url( $edit_link ) . '">' . __( 'Edit', 'alertx' ) . '</a></span>',
                    'view'    => '<span class="view"><a href="' . esc_url( $view_link ) . '">' . __( 'View', 'alertx' ) . '</a></span>',
                    'delete'  => '<span class="delete"><a href="' . esc_url( $delete_link ) . '" class="submitdelete">' . __( 'Delete Permanently', 'alertx' ) . '</a></span>'
                ];

                /* translators: %s: post title */
                $aria_label = sprintf( __( '"%s" (Edit)', 'alertx' ), $item->post_title );

                $output = '<strong class="has-media-icon">
                    <a href="' . esc_url( $edit_link ) . '" aria-label="' . esc_attr( $aria_label ) . '">
                        <span class="media-icon image-icon">' . $thumbnail . '</span>
                        ' . esc_html( $file_name ) . '
                    </a>
                </strong>
                <p class="filename">
                    <span class="screen-reader-text">' . __( 'File name:', 'alertx' ) . '</span>
                    ' . esc_html( $full_file_name ) . '
                </p>';

                // Append row actions
                $output .= $this->row_actions( $actions );
                return $output;
            case 'post_author':
                $author_name = get_the_author_meta( 'display_name', $item->post_author );
                $author_url  = add_query_arg(
                    [
                        'page'   => 'unused-media-cleaner',
                        'author' => $item->post_author,
                    ],
                    admin_url( 'upload.php' )
                );
                return '<a href="' . esc_url( $author_url ) . '">' . esc_html( $author_name ) . '</a>';
            case 'size':
                $file_path = get_attached_file( $item->ID );
                if ( $file_path && file_exists( $file_path ) ) {
                    return size_format( filesize( $file_path ) );
                } else {
                    return '-'; // Return a placeholder or handle the case when the file is missing.
                }
            case 'post_date':
                return date_i18n( 'Y/m/d', strtotime( $item->post_date ) );
            default:
                return print_r( $item, true );
        }
    }

    /**
     * Define sortable columns for the table.
     *
     * @return array
     */
    protected function get_sortable_columns() {
        return array(
            'post_title'  => array( 'post_title', false ),
            'post_author' => array( 'post_author', false ),
            'post_date'   => array( 'post_date', false ),
            'size'        => array( 'size', false ),
        );
    }

    /**
     * Get number of items to display per page.
     *
     * @param string $option  Name of the option to retrieve from user meta.
     * @param int    $default Default number of items per page.
     * @return int Number of items per page.
     */
    protected function get_items_per_page( $option, $default = 20 ) {
        $per_page = (int) get_user_meta( get_current_user_id(), $option, true );
        return empty( $per_page ) || $per_page < 1 ? $default : $per_page;
    }

    /**
     * Get all used media IDs with optimized queries to prevent timeouts
     * Now includes ACF (Advanced Custom Fields) support
     *
     * @return array Array of used media IDs
     */
    private function get_used_media_ids() {
        global $wpdb;

        $used_image_ids = [];

        // Increase memory limit and execution time
        if ( function_exists( 'ini_set' ) ) {
            ini_set( 'memory_limit', '512M' );
            set_time_limit( 300 );
        }

        // 1. Get featured images (post thumbnails) - Most common usage
        $featured_images = $wpdb->get_col("
            SELECT DISTINCT meta_value
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_thumbnail_id'
            AND meta_value != '0'
            AND meta_value != ''
        ");
        if ( $featured_images ) {
            $used_image_ids = array_merge( $used_image_ids, array_map( 'intval', $featured_images ) );
        }

        // 2. Get WooCommerce product gallery images
        if ( class_exists( 'WooCommerce' ) ) {
            $gallery_images = $wpdb->get_col("
                SELECT DISTINCT meta_value
                FROM {$wpdb->postmeta}
                WHERE meta_key = '_product_image_gallery'
                AND meta_value != ''
                AND meta_value IS NOT NULL
            ");

            foreach ( $gallery_images as $gallery_string ) {
                if ( ! empty( $gallery_string ) ) {
                    $gallery_ids = explode( ',', $gallery_string );
                    $used_image_ids = array_merge( $used_image_ids, array_map( 'intval', array_filter( $gallery_ids ) ) );
                }
            }

            // Get WooCommerce variation images
            $variation_images = $wpdb->get_col("
                SELECT DISTINCT meta_value
                FROM {$wpdb->postmeta}
                WHERE meta_key = '_thumbnail_id'
                AND post_id IN (
                    SELECT ID FROM {$wpdb->posts}
                    WHERE post_type = 'product_variation'
                )
                AND meta_value != '0'
                AND meta_value != ''
            ");
            if ( $variation_images ) {
                $used_image_ids = array_merge( $used_image_ids, array_map( 'intval', $variation_images ) );
            }
        }

        // 3. ACF (Advanced Custom Fields) support
        if ( class_exists( 'ACF' ) ) {
            // Get all ACF field values that might contain media IDs
            $acf_meta_values = $wpdb->get_col("
                SELECT DISTINCT pm.meta_value
                FROM {$wpdb->postmeta} pm
                JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                WHERE p.post_status = 'publish'
                AND pm.meta_key NOT LIKE '_%'
                AND (
                    pm.meta_value REGEXP '^[0-9]+$'
                    OR pm.meta_value LIKE '%\"[0-9]+\"%'
                    OR pm.meta_value LIKE '%i:[0-9]+;%'
                    OR pm.meta_value LIKE '%,[0-9]+,%'
                )
            ");

            foreach ( $acf_meta_values as $meta_value ) {
                if ( empty( $meta_value ) ) {
                    continue;
                }

                // Case 1: Simple numeric value (single image field)
                if ( is_numeric( $meta_value ) && $meta_value > 0 ) {
                    $used_image_ids[] = intval( $meta_value );
                    continue;
                }

                // Case 2: Serialized data (arrays, gallery fields)
                if ( is_serialized( $meta_value ) ) {
                    $unserialized = @unserialize( $meta_value );
                    if ( is_array( $unserialized ) ) {
                        // Extract numeric values that could be attachment IDs
                        array_walk_recursive( $unserialized, function( $item ) use ( &$used_image_ids ) {
                            if ( is_numeric( $item ) && $item > 0 && $item < 999999999 ) {
                                $used_image_ids[] = intval( $item );
                            }
                        });
                    }
                    continue;
                }

                // Case 3: JSON data
                if ( strpos( $meta_value, '"' ) !== false ) {
                    $json_decoded = json_decode( $meta_value, true );
                    if ( is_array( $json_decoded ) ) {
                        array_walk_recursive( $json_decoded, function( $item ) use ( &$used_image_ids ) {
                            if ( is_numeric( $item ) && $item > 0 && $item < 999999999 ) {
                                $used_image_ids[] = intval( $item );
                            }
                        });
                        continue;
                    }
                }

                // Case 4: Comma separated values (gallery fields)
                if ( strpos( $meta_value, ',' ) !== false ) {
                    $comma_values = explode( ',', $meta_value );
                    foreach ( $comma_values as $value ) {
                        $value = trim( $value );
                        if ( is_numeric( $value ) && $value > 0 ) {
                            $used_image_ids[] = intval( $value );
                        }
                    }
                }

                // Case 5: Extract IDs from serialized format patterns
                if ( preg_match_all( '/i:(\d+);/', $meta_value, $matches ) ) {
                    foreach ( $matches[1] as $id ) {
                        if ( $id > 0 ) {
                            $used_image_ids[] = intval( $id );
                        }
                    }
                }

                // Case 6: Extract IDs from JSON format patterns
                if ( preg_match_all( '/"(\d+)"/', $meta_value, $matches ) ) {
                    foreach ( $matches[1] as $id ) {
                        if ( $id > 0 && strlen( $id ) <= 10 ) { // Reasonable ID length check
                            $used_image_ids[] = intval( $id );
                        }
                    }
                }
            }

            // Additional ACF specific query for relationship and gallery fields
            $acf_specific_query = $wpdb->get_col("
                SELECT DISTINCT pm.meta_value
                FROM {$wpdb->postmeta} pm
                JOIN {$wpdb->postmeta} pm2 ON pm.post_id = pm2.post_id
                AND pm2.meta_key = CONCAT('_', pm.meta_key)
                JOIN {$wpdb->posts} acf ON pm2.meta_value = acf.post_name
                AND acf.post_type = 'acf-field'
                JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                WHERE p.post_status = 'publish'
                AND (
                    acf.post_content LIKE '%\"type\";s:5:\"image\"%'
                    OR acf.post_content LIKE '%\"type\";s:7:\"gallery\"%'
                    OR acf.post_content LIKE '%\"type\";s:4:\"file\"%'
                )
            ");

            foreach ( $acf_specific_query as $acf_value ) {
                if ( empty( $acf_value ) ) {
                    continue;
                }

                // Process the same way as above
                if ( is_numeric( $acf_value ) && $acf_value > 0 ) {
                    $used_image_ids[] = intval( $acf_value );
                } else {
                    // Handle serialized/JSON data for ACF specific fields
                    if ( is_serialized( $acf_value ) ) {
                        $unserialized = @unserialize( $acf_value );
                        if ( is_array( $unserialized ) ) {
                            array_walk_recursive( $unserialized, function( $item ) use ( &$used_image_ids ) {
                                if ( is_numeric( $item ) && $item > 0 && $item < 999999999 ) {
                                    $used_image_ids[] = intval( $item );
                                }
                            });
                        }
                    }
                }
            }
        }

        // 4. Get images used in post content (process in batches to avoid memory issues)
        $batch_size = 100;
        $offset = 0;

        while ( true ) {
            $posts_with_content = $wpdb->get_results( $wpdb->prepare("
                SELECT ID, post_content
                FROM {$wpdb->posts}
                WHERE post_content LIKE %s
                AND post_status = 'publish'
                LIMIT %d OFFSET %d
            ", '%wp-image-%', $batch_size, $offset ) );

            if ( empty( $posts_with_content ) ) {
                break;
            }

            foreach ( $posts_with_content as $post ) {
                preg_match_all( '/wp-image-(\d+)/', $post->post_content, $matches );
                if ( ! empty( $matches[1] ) ) {
                    $used_image_ids = array_merge( $used_image_ids, array_map( 'intval', $matches[1] ) );
                }
            }

            $offset += $batch_size;
        }

        // 5. Get Elementor images (process in smaller batches)
        $elementor_posts = $wpdb->get_col("
            SELECT post_id
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_elementor_data'
            LIMIT 50
        ");

        foreach ( $elementor_posts as $post_id ) {
            $elementor_data = get_post_meta( $post_id, '_elementor_data', true );

            if ( $elementor_data ) {
                $elementor_data = json_decode( $elementor_data, true );

                if ( is_array( $elementor_data ) ) {
                    array_walk_recursive( $elementor_data, function( $item, $key ) use ( &$used_image_ids ) {
                        if ( $key === 'id' && is_numeric( $item ) ) {
                            $used_image_ids[] = intval( $item );
                        }
                    } );
                }
            }
        }

        // 6. Get Site Icon ID
        $site_icon_id = get_option( 'site_icon' );
        if ( $site_icon_id ) {
            $used_image_ids[] = intval( $site_icon_id );
        }

        // 7. Get custom logo
        $custom_logo_id = get_theme_mod( 'custom_logo' );
        if ( $custom_logo_id ) {
            $used_image_ids[] = intval( $custom_logo_id );
        }

        // 8. Get header image
        $header_image_id = get_theme_mod( 'header_image_data' );
        if ( is_array( $header_image_id ) && isset( $header_image_id['attachment_id'] ) ) {
            $used_image_ids[] = intval( $header_image_id['attachment_id'] );
        }

        // 9. Get background image
        $background_image_id = get_theme_mod( 'background_image_thumb' );
        if ( $background_image_id ) {
            $used_image_ids[] = intval( $background_image_id );
        }

        // Remove duplicates and invalid IDs, also filter out IDs that are too large to be realistic
        $used_image_ids = array_unique( array_filter( array_map( 'intval', $used_image_ids ), function( $id ) {
            return $id > 0 && $id < 999999999;
        }));

        return $used_image_ids;
    }

    public function prepare_items() {
        global $wpdb;

        // Display delete message
        $this->display_delete_message();

        // Retrieve search term from request.
        $search = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';

        // Determine pagination information.
        $per_page     = $this->get_items_per_page( 'unused_media_cleaner_per_page', 20 );
        $current_page = $this->get_pagenum();
        $offset       = ( $current_page - 1 ) * $per_page;

        // Check for force refresh parameter
        $force_refresh = isset( $_GET['refresh_cache'] ) && $_GET['refresh_cache'] === '1';

        // Check if the results are cached
        $cache_key = 'unused_media_list_v2_' . md5( serialize( array( $search, $this->author_id, $current_page, $per_page ) ) );

        // Clear cache if forced or if there's recent activity
        if ( $force_refresh || $this->should_invalidate_cache() ) {
            delete_transient( $cache_key );
        }

        $cached_results = get_transient( $cache_key );

        if ( false === $cached_results || $force_refresh ) {
            // Get used media IDs with optimized method
            $used_image_ids = $this->get_used_media_ids();

            // Build optimized query for unused attachments
            $where_conditions = [
                "p.post_type = 'attachment'",
                "p.post_status = 'inherit'"
            ];

            // Exclude used image IDs with better performance
            if ( ! empty( $used_image_ids ) ) {
                $used_image_ids_placeholder = implode( ',', array_map( 'intval', $used_image_ids ) );
                $where_conditions[] = "p.ID NOT IN ($used_image_ids_placeholder)";
            }

            // Filter by author ID if provided
            if ( $this->author_id ) {
                $where_conditions[] = $wpdb->prepare( 'p.post_author = %d', $this->author_id );
            }

            // If there is a search query, add the search condition
            if ( $search ) {
                $where_conditions[] = $wpdb->prepare( 'p.post_title LIKE %s', '%' . $wpdb->esc_like( $search ) . '%' );
            }

            $where_clause = 'WHERE ' . implode( ' AND ', $where_conditions );

            // Count query first
            $count_query = "
                SELECT COUNT(*)
                FROM {$wpdb->posts} p
                $where_clause
            ";
            $total_items = $wpdb->get_var( $count_query );

            // Main query with pagination
            $query = "
                SELECT p.ID, p.post_title, p.guid, p.post_author, p.post_date
                FROM {$wpdb->posts} p
                $where_clause
                ORDER BY p.post_date DESC
                LIMIT $offset, $per_page
            ";

            // Retrieve items based on the constructed query
            $this->items = $wpdb->get_results( $query );

            // Cache the results for shorter time (10 seconds) with smart invalidation
            set_transient( $cache_key, array( 'items' => $this->items, 'total_items' => $total_items ), 10 ); // 10 seconds

            // Update last cache time
            update_option( 'unused_media_last_cache_time', time() );
        } else {
            // Use cached results
            $this->items = $cached_results['items'];
            $total_items = $cached_results['total_items'];
        }

        // Define column headers, hidden columns, and sortable columns
        $columns               = $this->get_columns();
        $hidden                = [];
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = [ $columns, $hidden, $sortable ];

        // Set pagination arguments for display
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_items / $per_page ),
         ) );
    }

    /**
     * Check if cache should be invalidated based on recent media activity
     *
     * @return bool True if cache should be cleared
     */
    private function should_invalidate_cache() {
        global $wpdb;

        // Get the last time cache was created
        $last_cache_time = get_option( 'unused_media_last_cache_time', 0 );

        // Check if any media was added/modified since last cache
        $recent_media_activity = $wpdb->get_var( $wpdb->prepare("
            SELECT COUNT(*)
            FROM {$wpdb->posts}
            WHERE post_type = 'attachment'
            AND post_modified_gmt > %s
        ", date( 'Y-m-d H:i:s', $last_cache_time ) ) );

        // Check if any posts were modified (which might affect image usage)
        $recent_post_activity = $wpdb->get_var( $wpdb->prepare("
            SELECT COUNT(*)
            FROM {$wpdb->posts}
            WHERE post_type IN ('post', 'page', 'product')
            AND post_modified_gmt > %s
        ", date( 'Y-m-d H:i:s', $last_cache_time ) ) );

        return ( $recent_media_activity > 0 || $recent_post_activity > 0 );
    }

    /**
     * Force clear all cache manually
     */
    public function force_clear_cache() {
        $this->clear_cache();
        delete_option( 'unused_media_last_cache_time' );
    }

    /**
     * Get total number of items, optionally filtered by search term.
     * This method now supports real-time counting for display purposes.
     *
     * @param string $search Optional. Search term to filter items.
     * @param bool   $force_fresh Optional. Force fresh calculation without cache.
     * @return int Total number of items.
     */
    public function get_total_items( $search = '', $force_fresh = false ) {
        global $wpdb;

        // Check for force refresh parameter or force_fresh flag
        $force_refresh = ( isset( $_GET['refresh_cache'] ) && $_GET['refresh_cache'] === '1' ) || $force_fresh;

        $cache_key = 'unused_media_total_' . md5( serialize( array( $search, $this->author_id ) ) );

        // Clear cache if forced or if there's recent activity
        if ( $force_refresh || $this->should_invalidate_cache() ) {
            delete_transient( $cache_key );
        }

        $cached_total = get_transient( $cache_key );

        if ( false !== $cached_total && ! $force_refresh ) {
            return $cached_total;
        }

        // Get used media IDs
        $used_image_ids = $this->get_used_media_ids();

        // Build the optimized count query
        $where_conditions = [
            "p.post_type = 'attachment'",
            "p.post_status = 'inherit'"
        ];

        // Exclude used image IDs
        if ( ! empty( $used_image_ids ) ) {
            $used_image_ids_placeholder = implode( ',', array_map( 'intval', $used_image_ids ) );
            $where_conditions[] = "p.ID NOT IN ($used_image_ids_placeholder)";
        }

        // Filter by author ID if provided
        if ( $this->author_id ) {
            $where_conditions[] = $wpdb->prepare( 'p.post_author = %d', $this->author_id );
        }

        // If there is a search query, add the search condition
        if ( $search ) {
            $where_conditions[] = $wpdb->prepare( 'p.post_title LIKE %s', '%' . $wpdb->esc_like( $search ) . '%' );
        }

        $where_clause = 'WHERE ' . implode( ' AND ', $where_conditions );

        $query = "
            SELECT COUNT(*)
            FROM {$wpdb->posts} p
            $where_clause
        ";

        $total_items = $wpdb->get_var( $query );

        // Cache for shorter time (10 seconds as you wanted) but with smart invalidation
        set_transient( $cache_key, $total_items, 10 ); // 10 seconds

        // Update last cache time
        update_option( 'unused_media_last_cache_time', time() );

        return $total_items;
    }

    /**
     * Get fresh total count without any caching - for display purposes
     *
     * @param string $search Optional. Search term to filter items.
     * @return int Total number of items.
     */
    public function get_fresh_total_items( $search = '' ) {
        return $this->get_total_items( $search, true );
    }

    /**
     * Render the checkbox column for bulk actions.
     *
     * @param object $item The current item being displayed.
     * @return string HTML output for the checkbox column.
     */
    protected function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="media[]" value="%s" />', $item->ID
        );
    }

    /**
     * Define bulk actions for the table.
     *
     * @return array Associative array of bulk actions.
     */
    protected function get_bulk_actions() {
        return [
            'delete' => __( 'Delete permanently', 'alertx' ),
        ];
    }

    /**
     * Process the bulk action when a form is submitted.
     */
    protected function process_bulk_action() {
        if ( 'delete' === $this->current_action() ) {
            $nonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])) : '';

            if ( ! wp_verify_nonce( $nonce, 'bulk-media' ) ) {
                die( 'Security check failed' );
            } else {
                $media_ids = isset( $_REQUEST['media'] ) ? array_map( 'absint', (array) $_REQUEST['media'] ) : [];

                if ( ! empty( $media_ids ) ) {
                    foreach ( $media_ids as $media_id ) {
                        // Delete the attachment
                        wp_delete_attachment( $media_id, true );
                    }

                    // Clear cache after deletion
                    $this->clear_cache();

                    // Set a transient to display a success message
                    $deleted_count = count( $media_ids );

                    /* translators: %d: number of deleted media files */
                    set_transient( 'unused_media_delete_message', sprintf( __( '%d media file(s) deleted successfully.', 'alertx' ), $deleted_count ), 30 );
                }
            }
        }
    }

    /**
     * Clear all plugin-related cache
     */
    private function clear_cache() {
        global $wpdb;

        // Delete all transients related to this plugin
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_unused_media_%'" );
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_unused_media_%'" );

        // Also clear the last cache time option
        delete_option( 'unused_media_last_cache_time' );
    }

    /**
     * Display the success message if a media file was deleted.
     */
    public function display_delete_message() {
        if ( $message = get_transient( 'unused_media_delete_message' ) ) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
            delete_transient( 'unused_media_delete_message' );
        }
    }

    /**
     * Display the table.
     */
    public function display() {
        wp_nonce_field( 'bulk-media' );
        parent::display();
    }
}
