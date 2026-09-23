<?php
/**
 * Email Templates page methods for the RestockX Pro admin menu.
 *
 * @package RestockX\Admin\Pages
 */

namespace RestockX\Admin\Pages;

defined( 'ABSPATH' ) || exit;

/**
 * Trait Email_Templates_Page
 *
 * Admin page methods moved out of RestockX_Menu. The trait is merged back
 * into the RestockX_Menu class, so every method keeps the exact same
 * visibility and $this behaviour as before the split.
 */
trait Email_Templates_Page {

	/**
	 * Displays and handles the settings page for stock notifications.
	 *
	 * This method processes form submissions to update stock notification settings,
	 * retrieves the current settings, and includes the settings page template if it exists.
	 *
	 * @return void
	 */
	public function restockx_email_templates() {
		if ( isset( $_POST['submit_settings'] ) ) {
			// Verify nonce for security.
			if ( ! isset( $_POST['settings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['settings_nonce'] ) ), 'restockx_save_settings' ) ) {
				wp_die( esc_html__( 'Security check failed.', 'restockx' ) );
			}

			if ( isset( $_POST['notification_threshold'] ) ) {
				update_option( 'restockx_threshold', intval( sanitize_text_field( wp_unslash( $_POST['notification_threshold'] ) ) ) );
			}

			if ( isset( $_POST['email_templates'] ) ) {
				update_option( 'restockx_email_templates', wp_kses_post( wp_unslash( $_POST['email_templates'] ) ) );
			}

			// Save confirmation requirement setting. Double opt-in is a
			// Premium feature: it only saves when RestockX Pro is active,
			// so the disabled free toggle can never be bypassed with a
			// crafted POST request (disabled checkboxes do not submit, and
			// manually added values are ignored here).
			$require_confirmation = ( isset( $_POST['require_confirmation'] ) && ! restockx_show_upgrade_cta() ) ? '1' : '0';
			update_option( 'restockx_require_confirmation', $require_confirmation );

			// Display success message.
			echo '<div class="updated"><p>' . esc_html__( 'Settings saved.', 'restockx' ) . '</p></div>';
		}

		// Retrieve current saved options; use defaults if not set.
		$threshold       = get_option( 'restockx_threshold', 1 );
		$email_templates = get_option( 'restockx_email_templates', $this->get_default_email_templates() );

		// Path to the settings page template.
		$template_path = RESTOCKX_PATH . 'views/email-templates.php';

		// Check if the settings page template exists and include it.
		if ( file_exists( $template_path ) ) {
			include $template_path;
		} else {
			// Display an error message if the template file does not exist.
			echo '<div class="error"><p>' . esc_html__( 'Settings page template not found.', 'restockx' ) . '</p></div>';
		}
	}

	/**
	 * Returns the default email templates for stock notifications.
	 *
	 * @return string Default email templates.
	 */
	private function get_default_email_templates() {
		$template = '';

		// Header section.
		$template .= '<table class="body" style="border-collapse: collapse; border-spacing: 0; vertical-align: top; height: 100% !important; width: 100% !important; min-width: 100%; background-color: #f5f9fc; color: #444; font-family: Helvetica,sans-serif; font-weight: normal; padding: 0; margin: 0; text-align: left; font-size: 14px; line-height: 140%;" border="0" width="100%" cellspacing="0" cellpadding="0">
            <tbody> 
                <tr style="padding: 0; vertical-align: top; text-align: left;">
                    <td class="body-inner wp-mail-smtp" style="border-collapse: collapse !important; vertical-align: top; color: #444; font-family: Helvetica,sans-serif; font-weight: normal; padding: 0; margin: 0; font-size: 14px; line-height: 140%; text-align: center;" align="center" valign="top">
                        <table class="container" style="border-collapse: collapse; border-spacing: 0; padding: 0; vertical-align: top; width: 600px; margin: 20px auto 0; text-align: inherit;" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                                <tr>
                                    <td style="padding: 0;">
                                        <div style="background-color: #3c06c5; color: #ffffff; padding: 20px; text-align: center;">
                                            <h1 style="margin: 0; font-size: 20px; font-weight: 600;">' . esc_html__( 'Product Back in Stock', 'restockx' ) . '</h1>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <table class="container" style="border-collapse: collapse; border-spacing: 0; padding: 0; vertical-align: top; width: 600px; margin: 0 auto; text-align: inherit;" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                                <tr style="padding: 0; vertical-align: top; text-align: left;">
                                    <td class="content" style="border-collapse: collapse !important; vertical-align: top; color: #444; font-family: Helvetica,sans-serif; font-weight: normal; margin: 0; text-align: left; font-size: 14px; line-height: 140%; padding: 60px 75px 45px 75px; position: relative; flex-direction: column; min-width: 0; background-color: #fff; border: 1px solid #eceef3;" align="left" valign="top">
                                        <div class="success">
                                            <p class="text-large" style="color: #444; font-family: Helvetica,Arial,sans-serif; font-weight: normal; padding: 0; text-align: left; line-height: 140%; margin: 0 0 15px 0; font-size: 14px;">' . esc_html__( 'Hello,', 'restockx' ) . '</p>
                                            <p class="text-large" style="color: #444; font-family: Helvetica,Arial,sans-serif; font-weight: normal; padding: 0; text-align: left; line-height: 140%; margin: 0 0 15px 0; font-size: 14px;">' . wp_kses_post( __( 'Great news! The product <strong>{product_name}</strong> is now back in stock at <strong>{site_name}</strong>.', 'restockx' ) ) . '</p>
                                            <p class="text-large" style="color: #444; font-family: Helvetica,Arial,sans-serif; font-weight: normal; padding: 0; text-align: left; line-height: 140%; margin: 0 0 15px 0; font-size: 14px;">' . esc_html__( 'You can purchase it here:', 'restockx' ) . ' <a style="padding: 10px 20px; margin: 10px 0; background-color: #3c06c5; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: bold;" href="{product_url}">' . esc_html__( 'Buy Now', 'restockx' ) . '</a></p>
                                            <p class="text-large" style="color: #444; font-family: Helvetica,Arial,sans-serif; font-weight: normal; padding: 0; text-align: left; line-height: 140%; margin: 0 0 15px 0; font-size: 14px;">' . esc_html__( 'Thank you for your patience and interest in our products.', 'restockx' ) . '</p>
                                            <p class="text-large" style="color: #444; font-family: Helvetica,Arial,sans-serif; font-weight: normal; padding: 0; text-align: left; line-height: 140%; margin: 0 0 15px 0; font-size: 14px;">' . esc_html__( 'Best Regards,', 'restockx' ) . '</p>
                                            <p class="text-large" style="color: #444; font-family: Helvetica,Arial,sans-serif; font-weight: normal; padding: 0; text-align: left; line-height: 140%; margin: 0 0 15px 0; font-size: 14px;">' . esc_html__( 'The {site_name} Team', 'restockx' ) . '</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <table class="container" style="border-collapse: collapse; border-spacing: 0; padding: 0; vertical-align: top; width: 600px; margin: 0 auto 30px; text-align: inherit;" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                                <tr>
                                    <td style="padding: 0;">
                                        <div style="background-color: #3c06c5; color: #ffffff; padding: 12px 20px; text-align: center;">
                                            <span style="margin: 0; font-size: 14px; font-weight: 400;">© ' . esc_html( wp_date( 'Y' ) ) . ' {site_name} | ' . esc_html__( 'All rights reserved.', 'restockx' ) . '</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>';

		// Append unsubscribe link placeholder.
		$template .= '<p style="text-align:center; font-size:13px; color:#666;">' . __( 'If you no longer wish to receive alerts, you can', 'restockx' ) . ' <a href="{unsubscribe_url}">' . __( 'unsubscribe here', 'restockx' ) . '</a>.</p>';
		return $template;
	}
}
