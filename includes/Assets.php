<?php
namespace AlertX;

// use AlertX\Helper;

/**
 * Handles assets for the admin interface
 */
class Assets {

	/**
     * Class constructor
     *
     * @since 1.0.0
     */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_script' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_script' ) );
	}

	/**
	 * Register necessary CSS and JS for admin
	 */
	public function admin_script( $hook_suffix ) {
		wp_enqueue_style( 'alertx-admin', ALERTX_URL .'/assets/dist/css/stock-admin.css', false, ALERTX_VERSION );
		wp_enqueue_style( 'alertx_admin', ALERTX_URL .'/assets/dist/css/admin.css', false, ALERTX_VERSION );
		// wp_enqueue_style( 'wp-color-picker' );

		// $screen = get_current_screen();
		// if ( $screen && $screen->id === 'woocommerce_page_multi-order-tracker' ) {

		// 	wp_enqueue_style(
		// 		'multi-order-toastify-style',
		// 		MULTI_ORDER_TRACKER_ASSETS . '/lib/toastify/toastify.css',
		// 		array(),
		// 		ALERTX_VERSION
		// 	);

		// 	wp_enqueue_style(
		// 		'multi-order-settings-style',
		// 		MULTI_ORDER_TRACKER_ASSETS . '/css/settings.min.css',
		// 		array(),
		// 		ALERTX_VERSION
		// 	);

		// 	wp_enqueue_script(
		// 		'multi-order-toastify-script',
		// 		MULTI_ORDER_TRACKER_ASSETS . '/lib/toastify/toastify.js',
		// 		array( 'multi-order-admin-script' ),
		// 		ALERTX_VERSION,
		// 		true
		// 	);
		// }

		// wp_enqueue_style(
		// 	'multi-order-admin-style',
		// 	MULTI_ORDER_TRACKER_ASSETS . '/css/admin.css',
		// 	array(),
		// 	ALERTX_VERSION
		// );

		// $settings = Helper::get_settings();
		// $heading_font_size 		= absint( $settings['heading_font_size'] );
		// $heading_color 			= sanitize_hex_color( $settings['heading_color'] );
		// $primary_color 			= sanitize_hex_color( $settings['primary_color'] );
		// $secondary_color 		= sanitize_hex_color( $settings['secondary_color'] );
		// $sub_heading_font_size 	= absint( $settings['sub_heading_font_size'] );
		// $sub_heading_font_color = sanitize_hex_color( $settings['sub_heading_color'] );
		// $footer_background_color = sanitize_hex_color( $settings['footer_background_color'] );
		// $footer_text_color 		= sanitize_hex_color( $settings['footer_text_color'] );

		// $popup_settings_css = "
		// 	:root {
		// 		--primary-color: {$primary_color};
		// 		--secondary-color: {$secondary_color};
		// 		--heading-color: {$heading_color};
		// 		--heading-font-size: {$heading_font_size}px;
		// 		--sub-heading-font-size: {$sub_heading_font_size}px;
		// 		--sub-heading-font-color: {$sub_heading_font_color};
		// 		--footer-background-color: {$footer_background_color};
		// 		--footer-text-color: {$footer_text_color};
		// 	}
		// ";

		// // Add inline CSS
		// wp_add_inline_style( 'multi-order-admin-style', $popup_settings_css );

		// wp_enqueue_script(
		// 	'multi-order-admin-script',
		// 	MULTI_ORDER_TRACKER_ASSETS . '/js/admin.js',
		// 	array( 'jquery', 'wp-color-picker' ),
		// 	ALERTX_VERSION,
		// 	true
		// );

		// wp_localize_script(
		// 	'multi-order-admin-script',
		// 	'multi_order',
		// 	array(
		// 		'ajax_url'       => esc_url( admin_url( 'admin-ajax.php' ) ),
		// 		'nonce'          => wp_create_nonce( 'multi-order-tracking-admin-nonce' ),
		// 		'activate'       => esc_html__( 'Activate', 'multi-order-tracker' ),
		// 		'loader'         => '<div class="multi-order-tracking-loader"></div>',
		// 		'settingsNotice' => esc_html__( 'Settings Saved Successfully!', 'multi-order-tracker' ),
		// 		'assetsURL'      => esc_url( MULTI_ORDER_TRACKER_ASSETS ),
		// 	)
		// );
	}


	/**
     * Registering necessary js and css
     * @ Frontend
     */
    public function frontend_script(){
        wp_enqueue_style( 'alertx-front', ALERTX_URL .'/assets/dist/css/notify-style.css', false, ALERTX_VERSION );

        #JS
        wp_enqueue_script( 'alertx-notify-script', ALERTX_URL .'/assets/dist/js/notify-script.js', array('jquery'), ALERTX_VERSION, true );
        wp_localize_script( 'alertx-notify-script', 'notify_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'stock_notification_nonce' )
        ) );
    }
}
