<?php

namespace Pluginever\WPCP\Admin;

use Pluginever\WPCP\Traits\Hooker;

class Admin {
    use Hooker;

    /**
     * Admin constructor.
     */
    public function __construct() {
        $this->action( 'admin_enqueue_scripts', 'load_assets' );
        $this->includes();
        $this->instantiate();

    }

    /**
     * Add all the assets required by the plugin
     *
     * @since 1.0.0
     *
     * @return void
     */
    function load_assets() {
        $suffix = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? '' : '.min';
        wp_register_style( 'wp-content-pilot', WPCP_ASSETS . "/css/wp-content-pilot{$suffix}.css", [], date( 'i' ) );
        wp_register_script( 'wp-content-pilot', WPCP_ASSETS . "/js/wp-content-pilot{$suffix}.js", [ 'jquery' ], date( 'i' ), true );
        wp_localize_script( 'wp-content-pilot', 'jsobject', [ 'ajaxurl' => admin_url( 'admin-ajax.php' ) ] );
        wp_enqueue_style( 'wp-content-pilot' );
        wp_enqueue_script( 'wp-content-pilot' );
    }

    public function includes() {
        require WPCP_INCLUDES . '/admin/metabox/class-metabox.php';
    }

    public function instantiate() {
        new \Pluginever\Framework\Metabox();
        new Metabox();
    }


}
