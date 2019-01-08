<?php

namespace Pluginever\WPCP\Core;

use Pluginever\WPCP\Traits\Hooker;

class Help {
    use Hooker;
    public $blocks;

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
    }

    public function admin_menu() {
        add_submenu_page( 'edit.php?post_type=wp_content_pilot', 'Help', '<span style="color:orange;">Help</span>', 'manage_options', 'wpcp-help', array(
            $this,
            'help_page'
        ) );
    }

    public function help_page() {
        ob_start();
        $blocks = $this->get_blocks();
        ?>
        <style>
            .ever-help-page {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                margin-top: 30px;
            }

            .ever-help-page * {
                box-sizing: border-box;
            }

            .ever-help-page .help-block {
                flex: 1;
                align-self: flex-start;
                min-width: 31%;
                max-width: 32%;
                border: 1px solid #ddd;
                margin-right: 2%;
                margin-bottom: 25px;
                border-radius: 3px;
                padding: 25px 15px;
                text-align: center;
                background: #fff;
            }

            .ever-help-page .help-block img {
                max-height: 70px;
            }
        </style>
        <div class="wrap">

            <h2>WP Content Pilot - Help </h2>

            <div class="ever-help-page">
                <?php foreach ( $blocks as $block ): ?>
                    <div class="help-block">
                        <img src="<?php echo esc_url_raw($block['image']);?>" alt="Looking for Something?">
                        <h3><?php echo esc_html($block['title'])?></h3>
                        <p><?php echo esc_html($block['desc'])?></p>
                        <a target="_blank" href="<?php echo esc_url_raw($block['url']);?>" class="button button-primary">
                            <?php echo esc_html($block['button_text'])?></a>
                    </div>
                <?php endforeach;?>
            </div>
        </div>
        <?php
        $output = ob_get_contents();
        ob_get_clean();

        echo $output;
    }

    protected function get_blocks() {
        return [
            [
                'image'       => WPCP_ASSETS . '/images/help/docs.svg',
                'title'       => __( 'Looking for Something?', 'wp-content-pilot' ),
                'desc'        => __( 'We have detailed documentation on every aspects of WP Content Pilot.', 'wp-content-pilot' ),
                'url'         => 'https://www.pluginever.com/docs/wp-content-pilot/',
                'button_text' => __( 'Visit the Plugin Documentation', 'wp-content-pilot' ),
            ],
            [
                'image'       => WPCP_ASSETS . '/images/help/support.svg',
                'title'       => __( 'Need Any Assistance?', 'wp-content-pilot' ),
                'desc'        => __( 'Our EXPERT Support Team is always ready to Help you out.', 'wp-content-pilot' ),
                'url'         => 'https://www.pluginever.com/support/',
                'button_text' => __( 'Contact Support', 'wp-content-pilot' ),
            ],
            [
                'image'       => WPCP_ASSETS . '/images/help/bugs.svg',
                'title'       => __( 'Found Any Bugs?', 'wp-content-pilot' ),
                'desc'        => __( 'OReport any Bug that you Discovered, Get Instant Solutions.', 'wp-content-pilot' ),
                'url'         => 'https://github.com/pluginever/wp-content-pilot',
                'button_text' => __( 'Report to Github', 'wp-content-pilot' ),
            ],
            [
                'image'       => WPCP_ASSETS . '/images/help/customization.svg',
                'title'       => __( 'Require Customization?', 'wp-content-pilot' ),
                'desc'        => __( 'We would Love to hear your Integration and Customization Ideas.', 'wp-content-pilot' ),
                'url'         => 'https://www.pluginever.com/contact-us/',
                'button_text' => __( 'Contact Our Services', 'wp-content-pilot' ),
            ],
            [
                'image'       => WPCP_ASSETS . '/images/help/like.svg',
                'title'       => __( 'Like The Plugin?', 'wp-content-pilot' ),
                'desc'        => __( 'Your Review is very important to us as it helps us to grow more.', 'wp-content-pilot' ),
                'url'         => 'https://wordpress.org/support/plugin/wp-content-pilot/reviews/?rate=5#new-post',
                'button_text' => __( 'Review Us on WP.org', 'wp-content-pilot' ),
            ],
        ];
    }
}
