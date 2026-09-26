<?php
/**
 * Settings page handler for the RestockX admin menu.
 *
 * @package RestockX\Admin\Pages
 */

namespace RestockX\Admin\Pages;

defined( 'ABSPATH' ) || exit;

/**
 * Trait Settings_Page
 *
 * Hosts the free Settings screen logic. The "Notify Me" tab is fully
 * free; the "Channels" and "Newsletter" tabs render the premium lock
 * screens and are taken over by RestockX Pro when it is active.
 */
trait Settings_Page {

	/**
	 * AJAX handler: saves the "Notify Me" button settings from the settings page.
	 *
	 * Registered at priority 5 so it runs before the Pro handler. When
	 * RestockX Pro is active this handler bails early without output and
	 * lets the Pro endpoint (which also saves the Channels sender email)
	 * produce the response.
	 *
	 * Expects the full settings array in the `settings` POST parameter and a valid
	 * `restockx_settings_nonce` security token.
	 *
	 * @return void
	 */
	public function save_notify_me_settings() {
		// Pro owns this endpoint while it is active.
		if ( ! restockx_show_upgrade_cta() ) {
			return;
		}

		check_ajax_referer( 'restockx_settings_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to do this.', 'restockx' ) ), 403 );
		}

		$raw = isset( $_POST['settings'] ) ? json_decode( wp_unslash( $_POST['settings'] ), true ) : null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON decoded then sanitized field-by-field below.

		if ( ! is_array( $raw ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid settings payload.', 'restockx' ) ), 400 );
		}

		$defaults = \RestockX\Frontend\Add_Notify_Me_Button::get_defaults();
		$settings = array();

		// Text fields.
		$settings['button_text']  = isset( $raw['button_text'] ) ? sanitize_text_field( wp_unslash( $raw['button_text'] ) ) : $defaults['button_text'];
		$settings['tooltip_text'] = isset( $raw['tooltip_text'] ) ? sanitize_text_field( wp_unslash( $raw['tooltip_text'] ) ) : $defaults['tooltip_text'];

		// Color fields.
		foreach ( array( 'text_color', 'bg_color', 'hover_bg_color', 'border_color' ) as $color_key ) {
			$color                  = isset( $raw[ $color_key ] ) ? sanitize_hex_color( wp_unslash( $raw[ $color_key ] ) ) : '';
			$settings[ $color_key ] = $color ? $color : $defaults[ $color_key ];
		}

		// Font family — only whitelisted stacks are accepted.
		$allowed_fonts           = \RestockX\Frontend\Add_Notify_Me_Button::get_allowed_font_families();
		$font                    = isset( $raw['font_family'] ) ? wp_unslash( $raw['font_family'] ) : $defaults['font_family']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Whitelisted below.
		$settings['font_family'] = isset( $allowed_fonts[ $font ] ) ? $font : $defaults['font_family'];

		// Icon fields.
		$allowed_icons    = array( 'none', 'bell', 'clock', 'mail', 'tag' );
		$icon             = isset( $raw['icon'] ) ? sanitize_key( wp_unslash( $raw['icon'] ) ) : $defaults['icon'];
		$settings['icon'] = in_array( $icon, $allowed_icons, true ) ? $icon : $defaults['icon'];

		$settings['icon_position'] = ( isset( $raw['icon_position'] ) && 'after' === $raw['icon_position'] ) ? 'after' : 'before';

		// Numeric fields (px values).
		foreach ( array( 'font_size', 'padding_top', 'padding_right', 'padding_bottom', 'padding_left', 'margin_top', 'margin_right', 'margin_bottom', 'margin_left', 'border_width', 'border_radius' ) as $num_key ) {
			$settings[ $num_key ] = isset( $raw[ $num_key ] ) ? absint( $raw[ $num_key ] ) : $defaults[ $num_key ];
		}

		update_option( \RestockX\Frontend\Add_Notify_Me_Button::OPTION_KEY, $settings );

		wp_send_json_success(
			array(
				'message'  => __( 'Settings saved successfully.', 'restockx' ),
				'settings' => $settings,
			)
		);
	}
}
