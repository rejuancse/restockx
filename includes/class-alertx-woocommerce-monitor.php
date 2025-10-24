<?php
/**
 * WooCommerce Monitor Class
 *
 * Monitors WooCommerce stock levels
 *
 * @package AlertX
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class AlertX_WooCommerce_Monitor {

    /**
     * Check if WooCommerce is active
     *
     * @return bool True if WooCommerce is active
     */
    public static function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }

    /**
     * Get out of stock products
     *
     * @return array Out of stock products data
     */
    public static function get_out_of_stock_products() {
        if (!self::is_woocommerce_active()) {
            return array(
                'status' => 'inactive',
                'message' => __('WooCommerce is not active', 'alertx')
            );
        }

        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_stock_status',
                    'value' => 'outofstock'
                )
            )
        );

        $products = get_posts($args);
        $count = count($products);

        $product_list = array();
        foreach ($products as $product) {
            $product_list[] = array(
                'id' => $product->ID,
                'title' => $product->post_title,
                'edit_link' => get_edit_post_link($product->ID)
            );
        }

        return array(
            'status' => $count > 0 ? 'warning' : 'good',
            'count' => $count,
            'products' => array_slice($product_list, 0, 10), // Show max 10
            'message' => $count > 0 ?
                sprintf(_n('%d product is out of stock', '%d products are out of stock', $count, 'alertx'), $count) :
                __('All products are in stock', 'alertx')
        );
    }

    /**
     * Get low stock products
     *
     * @return array Low stock products data
     */
    public static function get_low_stock_products() {
        if (!self::is_woocommerce_active()) {
            return array(
                'status' => 'inactive',
                'message' => __('WooCommerce is not active', 'alertx')
            );
        }

        $settings = get_option('alertx_settings');
        $threshold = isset($settings['low_stock_threshold']) ? (int)$settings['low_stock_threshold'] : 5;

        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_stock_status',
                    'value' => 'instock'
                ),
                array(
                    'key' => '_manage_stock',
                    'value' => 'yes'
                ),
                array(
                    'key' => '_stock',
                    'value' => $threshold,
                    'compare' => '<=',
                    'type' => 'NUMERIC'
                )
            )
        );

        $products = get_posts($args);
        $count = count($products);

        $product_list = array();
        foreach ($products as $product) {
            $stock = get_post_meta($product->ID, '_stock', true);
            $product_list[] = array(
                'id' => $product->ID,
                'title' => $product->post_title,
                'stock' => $stock,
                'edit_link' => get_edit_post_link($product->ID)
            );
        }

        return array(
            'status' => $count > 0 ? 'warning' : 'good',
            'count' => $count,
            'threshold' => $threshold,
            'products' => array_slice($product_list, 0, 10), // Show max 10
            'message' => $count > 0 ?
                sprintf(_n('%d product has low stock', '%d products have low stock', $count, 'alertx'), $count) :
                __('No products with low stock', 'alertx')
        );
    }

    /**
     * Get all WooCommerce data
     *
     * @return array All WooCommerce monitoring data
     */
    public static function get_all_data() {
        if (!self::is_woocommerce_active()) {
            return array(
                'active' => false,
                'message' => __('WooCommerce is not installed or activated', 'alertx')
            );
        }

        return array(
            'active' => true,
            'out_of_stock' => self::get_out_of_stock_products(),
            'low_stock' => self::get_low_stock_products()
        );
    }
}
