<?php
// don't call the file directly
defined( 'ABSPATH' ) || exit();

class WPCP_Feed extends WPCP_Module {
    
    /**
     * The single instance of the class
     *
     * @var $this ;
     */
    protected static $_instance = null;
    
    /**
     * WPCP_Module constructor.
     */
    public function __construct() {
        parent::__construct( 'feed' );
        add_action( 'wp_feed_options', array( $this, 'set_feed_options' ) );
        add_action( 'http_response', array( $this, 'trim_feed_content' ) );
    }
    
    /**
     * @return string
     */
    public function get_module_icon() {
        return '';
    }
    
    /**
     * @return array
     * @since 1.2.0
     */
    public function get_template_tags() {
        return array(
            'title'      => __( 'Title', 'wp-content-pilot' ),
            'excerpt'    => __( 'Summary', 'wp-content-pilot' ),
            'content'    => __( 'Content', 'wp-content-pilot' ),
            'image_url'  => __( 'Main image url', 'wp-content-pilot' ),
            'source_url' => __( 'Source link', 'wp-content-pilot' ),
        );
    }
    
    /**
     * @return string
     */
    public function get_default_template() {
        $template
            = <<<EOT
<img src="{image_url}" alt="">
{content}
<br> <a href="{source_url}" target="_blank">Source</a>
EOT;
        
        return $template;
    }
    
    /**
     * @param $post
     */
    public function add_campaign_option_fields( $post ) {
        
        echo WPCP_HTML::textarea_input( array(
            'name'        => '_feed_links',
            'label'       => __( 'Feed Links', 'wp-content-pilot' ),
            'placeholder' => __( 'Example: http://example.com/feed', 'wp-content-pilot' ),
            'desc'        => __( 'Input feed links, Separate links with a comma (,)', 'wp-content-pilot' ),
        ) );
        
        echo WPCP_HTML::checkbox_input( array(
            'name'          => '_fetch_full_content',
            'label'         => __( 'Feed fetch full content', 'wp-content-pilot' ),
            'wrapper_class' => 'pro',
            'attrs'         => array(
                'disabled' => 'disabled'
            )
        ) );
    }
    
    /**
     * @param $campaign_id
     * @param $posted
     */
    public function save_campaign_meta( $campaign_id, $posted ) {
        $raw_links = empty( $posted['_feed_links'] ) ? '' : esc_html( $posted['_feed_links'] );
        $links     = wpcp_string_to_array( $raw_links, ',', array( 'trim', 'esc_url' ) );
        $str_links = implode( ',', $links );
        
        $force_feed         = empty( $posted['_force_feed'] ) ? '' : sanitize_key( $posted['_force_feed'] );
        $fetch_full_content = empty( $posted['_fetch_full_content'] ) ? '' : sanitize_key( $posted['_fetch_full_content'] );
        
        update_post_meta( $campaign_id, '_feed_links', $str_links );
        update_post_meta( $campaign_id, '_force_feed', $force_feed );
        update_post_meta( $campaign_id, '_fetch_full_content', $fetch_full_content );
    }
    
    /**
     * @param $sections
     *
     * @return array
     * @since 1.2.0
     */
    public function get_setting_section( $sections ) {
        return $sections;
    }
    
    /**
     * @param $fields
     *
     * @return array
     * @since 1.2.0
     */
    public function get_setting_fields( $fields ) {
        return $fields;
    }
    
    /**
     * @param int $campaign_id
     *
     * @return mixed|void|WP_Error
     * @since  1.2.0
     */
    public function get_post( $campaign_id ) {
        $sources = $this->get_campaign_meta( $campaign_id, '_feed_links' );
        if ( empty( $sources ) ) {
            return new WP_Error( 'missing-data', __( 'Campaign do not have feed link to proceed, please set link', 'wp-content-pilot' ) );
        }
        
        wpcp_logger()->info( 'Loaded Feed Campaign', $campaign_id );
        
        foreach ( $sources as $source ) {
            wpcp_logger()->info( sprintf( __( 'Looping through feed link now trying with [ %s ]', 'wp-content-pilot' ), $source ), $campaign_id );
            
            if ( $this->is_deactivated_key( $campaign_id, $source ) ) {
                $message = sprintf( __( 'The feed url is deactivated for 1 hr because last time could not find any article with url [%s]', 'wp-content-pilot' ), $source );
                wpcp_logger()->info( $message, $campaign_id );
                continue;
            }
            
            //get links from database
            wpcp_logger()->info( __( 'Checking cached links in store', 'wp-content-pilot' ), $campaign_id );
            $links = $this->get_links( $source, $campaign_id );
            if ( empty( $links ) ) {
                wpcp_logger()->info( __( 'No cached links in store.Generating new links....', 'wp-content-pilot' ), $campaign_id );
                $this->discover_links( $source, $campaign_id );
                $links = $this->get_links( $source, $campaign_id );
            }
            
            if ( empty( $links ) ) {
                wpcp_logger()->error( __( 'Could not find any new link, lets try later.', 'wp-content-pilot' ), $campaign_id );
            }
            
            wpcp_logger()->info( __( 'Looping through cached links for publishing article', 'wp-content-pilot' ), $campaign_id );
            foreach ( $links as $link ) {
                wpcp_logger()->info( sprintf( __( 'Grabbing feed from [%s]', 'wp-content-pilot' ), $link->url ), $campaign_id );
                
                $this->update_link( $link->id, [ 'status' => 'failed' ] );
                
                $curl = $this->setup_curl();
                $curl->setHeader( 'accept-encoding', 'utf-8' );
                $curl->setOpt( CURLOPT_ENCODING, '' );
                $curl->get( $link->url );
                
                if ( $curl->isError() && $this->initiator != 'cron' ) {
                    wpcp_logger()->error( sprintf( __( "Failed processing link reason [%s]", 'wp-content-pilot' ), $curl->getErrorMessage() ), $campaign_id );
                    continue;
                }
                wpcp_logger()->info( __( "Extracting post response from request", 'wp-content-pilot' ), $campaign_id );
                $html = $curl->response;
                
                $readability = new WPCP_Readability();
                
                $readable = $readability->parse( $html, $link->url );
                if ( is_wp_error( $readable ) ) {
                    wpcp_logger()->error( sprintf( __( "Failed readability reason [%s]", 'wp-content-pilot' ), $readable->get_error_message() ), $campaign_id );
                    continue;
                }
                
                //check if the clean title metabox is checked and perform title cleaning
                $check_clean_title = wpcp_get_post_meta( $campaign_id, '_clean_title', 'off' );
                
                if ( 'on' == $check_clean_title ) {
                    wpcp_logger()->info( __( 'Cleaning post title', 'wp-content-pilot' ), $campaign_id );
                    $title = wpcp_clean_title( $readability->get_title() );
                } else {
                    $title = html_entity_decode( $readability->get_title(), ENT_QUOTES );
                }
                
                wpcp_logger()->info( __( 'Making article content from response', 'wp-content-pilot' ), $campaign_id );
                $meta = $link->meta;
                
                $article = apply_filters( 'wpcp_feed_article', array(
                    'title'      => $title,
                    'author'     => $readability->get_author(),
                    'image_url'  => $readability->get_image(),
                    'excerpt'    => $readability->get_excerpt(),
                    'language'   => $readability->get_language(),
                    'content'    => $readability->get_excerpt(),
                    'source_url' => $link->url,
                    'desc'       => $meta['description'],
                ), $readability, $campaign_id );
                
                wpcp_logger()->info( __( 'Article processed from campaign', 'wp-content-pilot' ), $campaign_id );
                $this->update_link( $link->id, [ 'status' => 'success' ] );
                
                return $article;
            }
        }
        
        $log_url = admin_url( '/edit.php?post_type=wp_content_pilot&page=wpcp-logs' );
        
        return new WP_Error( 'campaign-error', __( sprintf( 'No feed article generated check <a href="%s">log</a> for details.', $log_url ), 'wp-content-pilot' ) );
    }
    
    /**
     * @param $source
     * @param $campaign_id
     *
     * @return bool|mixed|WP_Error
     * @since 1.2.0
     */
    public function discover_links( $source, $campaign_id ) {
        include_once( ABSPATH . WPINC . '/feed.php' );
        
        // If force feed
        
        $rss = fetch_feed( $source );
        
        if ( is_wp_error( $rss ) ) {
            wpcp_logger()->error( sprintf( __( 'Failed fetching feeds [%s]', 'wp-content-pilot' ), $rss->get_error_message() ), $campaign_id );
            
            wpcp_logger()->info( __( 'Checking for force feed and initiating force feed', 'wp-content-pilot' ), $campaign_id );
            if ( ! function_exists( 'wpcp_force_feed' ) ) {
                add_action( 'wp_feed_options', 'wpcp_force_feed', 10, 1 );
                function wpcp_force_feed( $rss ) {
                    $rss->force_feed( true );
                }
            }
            
            return $rss;
        }
        
        $max_items = $rss->get_item_quantity();
        $rss_items = $rss->get_items( 0, $max_items );
        if ( ! isset( $max_items ) || $max_items == 0 ) {
            wpcp_logger()->error( __( 'Could not find any article, waiting...', 'wp-content-pilot' ), $campaign_id );
            
            return new WP_Error( 'feed-error', __( 'Could not find any article, waiting...', 'wp-content-pilot' ) );
        }
        
        $links    = [];
        $inserted = 0;
        wpcp_logger()->info( __( 'Finding links from response and inserting into database', 'wp-content-pilot' ), $campaign_id );
        
        foreach ( $rss_items as $rss_item ) {
            $url = esc_url( $rss_item->get_permalink() );
            if ( stristr( $source, 'news.google' ) ) {
                $urlParts   = explode( 'url=', $url );
                $correctUrl = $urlParts[1];
                $url        = $correctUrl;
            }
            
            //Google alerts links correction
            if ( stristr( $source, 'alerts/feeds' ) && stristr( $source, 'google' ) ) {
                preg_match( '{url\=(.*?)[&]}', $url, $urlMatches );
                $correctUrl = $urlMatches[1];
                
                if ( trim( $correctUrl ) != '' ) {
                    $url = $correctUrl;
                }
            }
            
            $title = $rss_item->get_title();
            $meta  = array(
                'description' => '' != $rss_item->get_content() ? $rss_item->get_content() : '',
            );
            
            if ( wpcp_is_duplicate_url( $url ) ) {
                continue;
            }
            
            $skip = apply_filters( 'wpcp_skip_duplicate_title', true, $title, $campaign_id );
            if ( $skip ) {
                continue;
            }
            $data = array(
                'url'     => esc_url( $url ),
                'title'   => $title,
                'for'     => $source,
                'camp_id' => $campaign_id,
                'meta'    => $meta
            );
            if ( false !== $this->insert_link( $data ) ) {
                $inserted += 1;
            }
        }
        
        wpcp_logger()->info( sprintf( __( 'Total found links [%d] and accepted [%d] and reject [%d]', 'wp-content-pilot' ), count( $rss_items ), $inserted, ( count( $rss_items ) - $inserted ) ), $campaign_id );
        
        return true;
    }
    
    /**
     * Set user agent to fix curl transfer
     * closed without complete data
     *
     * @param $args
     *
     * @since 1.0.0
     *
     */
    public function set_feed_options( $args ) {
        $args->set_useragent( 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/41.0.2272.76 ' );
    }
    
    /**
     * Trim body to remove extra space
     *
     * since 1.0.0
     *
     * @param $args
     *
     * @return mixed
     */
    public function trim_feed_content( $args ) {
        $args['body'] = trim( $args['body'] );
        
        return $args;
    }
    
    /**
     * Main WPCP_Feed Instance.
     *
     * Ensures only one instance of WPCP_Feed is loaded or can be loaded.
     *
     * @return WPCP_Feed Main instance
     * @since 1.0.0
     * @static
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
}

WPCP_Feed::instance();
