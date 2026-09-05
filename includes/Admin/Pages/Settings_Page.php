<?php
/**
 * Settings page methods for the AlertX Pro admin menu.
 *
 * @package Alertx\Admin\Pages
 */

namespace Alertx\Admin\Pages;

defined( 'ABSPATH' ) || exit;

/**
 * Trait Settings_Page
 *
 * Admin page methods moved out of Alertx_Menu. The trait is merged back
 * into the Alertx_Menu class, so every method keeps the exact same
 * visibility and $this behaviour as before the split.
 */
trait Settings_Page {

	/**
	 * AJAX handler: saves the "Notify Me" button settings from the settings page.
	 *
	 * Expects the full settings array in the `settings` POST parameter and a valid
	 * `alertx_settings_nonce` security token.
	 *
	 * @return void
	 */
	public function save_notify_me_settings() {
		check_ajax_referer( 'alertx_settings_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to do this.', 'alertx-pro' ) ), 403 );
		}

		$raw = isset( $_POST['settings'] ) ? json_decode( wp_unslash( $_POST['settings'] ), true ) : null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON decoded then sanitized field-by-field below.

		if ( ! is_array( $raw ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid settings payload.', 'alertx-pro' ) ), 400 );
		}

		$defaults = \Alertx\Frontend\Add_Notify_Me_Button::get_defaults();
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
		$allowed_fonts           = \Alertx\Frontend\Add_Notify_Me_Button::get_allowed_font_families();
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

		update_option( \Alertx\Frontend\Add_Notify_Me_Button::OPTION_KEY, $settings );

		// Sender email address (Settings → Channels). Stored separately from.
		// the Notify Me button settings; empty value resets the fallback.
		$sender_email = isset( $_POST['sender_email'] ) ? sanitize_email( wp_unslash( $_POST['sender_email'] ) ) : '';

		if ( '' !== $sender_email && ! is_email( $sender_email ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid sender email address.', 'alertx-pro' ) ), 400 );
		}

		update_option( 'alertx_sender_email', $sender_email );

		wp_send_json_success(
			array(
				'message'      => __( 'Settings saved successfully.', 'alertx-pro' ),
				'settings'     => $settings,
				'sender_email' => $sender_email,
			)
		);
	}

	/**
	 * Displays the settings admin page.
	 *
	 * @return void
	 */
	public function alertx_settings() {
		// Path to the admin page template file.
		$template_path = ALERTX_PATH . 'views/admin-settings.php';

		// Check if the template exists before including it.
		if ( file_exists( $template_path ) ) {
			include $template_path; // No parentheses needed for include.
		} else {
			// Template not found, display an error or a fallback message.
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Template file not found.', 'alertx-pro' ) . '</p></div>';
		}
	}
}
