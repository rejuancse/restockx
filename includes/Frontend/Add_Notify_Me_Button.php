<?php

namespace RestockX\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Shortcode handler class for adding a "Notify Me" button on WooCommerce product pages.
 */
class Add_Notify_Me_Button {

    /**
     * Option name used to store the notify button settings.
     *
     * @var string
     */
    const OPTION_KEY = 'restockx_notify_me_settings';

    /**
     * Tracks whether the notify UI has been rendered to avoid duplicates.
     *
     * @var bool
     */
    private $rendered = false;

    /**
     * Default values for the notify button settings.
     *
     * @return array
     */
    public static function get_defaults() {
        return array(
            'button_text'    => __( 'Notify Me When Available', 'restockx' ),
            'tooltip_text'   => __( 'We will email you as soon as this product is back in stock.', 'restockx' ),
            'text_color'     => '#ffffff',
            'bg_color'       => '#3c06c5',
            'hover_bg_color' => '#2a048a',
            'font_family'    => 'inherit',
            'font_size'      => 16,
            'icon'           => 'none',
            'icon_position'  => 'before',
            'padding_top'    => 10,
            'padding_right'  => 20,
            'padding_bottom' => 10,
            'padding_left'   => 20,
            'margin_top'     => 10,
            'margin_right'   => 0,
            'margin_bottom'  => 20,
            'margin_left'    => 0,
            'border_width'   => 0,
            'border_color'   => '#3c06c5',
            'border_radius'  => 4,
        );
    }

    /**
     * Retrieves the saved notify button settings merged with defaults.
     *
     * @return array
     */
    public static function get_settings() {
        $saved = get_option( self::OPTION_KEY, array() );
        if ( ! is_array( $saved ) ) {
            $saved = array();
        }

        $settings = wp_parse_args( $saved, self::get_defaults() );

        // Font family must be a safe CSS value, allow only known stacks.
        $allowed_fonts = self::get_allowed_font_families();
        if ( ! isset( $allowed_fonts[ $settings['font_family'] ] ) && ! in_array( $settings['font_family'], $allowed_fonts, true ) ) {
            $settings['font_family'] = 'inherit';
        }

        return $settings;
    }

    /**
     * Whitelisted font family stacks.
     *
     * @return array
     */
    public static function get_allowed_font_families() {
        return array(
            'inherit'                        => 'Theme default',
            'DM Sans,sans-serif'             => 'DM Sans',
            'system-ui, sans-serif'          => 'System UI',
            'Arial, sans-serif'              => 'Arial',
            'Helvetica, sans-serif'          => 'Helvetica',
            '"Segoe UI", sans-serif'         => 'Segoe UI',
            'Verdana, sans-serif'            => 'Verdana',
            '"Trebuchet MS", sans-serif'     => 'Trebuchet MS',
            'Georgia, serif'                 => 'Georgia',
            '"Times New Roman", serif'       => 'Times New Roman',
            '"Courier New", monospace'       => 'Courier New',
        );
    }

    /**
     * Returns the SVG markup for a given icon slug.
     *
     * @param string $icon Icon slug.
     * @return string SVG markup or empty string.
     */
    public static function get_icon_svg( $icon ) {
        $icons = array(
            'bell'  => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
            'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
            'mail'  => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
            'tag'   => '<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.83z"/><line x1="7" y1="7" x2="7.01" y2="7"/>',
        );

        if ( ! isset( $icons[ $icon ] ) ) {
            return '';
        }

        return '<span class="restockx-btn-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $icons[ $icon ] . '</svg></span>';
    }

    /**
     * Builds the icon markup for the button based on saved settings.
     *
     * @param array  $settings Notify button settings.
     * @param string $position Desired position (before|after).
     * @return string
     */
    private static function get_button_icon( $settings, $position ) {
        if ( 'none' === $settings['icon'] || $position !== $settings['icon_position'] ) {
            return '';
        }

        return self::get_icon_svg( $settings['icon'] );
    }

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

        // Check if already rendered to prevent duplicates
        if ( $this->rendered ) {
            return;
        }

        // Check if the global product object is available
        if ( empty( $product ) || ! is_a( $product, 'WC_Product' ) ) {
            return; // Exit if product is not valid
        }

        // Generate unique ID for this product instance
        $unique_id = $product->get_id();
        $is_logged_in = is_user_logged_in();
        $user_email = $is_logged_in ? wp_get_current_user()->user_email : '';

        // Create nonce for security
        $nonce = wp_create_nonce( 'restockx_notify_me_' . $unique_id );

        $button_settings = self::get_settings();

        // Variable product: render notify UI but let JS decide visibility per selected variation
        if ( $product->is_type( 'variable' ) ) {
            // Check if variable product is out of stock or all variations are out of stock
            $is_out_of_stock = false;
            $show_notify_button = false;

            // Check if the parent variable product is explicitly out of stock
            if ( ! $product->is_in_stock() ) {
                $is_out_of_stock = true;
                $show_notify_button = true;
            }

            // Get all variations to check if any are in stock
            $variations = $product->get_available_variations();
            $any_in_stock = false;

            foreach ( $variations as $variation ) {
                if ( isset( $variation['is_in_stock'] ) && $variation['is_in_stock'] ) {
                    $any_in_stock = true;
                    break;
                }
            }

            // If no variations are in stock, show the notify button
            if ( ! $any_in_stock && ! empty( $variations ) ) {
                $show_notify_button = true;
            }

            $wrapper_class = $show_notify_button ? 'notify-me-button-wrap' : 'notify-me-button-wrap notify-hidden';

            echo '<div class="' . esc_attr( $wrapper_class ) . '">';
                if ( $is_logged_in ) {
                    // For logged-in users, show the form directly
                    // Pre-fill product_id with parent ID if all variations are out of stock
                    $initial_product_id = $show_notify_button ? $unique_id : '';
                    echo '<div class="restockx-wrap">';
                        echo '<div class="restockx-notify-form" data-product-id="' . esc_attr( $unique_id ) . '">
                            <div class="form-fields">';
                                echo '<input type="hidden" class="restockx-notify-email" value="' . esc_attr( $user_email ) . '">';
                                echo '<input type="hidden" class="restockx-notify-product-id" value="' . esc_attr( $initial_product_id ) . '">';
                                echo '<input type="hidden" class="restockx-notify-parent-id" value="' . esc_attr( $unique_id ) . '">';
                                echo '<input type="hidden" class="restockx-notify-nonce" value="' . esc_attr( $nonce ) . '">';
                                echo '<button class="restockx-submit-notify">';
                                    echo self::get_button_icon( $button_settings, 'before' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup.
                                    echo '<span class="restockx-notify-button-text">' . esc_html( $button_settings['button_text'] ) . '</span>';
                                    echo self::get_button_icon( $button_settings, 'after' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup.
                                echo '</button>';

                                echo '<div class="restockx-tooltip tooltip"><span class="tooltip-mark">?</span><span class="tooltiptext">' . esc_html( $button_settings['tooltip_text'] ) . '</span></div>';
                            echo '</div>
                        </div>';
                    echo '</div>';
                } else {
                    echo '<div class="restockx-wrap">';
                        echo '<button class="button restockx-notify-button" data-product-id="' . esc_attr( $unique_id ) . '">';
                            echo self::get_button_icon( $button_settings, 'before' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup.
                            echo '<span class="restockx-notify-button-text">' . esc_html( $button_settings['button_text'] ) . '</span>';
                            echo self::get_button_icon( $button_settings, 'after' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup.
                        echo '</button>';

                        echo '<div class="restockx-tooltip tooltip"><span class="tooltip-mark">?</span><span class="tooltiptext">' . esc_html( $button_settings['tooltip_text'] ) . '</span></div>';
                    echo '</div>';


                    // Pre-fill product_id with parent ID if all variations are out of stock
                    $initial_product_id = $show_notify_button ? $unique_id : '';
                    echo '<div class="restockx-notify-form notify-hidden" data-product-id="' . esc_attr( $unique_id ) . '">
                            <div class="form-fields">';
                                echo '<input type="email" class="restockx-notify-email" placeholder="' . esc_attr__( 'Enter your email', 'restockx' ) . '" required>';
                                echo '<input type="hidden" class="restockx-notify-product-id" value="' . esc_attr( $initial_product_id ) . '">';
                                echo '<input type="hidden" class="restockx-notify-parent-id" value="' . esc_attr( $unique_id ) . '">';
                                echo '<input type="hidden" class="restockx-notify-nonce" value="' . esc_attr( $nonce ) . '">';
                                echo '<button class="restockx-submit-notify">' . esc_html__( 'Notify Me', 'restockx' ) . '</button>';
                            echo '</div>
                        </div>';
                }
            echo '</div>';
            $this->rendered = true;
        } else {
            // Simple product: only show the button if the product is out of stock
            if ( ! $product->is_in_stock() ) {
                echo '<div class="notify-me-button-wrap">';
                    if ( $is_logged_in ) {
                        // For logged-in users, show the form directly
                        echo '<div class="restockx-wrap">';
                            echo '<div class="restockx-notify-form" data-product-id="' . esc_attr( $unique_id ) . '">
                                <div class="form-fields">';
                                    echo '<input type="hidden" class="restockx-notify-email" value="' . esc_attr( $user_email ) . '">';
                                    echo '<input type="hidden" class="restockx-notify-product-id" value="' . esc_attr( $unique_id ) . '">';
                                    echo '<input type="hidden" class="restockx-notify-nonce" value="' . esc_attr( $nonce ) . '">';
                                    echo '<button class="button restockx-submit-notify">';
                                        echo self::get_button_icon( $button_settings, 'before' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup.
                                        echo '<span class="restockx-notify-button-text">' . esc_html( $button_settings['button_text'] ) . '</span>';
                                        echo self::get_button_icon( $button_settings, 'after' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup.
                                    echo '</button>';

                                    echo '<div class="restockx-tooltip tooltip"><span class="tooltip-mark">?</span><span class="tooltiptext">' . esc_html( $button_settings['tooltip_text'] ) . '</span></div>';
                                echo '</div>';
                            echo '</div>';
                        echo '</div>';
                    } else {
                        echo '<div class="restockx-wrap">';
                            echo '<button class="button restockx-notify-button" data-product-id="' . esc_attr( $unique_id ) . '">';
                                echo self::get_button_icon( $button_settings, 'before' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup.
                                echo '<span class="restockx-notify-button-text">' . esc_html( $button_settings['button_text'] ) . '</span>';
                                echo self::get_button_icon( $button_settings, 'after' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup.
                            echo '</button>';

                            echo '<div class="restockx-tooltip tooltip"><span class="tooltip-mark">?</span><span class="tooltiptext">' . esc_html( $button_settings['tooltip_text'] ) . '</span></div>';
                        echo '</div>';

                        echo '<div class="restockx-notify-form notify-hidden" data-product-id="' . esc_attr( $unique_id ) . '">
                                <div class="form-fields">';
                                    echo '<input type="email" class="restockx-notify-email" placeholder="' . esc_attr__( 'Enter your email', 'restockx' ) . '" required>';
                                    echo '<input type="hidden" class="restockx-notify-product-id" value="' . esc_attr( $unique_id ) . '">';
                                    echo '<input type="hidden" class="restockx-notify-nonce" value="' . esc_attr( $nonce ) . '">';
                                    echo '<button class="restockx-submit-notify">' . esc_html__( 'Notify Me', 'restockx' ) . '</button>';
                                echo '</div>';
                            echo '</div>';
                    }
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
