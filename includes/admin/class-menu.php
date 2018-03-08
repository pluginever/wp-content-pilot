<?php
namespace Pluginever\WPCP\Admin;

class menu{
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
    }

    public function admin_menu(){
    }

}
