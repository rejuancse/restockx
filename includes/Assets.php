<?php
namespace Alertx;

defined( 'ABSPATH' ) || exit;

// Direct $wpdb read of a custom plugin table (no WP query abstraction,
// low-frequency admin-only read). The table identifier is a static
// esc_sql()-escaped string; PreparedSQL is disabled alongside because the
// identifier cannot use a prepare() placeholder (same as the other DB files).
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

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
		wp_enqueue_style( 'alertx-admin', ALERTX_URL .'/assets/dist/css/admin.css', false, ALERTX_VERSION );

		// Get current page. Read-only page detection for conditional asset
		// enqueuing; no action is taken on the data, so no nonce applies.
		$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only GET param used only to identify the current admin page.

		// Enqueue admin script for campaigns page
		if ( 'campaigns' === $current_page ) {
			// Enqueue jQuery UI for date picker
			wp_enqueue_style( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-ui-datepicker' );

			wp_enqueue_script( 'alertx-admin-script', ALERTX_URL .'/assets/dist/js/campaigns.js', array('jquery', 'jquery-ui-datepicker'), ALERTX_VERSION, true );
			wp_localize_script( 'alertx-admin-script', 'alertxCampaign', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'alertx_campaign_nonce' ),
				'newCampaignUrl' => admin_url( 'admin.php?page=new-campaign' )
			) );
		}

		// Enqueue admin script + localized config for the settings page.
		if ( 'alertx-settings' === $current_page ) {
			wp_enqueue_script( 'alertx-admin-settings', ALERTX_URL . '/assets/dist/js/admin.js', array(), ALERTX_VERSION, true );

			$notify_icon_map = array();
			if ( class_exists( '\Alertx\Frontend\Add_Notify_Me_Button' ) ) {
				foreach ( array( 'bell', 'clock', 'mail', 'tag' ) as $icon_key ) {
					$notify_icon_map[ $icon_key ] = \Alertx\Frontend\Add_Notify_Me_Button::get_icon_svg( $icon_key );
				}

				wp_localize_script( 'alertx-admin-settings', 'alertxSettings', array(
					'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'alertx_settings_nonce' ),
					'iconMap'  => $notify_icon_map,
					'settings' => \Alertx\Frontend\Add_Notify_Me_Button::get_settings(),
				) );
			}
		}

		// Enqueue scripts for new campaign page
		if ( 'new-campaign' === $current_page ) {
			wp_enqueue_script( 'alertx-new-campaign', ALERTX_URL .'/assets/dist/js/new-campaign.js', array('jquery'), ALERTX_VERSION, true );

			// Get subscriber count
			global $wpdb;
			$table_name = esc_sql( $wpdb->prefix . 'alertx_subscriptions' );
			$subscriber_count = $wpdb->get_var(
				"SELECT COUNT(DISTINCT email) FROM $table_name WHERE status = 'confirmed'"
			);

			// Define template contents
			$template_contents = array(
				'default' => '<div style="font-family: Arial, sans-serif; max-width: 612px; margin: 30px auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">' . "\n" .
					'<h2 style="color: #3c06c5; margin-bottom: 20px;">{campaign_title}</h2>' . "\n" .
					'<p style="color: #333; line-height: 1.6; margin-bottom: 15px;">Hello {first_name}! We have an exciting offer just for you!</p>' . "\n" .
					'<p style="color: #555; line-height: 1.6; margin-bottom: 15px;">Check out our amazing products and take advantage of this special deal.</p>' . "\n" .
					'<div style="background: #f9f9f9; border: 2px dashed #ddd; border-radius: 8px; padding: 20px; text-align: center; margin: 25px 0;">' . "\n" .
					'<div style="color: #666; font-size: 12px; text-transform: uppercase; margin-bottom: 10px;">Your Discount Code</div>' . "\n" .
					'<div style="font-size: 28px; font-weight: 700; color: #3c06c5; letter-spacing: 2px; margin: 10px 0;">{discount_code}</div>' . "\n" .
					'<div style="color: #3c06c5; font-size: 15px; font-weight: 600; margin-top: 5px;">{discount_amount}</div>' . "\n" .
					'<div style="color: #777; font-size: 13px;">{discount_expiry}</div>' . "\n" .
					'</div>' . "\n" .
					'<div style="margin: 25px 0;">{product_list}</div>' . "\n" .
					'<div style="text-align: center; margin: 30px 0;">' . "\n" .
					'{cta_button}' . "\n" .
					'</div>' . "\n" .
					'<p style="color: #666; font-size: 13px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0;">Thank you for being part of our community!</p>' . "\n" .
					'</div>',

				'modern' => '<div style="font-family: \'Segoe UI\', Tahoma, sans-serif; max-width: 612px; margin: 30px auto; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">' . "\n" .
					'<div style="background: #f6f7f7; padding: 40px 30px; margin: 0;">' . "\n" .
					'<h1 style="color: #3c06c5; font-size: 32px; text-align: center; margin: 0 0 10px; letter-spacing: 2px; text-transform: uppercase;">{campaign_title}</h1>' . "\n" .
					'<p style="text-align: center; color: #764ba2; font-size: 16px; margin-bottom: 30px;">Just for {first_name}!</p>' . "\n" .
					'<p style="color: #333333; line-height: 1.8; margin-bottom: 20px;">You have been selected for this exclusive promotion. Don\'t miss out on these amazing savings!</p>' . "\n" .
					'<div style="background: #3c06c5; color: #ffffff; padding: 30px; text-align: center; border-radius: 12px; margin: 30px 0;">' . "\n" .
					'<div style="font-size: 12px; text-transform: uppercase; opacity: 0.9;">Use code at checkout</div>' . "\n" .
					'<div style="font-size: 36px; font-weight: 700; letter-spacing: 3px; margin: 15px 0;">{discount_code}</div>' . "\n" .
					'<div style="font-size: 15px; font-weight: 600; margin-top: 5px;">{discount_amount}</div>' . "\n" .
					'<div style="font-size: 13px; opacity: 0.85; margin-top: 5px;">{discount_expiry}</div>' . "\n" .
					'</div>' . "\n" .
					'<div style="margin: 25px 0;">{product_list}</div>' . "\n" .
					'<div style="text-align: center; margin: 30px 0;">' . "\n" .
					'{cta_button}' . "\n" .
					'</div>' . "\n" .
					'</div></div>',

				'minimal' => '<div style="font-family: Georgia, \'Times New Roman\', serif; max-width: 612px; margin: 0 auto; border: 1px solid #dcdcde; margin: 30px auto; padding: 30px; color: #333;">' . "\n" .
					'<p style="font-size: 18px; margin-bottom: 20px;">Dear {first_name},</p>' . "\n" .
					'<p style="line-height: 1.8;">Thank you for your continued support. We wanted to share a quick update with you.</p>' . "\n" .
					'<hr style="border: none; border-top: 1px solid #e0e0e0; margin: 25px 0;">' . "\n" .
					'<p><strong>Campaign:</strong> {campaign_title}</p>' . "\n" .
					'<p style="margin: 15px 0;"><strong>Your Discount Code:</strong> <span style="font-size: 18px; color: #3c06c5; letter-spacing: 1px;">{discount_code}</span></p>' . "\n" .
					'<p style="margin: 0 0 10px; color: #3c06c5; font-weight: 600;">{discount_amount}</p>' . "\n" .
					'<p style="margin: 0 0 15px; color: #666; font-size: 14px;">{discount_expiry}</p>' . "\n" .
					'<div style="margin: 20px 0;">{product_list}</div>' . "\n" .
					'<p style="margin: 30px 0; text-align: center;">{cta_button}</p>' . "\n" .
					'<hr style="border: none; border-top: 1px solid #e0e0e0; margin: 25px 0;">' . "\n" .
					'<p style="font-size: 13px; color: #666;">Visit our store to shop.</p>' . "\n" .
					'</div>',

				'product' => '<div style="font-family: Arial, sans-serif; max-width: 612px; margin: 30px auto; background-color: #ffffff; border: 1px solid #dcdcde; padding: 30px;">' . "\n" .
					'<h2 style="color: #23282d; margin-bottom: 15px;">{campaign_title}</h2>' . "\n" .
					'<p style="color: #333; line-height: 1.6; margin-bottom: 20px;">Hello {first_name}! We\'ve selected some amazing products just for you:</p>' . "\n" .
					'<div style="margin: 25px 0;">{product_list}</div>' . "\n" .
					'<div style="background: #f9f9f9; border: 2px dashed #ddd; border-radius: 8px; padding: 20px; text-align: center; margin: 25px 0;">' . "\n" .
					'<div style="color: #666; font-size: 12px; text-transform: uppercase;">Discount Code</div>' . "\n" .
					'<div style="font-size: 24px; font-weight: 700; color: #23282d; letter-spacing: 2px; margin: 10px 0;">{discount_code}</div>' . "\n" .
					'<div style="color: #3c06c5; font-size: 14px; font-weight: 600; margin-top: 5px;">{discount_amount}</div>' . "\n" .
					'<div style="color: #666; font-size: 12px;">{discount_expiry}</div>' . "\n" .
					'</div>' . "\n" .
					'<div style="text-align: center; margin: 30px 0;">' . "\n" .
					'{cta_button}' . "\n" .
					'</div>' . "\n" .
					'</div>'
			);

			wp_localize_script( 'alertx-new-campaign', 'alertxCampaign', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'alertx_new_campaign' ),
				'subscriberCount' => intval( $subscriber_count ),
				'campaignsUrl' => admin_url( 'admin.php?page=campaigns' ),
				'shopUrl' => ( class_exists( 'WooCommerce' ) && function_exists( 'wc_get_page_permalink' ) ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ),
				'templateContents' => $template_contents
			) );
		}
	}


	/**
     * Registering necessary js and css
     * @ Frontend
     */
    public function frontend_script(){
        wp_enqueue_style( 'alertx-front', ALERTX_URL .'/assets/dist/css/notify-style.css', false, ALERTX_VERSION );

        // Apply custom button styles saved from the admin settings.
        $this->add_button_inline_styles();

        #JS
        wp_enqueue_script( 'alertx-notify-script', ALERTX_URL .'/assets/dist/js/notify-script.js', array('jquery'), ALERTX_VERSION, true );
        wp_localize_script( 'alertx-notify-script', 'notify_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'alertx_notification_nonce' )
        ) );
    }

    /**
     * Builds and attaches the inline CSS for the notify button
     * based on the settings saved in the admin "Notify Me" panel.
     */
    public function add_button_inline_styles() {
        if ( ! class_exists( '\Alertx\Frontend\Add_Notify_Me_Button' ) ) {
            return;
        }

        $s = \Alertx\Frontend\Add_Notify_Me_Button::get_settings();

        // Colors are validated as hex values to keep the CSS output safe.
        $text_color = sanitize_hex_color( $s['text_color'] );
        $bg_color   = sanitize_hex_color( $s['bg_color'] );
        $hover_bg   = sanitize_hex_color( $s['hover_bg_color'] );
        $border_col = sanitize_hex_color( $s['border_color'] );

        $css = sprintf(
            '.notify-me-button-wrap .alertx-notify-button, .notify-me-button-wrap .alertx-submit-notify {color:%1$s !important;background-color:%2$s !important;font-family:%3$s !important;font-size:%4$dpx !important;line-height:1.2;padding:%5$dpx %6$dpx %7$dpx %8$dpx !important;margin:%9$dpx %10$dpx %11$dpx %12$dpx !important;border:%13$dpx solid %14$s !important;border-radius:%15$dpx !important;display:inline-flex;align-items:center;justify-content:center;gap:8px;box-shadow:none !important;transition:background-color .3s ease,border-color .3s ease,color .3s ease;}' .
            '.notify-me-button-wrap .alertx-notify-button:hover,
			.notify-me-button-wrap .alertx-notify-button:focus
			.notify-me-button-wrap .alertx-submit-notify:hover,
			.notify-me-button-wrap .alertx-submit-notify:focus {background-color:%16$s !important;color:%1$s !important;opacity:1 !important;box-shadow:none !important;}' .
            '.notify-me-button-wrap .alertx-notify-button .alertx-btn-icon, .notify-me-button-wrap .alertx-submit-notify .alertx-btn-icon{display:inline-flex;flex-shrink:0;width:1em;height:1em;}' .
            '.notify-me-button-wrap .alertx-notify-button .alertx-btn-icon svg,
			.notify-me-button-wrap .alertx-submit-notify .alertx-btn-icon svg{width:100%%;height:100%%;}',
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

        wp_add_inline_style( 'alertx-front', $css );
    }
}
// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
