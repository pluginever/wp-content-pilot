<?php

namespace Pluginever\WPCP;

class Menu {
    /**
     * Admin_Menu constructor.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'main_menu' ), 10 );
        add_action( 'admin_menu', array( $this, 'new_campaign_menu' ), 10 );
    }

    public function main_menu() {
        add_menu_page( __( 'WP Content Pilot', 'wp-content-pilot' ), __( 'WP Content Pilot', 'wp-content-pilot' ), 'manage_options', 'wp-content-pilot-v2', array( $this, 'main_menu' ), WPCP_ASSETS . '/images/logo.svg', 5 );
    }

    public function new_campaign_menu() {
        add_submenu_page( 'wp-content-pilot-v2', __( 'Add Campaign', 'wp-content-pilot' ), __( 'Add Campaign', 'wp-content-pilot' ), 'manage_options', 'add-new-campaign-wpcp', array( $this, 'new_campaign_page' ) );
    }

    public function main_menu_page() {

    }

    public function new_campaign_page() {
        include WPCP_PATH . '/screen/new-campaign.php';
    }

}
