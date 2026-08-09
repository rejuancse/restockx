<?php
/**
 * Plugin Name: AlertX for WooCommerce
 * Description: Inform customers when out-of-stock WooCommerce products return to stock. "Notify Me" functionality and automatic email reminders.
 * Author: Rejuan Ahamed
 * Version: 1.0.0
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * Tested up to: 7.0
 * Text Domain: alertx
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/vendor/autoload.php';

/**
 * The main plugin class
 */
final class AlertX {

    /**
     * Plugin version
     *
     * @var string
     */
    const version = '1.0.0';

    /**
     * Class construcotr
     */
    private function __construct() {
        $this->define_constants();
        add_action( 'init', array( $this, 'stock_alert_language_load' ) );
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'frontend_script' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_script' ) );
    }

    /**
    * Load Text Domain Language
    */
    function stock_alert_language_load(){
        load_plugin_textdomain( 'alertx', false, basename( dirname( __FILE__ ) ).'/languages/' );
    }

    /**
     * Initialize a singleton instance
     * @return \AlertX
     */
    public static function init() {
        static $instance = false;

        if( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Define the required plugin constants
     *
     * @return void
     */
    public function define_constants() {
        define( 'ALERTX_VERSION', self::version );
        define( 'ALERTX_FILE', __FILE__ );
        define( 'ALERTX_PATH', plugin_dir_path( ALERTX_FILE ) ); // Correct path to the plugin's directory
        define( 'ALERTX_URL', plugin_dir_url( ALERTX_FILE ) );   // Correct URL for the plugin's assets
        define( 'ALERTX_ASSETS', ALERTX_URL . 'assets' );        // URL for the plugin's assets directory
    }

    /**
     * Do stuff upon plugin activation
     *
     * @return void
     */
    public function activate() {
        $installer = new AlertX\Installer();
        $installer->run();
    }

    /**
     * Initialize the plugin
     *
     * @return void
     */
    public function init_plugin() {
        // Ensure DB schema is up to date
        if ( class_exists( 'AlertX\Installer' ) ) {
            $installer = new AlertX\Installer();
            $installer->maybe_upgrade_schema();
        }
        new AlertX\Admin();
        new AlertX\Frontend();
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

    /**
     * Registering necessary js and css
     * @ Admin
     */
    public function admin_script(){
        wp_enqueue_style( 'alertx-admin', ALERTX_URL .'/assets/dist/css/stock-admin.css', false, ALERTX_VERSION );
    }
}

/**
 * Initilizes the main plugin
 */
function alertx_get_alert() {
    return AlertX::init();
}

// Kick-off the plugin
alertx_get_alert();
