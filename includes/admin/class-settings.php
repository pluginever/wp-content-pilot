<?php
namespace Pluginever\WPCP\Admin;

class Settings{
    private $settings;
    function __construct() {
        $this->settings = get_option('wpcp_settings');
        add_action( 'admin_menu', array($this, 'admin_menu') );
        add_action( 'admin_init', array($this, 'wpcp_settings_init') );
    }
    function admin_menu() {
        add_submenu_page( 'edit.php?post_type=wp_content_pilot', 'Settings', 'Settings', 'manage_options', 'wpcp-settings', array($this, 'setting_page') );
    }

    public function setting_page(){
        ?>
        <div class="wrap plvr-settings plvr">
            <form action='options.php' method='post'>

                <h2><?php _e('WP Content Pilot Settings', 'wpcp');?></h2>

                <?php
                settings_fields( 'wpcp_settings' );
                do_settings_sections( 'wpcp_settings' );
                submit_button();
                ?>

            </form>
        </div>
        <?php
    }

    public function wpcp_settings_init(){
        //white listing
        register_setting( 'wpcp_settings', 'wpcp_settings' );
        //article
        add_settings_section(
            'wpcp_article_section',
            __( 'Article Settings', 'wpcp' ),
            array($this, 'wpcp_article_section_callback'),
            'wpcp_settings'
        );

        add_settings_field(
            'article_api',
            __( 'Banned Hosts', 'wpcp' ),
            array( $this, 'article_api_field'),
            'wpcp_settings',
            'wpcp_article_section'
        );

        //youtube
        add_settings_section(
            'wpcp_youtube_section',
            __( 'Youtube Settings', 'wpcp' ),
            array($this, 'wpcp_youtube_section_callback'),
            'wpcp_settings'
        );

        add_settings_field(
            'youtube_api',
            __( 'Youtube API key', 'wpcp' ),
            array( $this, 'youtube_api_field'),
            'wpcp_settings',
            'wpcp_youtube_section'
        );
        //vemio
//        add_settings_section(
//            'wpcp_vimeo_section',
//            __( 'Vimeo Settings', 'wpcp' ),
//            array($this, 'wpcp_vimeo_section_callback'),
//            'wpcp_settings'
//        );
//        add_settings_field(
//            'vimeo_token',
//            __( 'Vimeo Token', 'wpcp' ),
//            array( $this, 'vimeo_token_field'),
//            'wpcp_settings',
//            'wpcp_vimeo_section'
//        );

        //flicker
        add_settings_section(
            'wpcp_flicker_section',
            __( 'Flicker Settings', 'wpcp' ),
            array($this, 'wpcp_flicker_section_callback'),
            'wpcp_settings'
        );
        add_settings_field(
            'flicker_api_key',
            __( 'Flicker API key', 'wpcp' ),
            array( $this, 'flicker_api_key_field'),
            'wpcp_settings',
            'wpcp_flicker_section'
        );
//        add_settings_field(
//            'flicker_api_secret',
//            __( 'Flicker API Secret', 'wpcp' ),
//            array( $this, 'flicker_api_secret_field'),
//            'wpcp_settings',
//            'wpcp_flicker_section'
//        );

    }

    function wpcp_article_section_callback(){

    }

    function article_api_field(  ) {
        ?>
        <textarea name='wpcp_settings[article_banned_host]' id="wpcp_settings[article_banned_host]" cols="30" rows="10"><?php echo $this->get_settings('article_banned_host'); ?>
        </textarea>
        <p class="description"><?php _e('Articles from the above hosts will be rejected.<br> put single url/host per line.', 'wpcp');?></p>
        <?php
    }


    function wpcp_youtube_section_callback(){

    }

    function youtube_api_field(  ) {
        ?>
        <input type='text' name='wpcp_settings[youtube_api]' value='<?php echo $this->get_settings('youtube_api'); ?>'>
        <?php
    }

    function wpcp_vimeo_section_callback(){}
    function vimeo_token_field() {
        ?>
        <input type='text' name='wpcp_settings[vimeo_token]' value='<?php echo $this->get_settings('vimeo_token'); ?>'>
        <?php
    }

    function wpcp_flicker_section_callback(){}
    function flicker_api_key_field() {
        ?>
        <input type='text' name='wpcp_settings[flicker_api_key_field]' value='<?php echo $this->get_settings('flicker_api_key_field'); ?>'>
        <?php
    }
    function flicker_api_secret_field() {
        ?>
        <input type='text' name='wpcp_settings[flicker_api_secret]' value='<?php echo $this->get_settings('flicker_api_secret'); ?>'>
        <?php
    }


    function get_settings($key){
        if( !empty($this->settings[$key])){
            return $this->settings[$key];
        }
        return '';
    }


}


