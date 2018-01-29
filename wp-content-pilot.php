<?php
/**
 * Plugin Name: WP Content Pilot
 * Plugin URI:  http://pluginever.com
 * Description: The plugin posts high quality Articles. Amazon, Ebay, BestBuy, Walmart Products Youtube, Vimeo Videos as well as Images from Flicker and from others platforms based on your keywords.
 * Version:     1.0.0
 * Author:      PluginEver
 * Author URI:  http://pluginever.com
 * Donate link: http://pluginever.com
 * License:     GPLv2+
 * Text Domain: wpcp
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2018 PluginEver (email : support@pluginever.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main initiation class
 *
 * @since 1.0.0
 */
class Wp_Content_Pilot {

    /**
     * Add-on Version
     *
     * @since 1.0.0
     * @var  string
     */
    public $version = '1.0.0';

    /**
     * Minimum PHP version required
     *
     * @var string
     */
    private $min_php = '5.4.0';

    /**
     * Holds various class instances
     *
     * @var array
     */
    private $container = array();

    /**
     * @var object
     *
     */
    private static $instance;

    /**
     * Constructor for the class
     *
     * Sets up all the appropriate hooks and actions
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function setup() {
        // on deactivate plugin register hook
        register_deactivation_hook( __FILE__, array( $this, 'auto_deactivate' ) );

        if ( ! $this->is_supported_php() ) {
            return;
        }

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        // instantiate classes
        $this->instantiate();

        // Initialize the action hooks
        $this->init_actions();

        // load the modules
        $this->load_module();

        do_action( 'wp_content_pilot_loaded' );
    }


    /**
     * Initializes the class
     *
     * Checks for an existing instance
     * and if it does't find one, creates it.
     *
     * @since 1.0.0
     *
     * @return object Class instance
     */
    public static function init() {
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Wp_Content_Pilot ) ) {
            self::$instance = new Wp_Content_Pilot;
            self::$instance->setup();
        }

        return self::$instance;
    }


    /**
     * Magic getter to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __get( $prop ) {
        if ( array_key_exists( $prop, $this->container ) ) {
            return $this->container[ $prop ];
        }

        return $this->{$prop};
    }

    /**
     * Magic isset to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __isset( $prop ) {
        return isset( $this->{$prop} ) || isset( $this->container[ $prop ] );
    }


    /**
     * Define constants
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function define_constants() {
        define( 'WPCP_VERSION', $this->version );
        define( 'WPCP_FILE', __FILE__ );
        define( 'WPCP_PATH', dirname( WPCP_FILE ) );
        define( 'WPCP_INCLUDES', WPCP_PATH . '/includes' );
        define( 'WPCP_VENDOR_DIR', WPCP_PATH . '/vendor' );
        define( 'WPCP_URL', plugins_url( '', WPCP_FILE ) );
        define( 'WPCP_ASSETS', WPCP_URL . '/assets' );
        define( 'WPCP_VIEWS', WPCP_PATH . '/views' );
        define( 'WPCP_TEMPLATES_DIR', WPCP_PATH . '/templates' );
    }

    /**
     * Include required files
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function includes() {
        require WPCP_VENDOR_DIR . '/autoload.php';
        require WPCP_INCLUDES . '/functions.php';
        require WPCP_INCLUDES . '/function-helper.php';
        require WPCP_INCLUDES . '/campaign-functions.php';
        require WPCP_INCLUDES . '/module-categories.php';
    }

    /**
     * Instantiate classes
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function instantiate() {
        new \Pluginever\WPCP\Core\Cron();
//        new \Pluginever\WPCP\Install();
//        new \Pluginever\WPCP\Upgrades();
        new \Pluginever\WPCP\Core\CPT();
        new \Pluginever\WPCP\Admin\Admin();
        new \Pluginever\WPCP\Core\Processor();

        $this->container['modules'] = new \Pluginever\WPCP\Module\Module();
    }

    /**
     * Init Hooks
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function init_actions() {
        // Localize our plugin
        add_action( 'init', [ $this, 'localization_setup' ] );

        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
    }

    /**
     * Initialize plugin for localization
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function localization_setup() {
        load_plugin_textdomain( 'wp_content_pilot', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }


    /**
     * Plugin action links
     *
     * @param  array $links
     *
     * @return array
     */
    function plugin_action_links( $links ) {

        //$links[] = '<a href="' . admin_url( 'admin.php?page=' ) . '">' . __( 'Settings', '' ) . '</a>';

        return $links;
    }


    /**
     * Check if the PHP version is supported
     *
     * @return bool
     */
    public function is_supported_php( $min_php = null ) {

        $min_php = $min_php ? $min_php : $this->min_php;

        if ( version_compare( PHP_VERSION, $min_php, '<=' ) ) {
            return false;
        }

        return true;
    }

    /**
     * Show notice about PHP version
     *
     * @return void
     */
    function php_version_notice() {

        if ( $this->is_supported_php() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $error = __( 'Your installed PHP Version is: ', 'wpcp' ) . PHP_VERSION . '. ';
        $error .= __( 'The <strong>WP Content Pilot</strong> plugin requires PHP version <strong>', 'wpcp' ) . $this->min_php . __( '</strong> or greater.', 'wp_content_pilot' );
        ?>
        <div class="error">
            <p><?php printf( $error ); ?></p>
        </div>
        <?php
    }

    /**
     * Bail out if the php version is lower than
     *
     * @return void
     */
    function auto_deactivate() {
        if ( $this->is_supported_php() ) {
            return;
        }

        deactivate_plugins( plugin_basename( __FILE__ ) );

        $error = __( '<h1>An Error Occurred</h1>', 'wpcp' );
        $error .= __( '<h2>Your installed PHP Version is: ', 'wpcp' ) . PHP_VERSION . '</h2>';
        $error .= __( '<p>The <strong>WP Content Pilot</strong> plugin requires PHP version <strong>', 'wp_content_pilot' ) . $this->min_php . __( '</strong> or greater', 'wpcp' );
        $error .= __( '<p>The version of your PHP is ', 'wpcp' ) . '<a href="http://php.net/supported-versions.php" target="_blank"><strong>' . __( 'unsupported and old', 'wpcp' ) . '</strong></a>.';
        $error .= __( 'You should update your PHP software or contact your host regarding this matter.</p>', 'wpcp' );

        wp_die( $error, __( 'Plugin Activation Error', 'wpcp' ), array( 'back_link' => true ) );
    }

    /**
     * Load modules
     *
     * We don't load every module at once, just load
     * what is necessary
     *
     * @return void
     */
    public function load_module() {
        $modules = $this->modules->get_modules();

        if ( ! $modules ) {
            return;
        }

        foreach ( $modules as $key => $module ) {

            if ( ! $this->modules->get_module( $key ) ) {
                continue;
            }

            if ( isset( $module['callback'] ) && class_exists( $module['callback'] ) ) {
                new $module['callback']( $this );
            }
        }
    }

}

/**
 * Initialize the plugin
 *
 * @return object
 */
function wp_content_pilot() {
    return Wp_Content_Pilot::init();
}

// kick-off
wp_content_pilot();
