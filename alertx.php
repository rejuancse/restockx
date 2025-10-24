<?php
/**
 * Plugin Name: AlertX
 * Description: AlertX is a WordPress plugin to find and remove unused media files, manage duplicates, and optimize your media library for better performance.
 * Author: TheBitCraft
 * Author URI: https://thebitcraft.com/
 * Version: 1.0.0
 * Requires PHP: 7.4
 * Requires at least: 5.9
 * Tested up to: 6.8.3
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
    const version = '1.1.0';

    /**
     * Class constructor
     */
    private function __construct() {
        $this->define_constants();
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_script' ) );
        Alertx\Installer::deactivate();
    }

    /**
     * Initialize a singleton instance
     * @return \Alertx
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
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
        define( 'MEDIA_TRACKER_VERSION', self::version );
        define( 'MEDIA_TRACKER_FILE', __FILE__ );
        define( 'MEDIA_TRACKER_PATH', __DIR__ );
        define('ALERTX_PLUGIN_DIR', plugin_dir_path(__FILE__));
        define( 'MEDIA_TRACKER_URL', plugins_url( '', MEDIA_TRACKER_FILE ) );
        define( 'MEDIA_TRACKER_ASSETS', MEDIA_TRACKER_URL . '/assets' );
        define( 'MEDIA_TRACKER_BASENAME', plugin_basename(__FILE__) );
    }

    /**
     * Include required files
     */
    private function includes() {
        require_once ALERTX_PLUGIN_DIR . 'includes/class-alertx-system-monitor.php';
        require_once ALERTX_PLUGIN_DIR . 'includes/class-alertx-security-monitor.php';
        // require_once ALERTX_PLUGIN_DIR . 'includes/class-alertx-woocommerce-monitor.php';
        require_once ALERTX_PLUGIN_DIR . 'includes/class-alertx-notifications.php';
        // require_once ALERTX_PLUGIN_DIR . 'admin/class-alertx-admin.php';
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

        if ( is_admin() ) {
            new Alertx\Admin();
        }
    }

    /**
     * Register necessary CSS and JS
     * @ Admin
     */
    public function admin_script() {
        wp_enqueue_style( 'mt-admin-style', MEDIA_TRACKER_URL . '/assets/dist/css/mt-admin.css', false, MEDIA_TRACKER_VERSION );
        wp_enqueue_script( 'mt-admin-script', MEDIA_TRACKER_URL . '/assets/dist/js/mt-admin.js', array( 'jquery' ), MEDIA_TRACKER_VERSION, true );
        wp_localize_script( 'mt-admin-script', 'mediaTacker', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'mediaTacker_nonce' ),
            'security' => wp_create_nonce('media_tracker_nonce'),
        ));
    }
}

/**
 * Initialize the main plugin
 */
function media_tracker_list() {
    return Alertx::init();
}

// Kick-off the plugin
media_tracker_list();
