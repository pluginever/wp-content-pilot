<?php namespace Pluginever\WPCP\Traits;

trait Singleton {

    /**
     * @var
     */
    private static $instance;

    /**
     * @return \Pluginever\WPCP\Traits\Singleton
     */
    public static function instance() {
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
            self::$instance = new self;

            if ( method_exists( self::$instance, 'setup' ) ) {
                self::$instance->setup();
            }
        }

        return self::$instance;
    }

}
