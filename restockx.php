<?php
/**
 * Plugin Name: RestockX
 * Description: Recover lost sales with automatic back-in-stock alerts. Customers click Notify Me on out-of-stock products and get an email when items return.
 * Author: Rejuan Ahamed
 * Version: 1.0.1
 * Requires at least: 6.2
 * Requires Plugins: woocommerce
 * Requires PHP: 7.4
 * Text Domain: restockx
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/vendor/autoload.php';

/**
 * The main plugin class
 */
final class RestockX {

    /**
     * Plugin version
     *
     * @var string
     */
    const version = '1.0.1';

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
     * @return \RestockX
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
        define( 'RESTOCKX_VERSION', self::version );
        define( 'RESTOCKX_FILE', __FILE__ );
        define( 'RESTOCKX_PATH', plugin_dir_path( RESTOCKX_FILE ) ); // Correct path to the plugin's directory
        define( 'RESTOCKX_URL', plugin_dir_url( RESTOCKX_FILE ) );   // Correct URL for the plugin's assets
        define( 'RESTOCKX_ASSETS', RESTOCKX_URL . 'assets' );        // URL for the plugin's assets directory
    }

    /**
     * Do stuff upon plugin activation
     *
     * @return void
     */
    public function activate() {
        $installer = new RestockX\Installer();
        $installer->run();
    }

    /**
     * Initialize the plugin
     *
     * @return void
     */
    public function init_plugin() {
        new RestockX\Assets();

        // Ensure DB schema is up to date
        if ( class_exists( 'RestockX\Installer' ) ) {
            $installer = new RestockX\Installer();
            $installer->maybe_upgrade_schema();
        }

        new RestockX\Admin();
        new RestockX\Frontend();

        /**
         * Fires once RestockX (free) has fully loaded its core modules.
         *
         * Extensions such as RestockX Pro can use this action to bootstrap
         * their own modules safely after the core plugin is ready.
         */
        do_action( 'restockx_loaded' );
    }
}

/**
 * Initilizes the main plugin
 */
function restockx_get_alert() {
    return RestockX::init();
}

/**
 * Get the configured sender email address (Settings → Channels).
 *
 * Every outgoing RestockX email (stock alerts, confirmations and campaigns)
 * uses this address as the "From" header when it is set.
 *
 * @return string Valid email address, or empty string when not configured.
 */
function restockx_get_sender_email() {
    $sender = sanitize_email( (string) get_option( 'restockx_sender_email', '' ) );

    return is_email( $sender ) ? $sender : '';
}

/**
 * Whether the premium upgrade CTAs should be rendered.
 *
 * Returns true by default (free-only install). When RestockX Pro is active it
 * hooks this filter and returns false, which hides every "Go Premium" CTA and
 * unlocks the premium controls at the PHP level (no CSS tricks).
 *
 * Extensions can also use this filter to control the upsell visibility.
 *
 * @return bool
 */
function restockx_show_upgrade_cta() {
    return (bool) apply_filters( 'restockx_show_upgrade_cta', true );
}

// Kick-off the plugin
restockx_get_alert();
