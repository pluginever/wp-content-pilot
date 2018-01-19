<?php

namespace Pluginever\WPCP\Module;

class Module {

    /**
     * Hold the modules
     *
     * @var
     */
    protected $modules;

    /**
     * Module constructor.
     */
    public function __construct() {
        $this->init_modules();
    }

    /**
     * Initialize the modules
     *
     * @return void
     */
    public function init_modules() {
        $this->modules = [
            'article' => [
                'title'       => __( 'Article', 'erp' ),
                'description' => __( 'Scrap articles with the predefined keywords', 'wpcp' ),
                'supports'    => array( 'title', 'author', 'image', 'images', 'content', 'excerpt' ),
                'callback'    => 'Pluginever\WPCP\Module\Article',
            ],
        ];

    }

    /**
     * Get all the registered modules
     *
     * @return array
     */
    public function get_modules() {
        return apply_filters( 'wpcp_get_modules', $this->modules );
    }

    /**
     * Get a module
     *
     * @param $module
     *
     * @return array|boolean
     */
    public function get_module( $module ) {
        if ( array_key_exists( $module, $this->modules ) ) {
            return $this->modules[ $module ];
        }

        return false;
    }

}
