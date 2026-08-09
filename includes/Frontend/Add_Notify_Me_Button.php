<?php

namespace AlertX\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Shortcode handler class for adding a "Notify Me" button on WooCommerce product pages.
 */
class Add_Notify_Me_Button {

    /**
     * Tracks whether the notify UI has been rendered to avoid duplicates.
     *
     * @var bool
     */
    private $rendered = false;

    /**
     * Initializes the class by hooking into WooCommerce single product summary.
     */
    public function __construct() {
        // Primary hook within the product summary
        add_action( 'woocommerce_single_product_summary', array( $this, 'add_notify_me_button' ), 30 );

        // Fallback hooks for themes that customize/remove the summary hook
        add_action( 'woocommerce_after_add_to_cart_form', array( $this, 'add_notify_me_button_fallback' ), 10 );
        add_action( 'woocommerce_after_single_product_summary', array( $this, 'add_notify_me_button_fallback' ), 10 );
        add_action( 'woocommerce_after_variations_form', array( $this, 'add_notify_me_button_fallback' ), 10 );

        // Last-resort fallback: render in footer on single product pages
        add_action( 'wp_footer', array( $this, 'add_notify_me_button_footer_fallback' ), 5 );
    }

    /**
     * Outputs the "Notify Me" button if the product is out of stock.
     *
     * @return void
     */
    public function add_notify_me_button() {
        global $product;

        // Check if the global product object is available
        if ( empty( $product ) || ! is_a( $product, 'WC_Product' ) ) {
            return; // Exit if product is not valid
        }

        // Variable product: render notify UI but let JS decide visibility per selected variation
        if ( $product->is_type( 'variable' ) ) {
            echo '<div class="notify-me-button-wrap" style="display:none;">';
                echo '<button class="button" id="notify-me-button">' . esc_html__('Notify Me When Available', 'alertx') . '</button>';
                echo '<div id="notify-me-form" style="display: none;">
                        <div class="form-fields">
                            <input type="email" id="notify-email" placeholder="' . esc_attr__( 'Enter your email', 'alertx' ) . '" required>
                            <input type="hidden" id="notify-product-id" value="">'
                            . '<button id="submit-notify">' . esc_html__( 'Notify Me', 'alertx' ) . '</button>' .
                        '</div>
                    </div>';
            echo '</div>';
            $this->rendered = true;
        } else {
            // Simple product: only show the button if the product is out of stock
            if ( ! $product->is_in_stock() ) {
                echo '<div class="notify-me-button-wrap">';
                    echo '<button class="button" id="notify-me-button">' . esc_html__('Notify Me When Available', 'alertx') . '</button>';
                    echo '<div id="notify-me-form" style="display: none;">
                            <div class="form-fields">
                                <input type="email" id="notify-email" placeholder="' . esc_attr__( 'Enter your email', 'alertx' ) . '" required>
                                <input type="hidden" id="notify-product-id" value="' . esc_attr( $product->get_id() ) . '">
                                <button id="submit-notify">' . esc_html__( 'Notify Me', 'alertx' ) . '</button>
                            </div>
                        </div>';
                echo '</div>';
                $this->rendered = true;
            }
        }
    }

    /**
     * Fallback renderer: calls main renderer only if not already rendered.
     */
    public function add_notify_me_button_fallback() {
        if ( ! $this->rendered ) {
            $this->add_notify_me_button();
        }
    }

    /**
     * Footer fallback: ensures the notify UI appears even if theme removes WooCommerce hooks.
     * Attempts to render once on single product pages.
     */
    public function add_notify_me_button_footer_fallback() {
        if ( $this->rendered ) {
            return;
        }

        if ( function_exists( 'is_product' ) && is_product() ) {
            $this->add_notify_me_button();
        }
    }
}
