<?php

namespace Pluginever\WPCP\Admin;

use Pluginever\WPCP\Traits\Hooker;

class Admin {
    use Hooker;

    /**
     * Admin constructor.
     */
    public function __construct() {
        $this->includes();
        $this->instantiate();
        $this->action( 'admin_enqueue_scripts', 'load_assets' );
        $this->action( 'admin_menu', 'admin_menu' );
        $this->action( 'admin_init', 'remove_logs' );

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

    public function admin_menu(){
        add_submenu_page( 'edit.php?post_type=wp_content_pilot', 'Logs', 'Logs', 'manage_options', 'wpcp-logs', array($this, 'logs_page') );
    }

    public function includes() {
        require WPCP_INCLUDES . '/admin/metabox/class-metabox.php';
        require WPCP_INCLUDES . '/admin/log-list-table.php';
    }

    public function instantiate() {
        new \Pluginever\Framework\Metabox();
        new Metabox();
        new Settings();
    }

    public function remove_logs(){
        if(isset($_GET['remove_logs']) && $_GET['remove_logs'] == '1'){
            if(!current_user_can('manage_options')){
                return;
            }

            if (!isset($_GET['wpcp_nonce']) || !wp_verify_nonce($_GET['wpcp_nonce'], 'wpcp_remove_logs')) {
                return;
            }

            global $wpdb;
            $sql = "truncate {$wpdb->prefix}wpcp_logs;";
            $wpdb->query($sql);
            $page_url  = admin_url( 'edit.php?post_type=wp_content_pilot&page=wpcp-logs' );
            $page_url = remove_query_arg(array('wpcp_nonce', 'remove_logs'), $page_url);
            wp_safe_redirect($page_url);
        }
    }

    function logs_page(){
        $log_remove_url = wp_nonce_url(admin_url('edit.php?post_type=wp_content_pilot&page=wpcp-logs'), 'wpcp_remove_logs', 'wpcp_nonce');
        ?>
        <div class="wrap">
            <h2><?php _e( 'Campaign Log', 'wpcp' ); ?>  <a href="<?php echo esc_url($log_remove_url."&remove_logs=1"); ?>" class="button button-seconday"><?php _e('Clear Logs', 'wpcp');?></a>
            </h2>

            <form method="post">
                <input type="hidden" name="page" value="ttest_list_table">
                <?php
                $list_table = new Log_List_Table();
                $list_table->prepare_items();
                $list_table->display();
                ?>
            </form>

        </div>
        <?php
    }

}
