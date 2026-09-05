<?php
/**
 * New Campaign page methods for the AlertX Pro admin menu.
 *
 * @package Alertx\Admin\Pages
 */

namespace Alertx\Admin\Pages;

defined( 'ABSPATH' ) || exit;

/**
 * Trait New_Campaign_Page
 *
 * Admin page methods moved out of Alertx_Menu. The trait is merged back
 * into the Alertx_Menu class, so every method keeps the exact same
 * visibility and $this behaviour as before the split.
 */
trait New_Campaign_Page {

	/**
	 * New Campaign page
	 *
	 * @return void
	 */
	public function alertx_new_campaign() {
		$template_path = ALERTX_PATH . 'views/admin-new-campaign.php';

		if ( file_exists( $template_path ) ) {
			include $template_path;
		} else {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Template file not found.', 'alertx-pro' ) . '</p></div>';
		}
	}
}
