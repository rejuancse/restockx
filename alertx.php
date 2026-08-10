<?php
/**
 * Plugin Name: AlertX for WooCommerce
 * Description: Inform customers when out-of-stock WooCommerce products return to stock. "Notify Me" functionality and automatic email reminders.
 * Author: Rejuan Ahamed
 * Version: 1.0.0
 * Requires at least: 5.9
 * Requires Plugins: woocommerce
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

        register_activation_hook( __FILE__, array( $this, 'activate' )  );
        add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
        add_action( 'init', array( $this, 'load_textdomain' ) );
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
        define( 'ALERTX_PATH', plugin_dir_path( ALERTX_FILE ) );
        define( 'ALERTX_URL', plugin_dir_url( ALERTX_FILE ) );
        define( 'ALERTX_ASSETS', ALERTX_URL . 'assets' );
    }

    /**
     * Initialize the plugin
     *
     * @return void
     */
    public function init_plugin() {
        new AlertX\Assets();

        // if ( defined('DOING_AJAX') && DOING_AJAX ) {
        //     new AlertX\Ajax();
        // }

        if (is_admin()) {
            new AlertX\Admin();
        }

        new AlertX\Frontend();
    }

    /**
     * Load plugin textdomain for translations
     *
     * @return void
     */
    public function load_textdomain() {
        if ( version_compare( get_bloginfo( 'version' ), '4.6', '<' ) ) {
            load_plugin_textdomain( 'alertx', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        }
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
}

/**
 * Initilizes the main plugin
 */
function alertx_get_alert() {
    return AlertX::init();
}

// Kick-off the plugin
alertx_get_alert();
