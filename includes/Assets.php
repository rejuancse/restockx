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
	}


	/**
     * Registering necessary js and css
     * @ Frontend
     */
    public function frontend_script(){
        wp_enqueue_style( 'restockx-frontend', RESTOCKX_URL .'/assets/dist/css/restockx-frontend.css', false, RESTOCKX_VERSION );

        #JS
        wp_enqueue_script( 'restockx-frontend', RESTOCKX_URL .'/assets/dist/js/restockx-frontend.js', array('jquery'), RESTOCKX_VERSION, true );
        wp_localize_script( 'restockx-frontend', 'restockx_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'restockx_notification_nonce' )
        ) );
    }
}
