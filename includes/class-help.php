<?php

class WPCP_Help {
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
        $features = $this->get_features();
        ?>
        <style>
            .ever-help-page {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                margin: -15px;
            }

            .ever-help-page * {
                box-sizing: border-box;
            }

            .ever-help-page .help-block-wrap {
                flex: 0 0 20%;
                max-width: 20%;
                align-self: flex-start;
                padding: 15px;
            }
            .ever-help-page .help-block {
                border: 1px solid #ddd;
                border-radius: 3px;
                padding: 25px 15px;
                text-align: center;
                background: #fff;
            }

            .ever-help-page .help-block img {
                max-height: 70px;
            }

            .free-vs-pro table{
                background-color: #fff;
                border: 1px solid #DDDDDD;
                border-radius: 3px;
                max-width: 700px;
            }
            .free-vs-pro table tr{
                padding: 0;
            }
            .free-vs-pro table tr:nth-child(even){
                background-color: #f1f1f1;
            }
            .free-vs-pro table th{
                padding: 30px;
                border-left: 1px solid #DDDDDD;
                font-weight: 500;
                font-size: 18px;
            }
            .free-vs-pro table th + th,
            .free-vs-pro table td + td{
                text-align: center;
            } 
            .free-vs-pro table td{
                padding: 10px 30px;
                border-left: 1px solid #DDDDDD;
                vertical-align: middle;
            }
            .free-vs-pro table th:first-child,
            .free-vs-pro table td:first-child{
                border-left: none;
            }
            .free-vs-pro table td strong{
                font-size: 16px;
            }
            .free-vs-pro table td p{
                margin-top: 5px;
            }
            .free-vs-pro table td .dashicons-yes{
                color: #1BAB0B;
            }
            .free-vs-pro table td .dashicons-no-alt{
                color: #EF2727;
            }
            .free-vs-pro .button-pro{
                background-color: #ff7a03;
                color: #fff;
                border-color: #ca5f00;
                height: auto;
                padding: 5px 20px;
                font-size: 16px;
            }

            .free-vs-pro .button-pro:hover{
                color: #fff;
                border-color: #ca5f00;
                background-color: #ff8518;
            }
        </style>
        <div class="wrap">

            <h2>WP Content Pilot - Help </h2>

            <div class="ever-help-page">
                <?php foreach ( $blocks as $block ): ?>
                    <div class="help-block-wrap">
                        <div class="help-block">
                            <img src="<?php echo esc_url_raw($block['image']);?>" alt="Looking for Something?">
                            <h3><?php echo esc_html($block['title'])?></h3>
                            <p><?php echo esc_html($block['desc'])?></p>
                            <a target="_blank" href="<?php echo esc_url_raw($block['url']);?>" class="button button-primary">
                                <?php echo esc_html($block['button_text'])?></a>
                        </div>
                    </div>
                <?php endforeach;?>
            </div>
            <?php if ( ! defined( 'WPCP_PRO_VERSION' ) ): ?>
                <div class="free-vs-pro">
                    <h3>Are you looking for more? Checkout our Pro Version.</h3>
                    <table class="widefat">
                        <tr>
                            <th>Features</th>
                            <th>Free</th>
                            <th>Pro</th>
                        </tr>
                        <?php foreach( $features as $feature ): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html( $feature[ 'title' ] )?></strong>
                                    <p><?php echo esc_html( $feature[ 'desc' ] )?></p>
                                </td>
                                <td>
                                    <?php if ( isset( $feature[ 'free' ] ) && $feature[ 'free' ] ): ?>
                                        <span class="dashicons dashicons-yes"></span>
                                    <?php else: ?>
                                        <span class="dashicons dashicons-no-alt"></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ( isset( $feature[ 'pro' ] ) && $feature[ 'pro' ] ): ?>
                                        <span class="dashicons dashicons-yes"></span>
                                    <?php else: ?>
                                        <span class="dashicons dashicons-no-alt"></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td></td>
                            <td></td>
                            <td>
                                <a href="#" class="button button-pro">Get Pro</a>
                            </td>
                        </tr>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php
        $output = ob_get_contents();
        ob_get_clean();

        echo $output;
    }

    protected function get_blocks() {
        return [
            [
                'image'       => WPCP_ASSETS_URL . '/images/help/docs.svg',
                'title'       => __( 'Looking for Something?', 'wp-content-pilot' ),
                'desc'        => __( 'We have detailed documentation on every aspects of WP Content Pilot.', 'wp-content-pilot' ),
                'url'         => 'https://www.pluginever.com/docs/wp-content-pilot/',
                'button_text' => __( 'Visit the Plugin Documentation', 'wp-content-pilot' ),
            ],
            [
                'image'       => WPCP_ASSETS_URL . '/images/help/support.svg',
                'title'       => __( 'Need Any Assistance?', 'wp-content-pilot' ),
                'desc'        => __( 'Our EXPERT Support Team is always ready to Help you out.', 'wp-content-pilot' ),
                'url'         => 'https://www.pluginever.com/support/',
                'button_text' => __( 'Contact Support', 'wp-content-pilot' ),
            ],
            [
                'image'       => WPCP_ASSETS_URL . '/images/help/bugs.svg',
                'title'       => __( 'Found Any Bugs?', 'wp-content-pilot' ),
                'desc'        => __( 'OReport any Bug that you Discovered, Get Instant Solutions.', 'wp-content-pilot' ),
                'url'         => 'https://github.com/pluginever/wp-content-pilot',
                'button_text' => __( 'Report to Github', 'wp-content-pilot' ),
            ],
            [
                'image'       => WPCP_ASSETS_URL . '/images/help/customization.svg',
                'title'       => __( 'Require Customization?', 'wp-content-pilot' ),
                'desc'        => __( 'We would Love to hear your Integration and Customization Ideas.', 'wp-content-pilot' ),
                'url'         => 'https://www.pluginever.com/contact-us/',
                'button_text' => __( 'Contact Our Services', 'wp-content-pilot' ),
            ],
            [
                'image'       => WPCP_ASSETS_URL . '/images/help/like.svg',
                'title'       => __( 'Like The Plugin?', 'wp-content-pilot' ),
                'desc'        => __( 'Your Review is very important to us as it helps us to grow more.', 'wp-content-pilot' ),
                'url'         => 'https://wordpress.org/support/plugin/wp-content-pilot/reviews/?rate=5#new-post',
                'button_text' => __( 'Review Us on WP.org', 'wp-content-pilot' ),
            ],
        ];
    }

    public function get_features() {
        return [
            [
                'title' => __( 'Feed', 'wp-content-pilot' ),
                'desc' => __( 'RSS Feed module to import rss feeds', 'wp-content-pilot' ),
                'free' => true,
                'pro' => true,
            ],
            [
                'title' => __( 'Envato', 'wp-content-pilot' ),
                'desc' => __( 'RSS Feed module to import rss feeds', 'wp-content-pilot' ),
                'free' => true,
                'pro' => true,
            ],
            [
                'title' => __( 'Twitter', 'wp-content-pilot' ),
                'desc' => __( 'RSS Feed module to import rss feeds', 'wp-content-pilot' ),
                'free' => false,
                'pro' => true,
            ],
            [
                'title' => __( 'Facebook', 'wp-content-pilot' ),
                'desc' => __( 'RSS Feed module to import rss feeds', 'wp-content-pilot' ),
                'free' => false,
                'pro' => true,
            ],
        ];
    }
}
