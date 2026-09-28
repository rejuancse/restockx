<?php
namespace RestockX;

defined( 'ABSPATH' ) || exit;

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
		wp_enqueue_style( 'restockx-admin', RESTOCKX_URL .'/assets/dist/css/restockx-admin.css', false, RESTOCKX_VERSION );

		// Get current page. Read-only page detection for conditional asset
		// enqueuing; no action is taken on the data, so no nonce applies.
		$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only GET param used only to identify the current admin page.

		// Admin JS is only needed on the RestockX pages.
		if ( false === strpos( (string) $hook_suffix, 'restockx' ) ) {
			return;
		}

		wp_enqueue_script( 'restockx-admin', RESTOCKX_URL .'/assets/dist/js/restock-admin.js', array(), RESTOCKX_VERSION, true );
		wp_localize_script(
			'restockx-admin',
			'restockx_admin',
			array(
				'confirm_delete_subscriber' => __( 'Are you sure you want to delete this subscriber?', 'restockx' ),
				'confirm_reset_template'    => __( 'Are you sure you want to reset the email template to its default content?', 'restockx' ),
			)
		);

		// Settings page (free only): tabbed settings UI. The "Notify Me" tab
		// is fully free; "Channels" and "Newsletter" render the premium lock
		// screens. When Pro is active it enqueues its own settings assets.
		if ( 'restockx-settings' === $current_page && restockx_show_upgrade_cta() ) {
			wp_enqueue_script( 'restockx-settings', RESTOCKX_URL . '/assets/dist/js/restock-settings.js', array(), RESTOCKX_VERSION, true );

			// The button settings API (defaults, fonts, icon SVGs) lives in
			// the Add_Notify_Me_Button class.
			$notify_icon_map = array();
			foreach ( array( 'bell', 'clock', 'mail', 'tag' ) as $icon_key ) {
				$notify_icon_map[ $icon_key ] = \RestockX\Frontend\Add_Notify_Me_Button::get_icon_svg( $icon_key );
			}

			wp_localize_script( 'restockx-settings', 'restockxSettings', array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'restockx_settings_nonce' ),
				'iconMap'  => $notify_icon_map,
				'settings' => \RestockX\Frontend\Add_Notify_Me_Button::get_settings(),
			) );
		}
	}

	/**
     * Registering necessary js and css
     * @ Frontend
     */
    public function frontend_script(){
        wp_enqueue_style( 'restockx-frontend', RESTOCKX_URL .'/assets/dist/css/restockx-frontend.css', false, RESTOCKX_VERSION );

        // Apply the button appearance saved in Settings → "Notify Me" so the
        // styles work with or without the Pro plugin active.
        wp_add_inline_style( 'restockx-frontend', $this->notify_button_inline_styles() );

        #JS
        wp_enqueue_script( 'restockx-frontend', RESTOCKX_URL .'/assets/dist/js/restockx-frontend.js', array('jquery'), RESTOCKX_VERSION, true );
        wp_localize_script( 'restockx-frontend', 'restockx_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'restockx_notification_nonce' )
        ) );
    }

    /**
     * Builds the inline CSS that applies the "Notify Me" button appearance
     * saved from the settings page.
     *
     * @return string
     */
    private function notify_button_inline_styles() {
        $s = \RestockX\Frontend\Add_Notify_Me_Button::get_settings();

        // Colors are validated as hex values to keep the CSS output safe.
        $text_color = sanitize_hex_color( $s['text_color'] );
        $bg_color   = sanitize_hex_color( $s['bg_color'] );
        $hover_bg   = sanitize_hex_color( $s['hover_bg_color'] );
        $border_col = sanitize_hex_color( $s['border_color'] );

        return sprintf(
            '.notify-me-button-wrap .restockx-notify-button, .notify-me-button-wrap .restockx-submit-notify {color:%1$s !important;background-color:%2$s !important;font-family:%3$s !important;font-size:%4$dpx !important;line-height:1.2;padding:%5$dpx %6$dpx %7$dpx %8$dpx !important;margin:%9$dpx %10$dpx %11$dpx %12$dpx !important;border:%13$dpx solid %14$s !important;border-radius:%15$dpx !important;display:inline-flex;align-items:center;justify-content:center;gap:8px;box-shadow:none !important;transition:background-color .3s ease,border-color .3s ease,color .3s ease;}' .
            '.notify-me-button-wrap .restockx-notify-button:hover,.notify-me-button-wrap .restockx-notify-button:focus,.notify-me-button-wrap .restockx-submit-notify:hover,.notify-me-button-wrap .restockx-submit-notify:focus {background-color:%16$s !important;color:%1$s !important;opacity:1 !important;box-shadow:none !important;}' .
            '.notify-me-button-wrap .restockx-notify-button .restockx-btn-icon, .notify-me-button-wrap .restockx-submit-notify .restockx-btn-icon{display:inline-flex;flex-shrink:0;width:1em;height:1em;}' .
            '.notify-me-button-wrap .restockx-notify-button .restockx-btn-icon svg,.notify-me-button-wrap .restockx-submit-notify .restockx-btn-icon svg{width:100%%;height:100%%;}',
            $text_color ? $text_color : '#ffffff',
            $bg_color ? $bg_color : '#3c06c5',
            $s['font_family'], // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet -- Whitelisted font stack, validated in get_settings().
            max( 8, absint( $s['font_size'] ) ),
            max( 0, absint( $s['padding_top'] ) ),
            max( 0, absint( $s['padding_right'] ) ),
            max( 0, absint( $s['padding_bottom'] ) ),
            max( 0, absint( $s['padding_left'] ) ),
            max( 0, absint( $s['margin_top'] ) ),
            max( 0, absint( $s['margin_right'] ) ),
            max( 0, absint( $s['margin_bottom'] ) ),
            max( 0, absint( $s['margin_left'] ) ),
            max( 0, absint( $s['border_width'] ) ),
            $border_col ? $border_col : '#3c06c5',
            max( 0, absint( $s['border_radius'] ) ),
            $hover_bg ? $hover_bg : '#2a048a'
        );
    }
}
