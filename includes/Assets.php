<?php
namespace Alertx;

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
		wp_enqueue_style( 'alertx-admin', ALERTX_URL .'/assets/dist/css/alertx-admin.css', false, ALERTX_VERSION );
	}


	/**
     * Registering necessary js and css
     * @ Frontend
     */
    public function frontend_script(){
        wp_enqueue_style( 'alertxwc-frontend', ALERTX_URL .'/assets/dist/css/alertx-frontend.css', false, ALERTX_VERSION );

        #JS
        wp_enqueue_script( 'alertxwc-frontend', ALERTX_URL .'/assets/dist/js/alertx-frontend.js', array('jquery'), ALERTX_VERSION, true );
        wp_localize_script( 'alertxwc-frontend', 'alertxwc_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'alertx_notification_nonce' )
        ) );
    }
}
