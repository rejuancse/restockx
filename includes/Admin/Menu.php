<?php

namespace Alertx\Admin;

/**
 * Class Menu
 *
 * Handles administration menu and AJAX functionality for AlertX plugin.
 */
class Menu {

    /**
     * Menu constructor.
     *
     * Adds necessary actions and filters upon initialization.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_unused_media_cleaner_menu' ) );
        add_filter( 'set-screen-option', array( $this, 'set_screen_option' ), 10, 3);
        add_action( 'load-media_page_unused-media-cleaner', array( $this, 'add_screen_options' ) );

        // Add this line to handle the AJAX action
        add_action( 'wp_ajax_clear_broken_links_transient', array( $this, 'handle_clear_transient' ) );
    }

    /**
     * Add media page to WordPress admin menu.
     */
    public function register_unused_media_cleaner_menu() {
        add_menu_page(
            __( 'AlertX', 'alertx' ),
            __( 'AlertX', 'alertx' ),
            'manage_options',
            'alertx',
            array( $this, 'uic_admin_page' ),
            'dashicons-warning',
            25                                     // position
        );
    }

    /**
     * Callback function to render the unused media admin page content.
     */
    public function uic_admin_page() {
        $search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
        $author_id = isset( $_GET['author'] ) ? intval( $_GET['author'] ) : null;

        // Create instance of Unused_Media_List and prepare items
        $unused_media_list = new Unused_Media_List($search, $author_id);
        $unused_media_list->prepare_items();

        // Include the view template for displaying the list
        $template = __DIR__ . '/views/unused-media-list.php';
        if ( file_exists( $template ) ) {
            include $template;
        }
    }

    /**
     * Filter callback for setting screen options.
     *
     * @param mixed $status Current status of the screen option.
     * @param string $option Option name.
     * @param mixed $value Option value.
     * @return mixed Filtered status or value.
     */
    public function set_screen_option( $status, $option, $value ) {
        if ( in_array( $option, array( 'unused_media_cleaner_per_page' ), true ) ) {
            return $value;
        }

        return $status;
    }

    /**
     * Add screen options for the media list page.
     */
    public function add_screen_options() {
        add_screen_option( 'per_page', array(
            'label'   => esc_html__( 'Number of items per page:', 'alertx' ),
            'default' => 20,
            'option'  => 'unused_media_cleaner_per_page',
        ));
    }
}
