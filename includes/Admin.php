<?php

namespace AlertX;

defined( 'ABSPATH' ) || exit;

/**
 * The admin class
 */
class Admin {
    /**
     * Initialize the class
     *
     * @since   1.0.0
     * @access  public
     * @param   none
     * @return  void
     */
    public function __construct() {
        new Admin\Alertx_Menu();
    }
}
