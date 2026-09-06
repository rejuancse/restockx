<?php
/**
 * Plugin Name: Alertx for WooCommerce
 * Description: Recover lost sales with automatic back-in-stock alerts. Customers click Notify Me on out-of-stock products and get an email when items return.
 * Author: Rejuan Ahamed
 * Version: 1.0.0
 * Requires at least: 6.2
 * Requires Plugins: woocommerce
 * Requires PHP: 7.4
 * Tested up to: 7.1
 * Text Domain: alertx
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/vendor/autoload.php';

/**
 * The main plugin class
 */
final class Alertx {

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

        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
    }

    /**
     * Initialize a singleton instance
     * @return \Alertx
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
        $installer = new Alertx\Installer();
        $installer->run();
    }

    /**
     * Initialize the plugin
     *
     * @return void
     */
    public function init_plugin() {
        new Alertx\Alertx_i18n();
        new Alertx\Assets();

        // Ensure DB schema is up to date
        if ( class_exists( 'Alertx\Installer' ) ) {
            $installer = new Alertx\Installer();
            $installer->maybe_upgrade_schema();
        }

        new Alertx\Admin();
        new Alertx\Frontend();
    }
}

/**
 * Initilizes the main plugin
 */
function alertx_get_stock_alert() {
    return Alertx::init();
}

/**
 * Get the configured sender email address (Settings → Channels).
 *
 * Every outgoing AlertX email (stock alerts, confirmations and campaigns)
 * uses this address as the "From" header when it is set.
 *
 * @return string Valid email address, or empty string when not configured.
 */
function alertx_get_sender_email() {
    $sender = sanitize_email( (string) get_option( 'alertx_sender_email', '' ) );

    return is_email( $sender ) ? $sender : '';
}

// Kick-off the plugin
alertx_get_stock_alert();
