<?php
// don't call the file directly
defined( 'ABSPATH' ) || exit();

class WPCP_Vimeo extends WPCP_Module {

	/**
	 * The single instance of the class
     * @author Mahedi hasan <mahedihasannoman@gmail.com>
	 *
	 * @var $this ;
	 */
	protected static $_instance = null;

	/**
	 * WPCP_Module constructor.
	 */
	public function __construct() {
		//option fields
		add_action( 'wpcp_vimeo_campaign_options_meta_fields', 'wpcp_keyword_suggestion_field' );
		add_action( 'wpcp_vimeo_campaign_options_meta_fields', 'wpcp_keyword_field' );

		parent::__construct( 'vimeo' );

		//admin notice
		add_action( 'admin_notices', array( $this, 'admin_notice' ) );
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
			'title'             => __( 'Title', 'wp-content-pilot' ),
			'excerpt'           => __( 'Summary', 'wp-content-pilot' ),
			'content'           => __( 'Content', 'wp-content-pilot' ),
			'image_url'         => __( 'Main image url', 'wp-content-pilot' ),
			'source_url'        => __( 'Source link', 'wp-content-pilot' ),
			'user_name'         => __( 'User Name', 'wp-content-pilot' ),
			'user_link'         => __( 'User Link', 'wp-content-pilot' ),
			'user_bio'          => __( 'User Bio', 'wp-content-pilot' ),
			'user_short_bio'    => __( 'User Short Bio', 'wp-content-pilot' ),
			'tags'              => __( 'Video Tags', 'wp-content-pilot' ),
			'duration'          => __( 'Video Duration', 'wp-content-pilot' ),
			'like_count'        => __( 'Total Likes', 'wp-content-pilot' ),
			'comment_count'     => __( 'Total Comments', 'wp-content-pilot' ),
            'embed_html'        => __( 'HTML Embed Code ', 'wp-content-pilot' ),
            'transcript'        => __( 'Video transcript available in PRO', 'wp-content-pilot' ),
		);
	}

	/**
	 * @return string
	 * @since 1.2.0
	 */
	public function get_default_template() {
		$template
			= <<<EOT
{embed_html}
<br>{content}
<br>
<br>Tags: {tags}
<br>Video Uploaded by: <a href="{user_link}" target="_blank">{user_name}</a>
<br>Bio: {user_bio}
<br>Short Bio: {user_short_bio}
<br>Duration: {duration}
<br>Total Likes: {like_count}
<br>Total Comments: {comment_count}
<br> <a href="{source_url}" target="_blank">Source</a>
EOT;

		return $template;
	}

	/**
	 * @param $post
	 */
	public function add_campaign_option_fields( $post ) {

		echo WPCP_HTML::start_double_columns();

		echo WPCP_HTML::select_input( array(
			'name'    => '_vimeo_sort_direction',
			'label'   => __( 'Vimeo Sort Direction', 'wp-content-pilot' ),
			'tooltip' => __( 'The sort direction of the results.', 'wp-content-pilot' ),
			'options' => array(
				'asc'   => __( 'ASC', 'wp-content-pilot' ),
				'desc' => __( 'DESC', 'wp-content-pilot' ),
			),
			'default' => 'ASC',
		) );


		echo WPCP_HTML::select_input( array(
			'name'    => '_vimeo_filter',
            'label'   => __( 'Vimeo Filter', 'wp-content-pilot' ),
            'tooltip' => __( 'The attribute by which to filter the results. CC and related filters target videos with the corresponding Creative Commons licenses.', 'wp-content-pilot' ),
			'options' => array(
				'CC' => __( 'CC', 'wp-content-pilot' ),
				'CC-BY'      => __( 'CC-BY', 'wp-content-pilot' ),
				'CC-BY-NC'     => __( 'CC-BY-NC', 'wp-content-pilot' ),
				'CC-BY-NC-ND' => __( 'CC-BY-NC-ND', 'wp-content-pilot' ),
				'CC-BY-NC-SA'    => __( 'CC-BY-NC-SA', 'wp-content-pilot' ),
				'CC-BY-ND'    => __( 'CC-BY-ND', 'wp-content-pilot' ),
				'CC-BY-SA'    => __( 'CC-BY-SA', 'wp-content-pilot' ),
				'CC0'    => __( 'CC0', 'wp-content-pilot' ),
				'categories'    => __( 'Categories', 'wp-content-pilot' ),
				'duration'    => __( 'Duration', 'wp-content-pilot' ),
				'in-progress'    => __( 'In progress', 'wp-content-pilot' ),
				'minimum_likes'    => __( 'Minimum likes', 'wp-content-pilot' ),
				'trending'    => __( 'Trending', 'wp-content-pilot' ),
				'upload_date'    => __( 'Upload date', 'wp-content-pilot' ),
			),
			'default' => 'categories',
		) );

		echo WPCP_HTML::select_input( array(
			'name'    => '_vimeo_sort',
            'label'   => __( 'Vimeo sort', 'wp-content-pilot' ),
            'tooltip' => __( 'The way to sort the results.', 'wp-content-pilot' ),
			'options' => array(
				'alphabetical' => __( 'Alphabetical', 'wp-content-pilot' ),
				'comments'      => __( 'Comments', 'wp-content-pilot' ),
				'date'     => __( 'Date', 'wp-content-pilot' ),
				'duration' => __( 'Duration', 'wp-content-pilot' ),
				'likes'    => __( 'Likes', 'wp-content-pilot' ),
				'plays'    => __( 'Plays', 'wp-content-pilot' ),
				'relevant'    => __( 'Relevant', 'wp-content-pilot' ),
			),
		) );

		

		echo WPCP_HTML::end_double_columns();

	}

	/**
	 * @param $campaign_id
	 * @param $posted
	 */
	public function save_campaign_meta( $campaign_id, $posted ) {
		wpcp_update_post_meta( $campaign_id, '_vimeo_sort_direction', empty( $posted['_vimeo_sort_direction'] ) ? '' : sanitize_text_field( $posted['_vimeo_sort_direction'] ) );
		wpcp_update_post_meta( $campaign_id, '_vimeo_filter', empty( $posted['_vimeo_filter'] ) ? '' : sanitize_text_field( $posted['_vimeo_filter'] ) );
		wpcp_update_post_meta( $campaign_id, '_vimeo_sort', empty( $posted['_vimeo_sort'] ) ? '' : sanitize_text_field( $posted['_vimeo_sort'] ) );
		
	}

	/**
	 * @param $section
	 *
	 * @return array
	 * @since 1.2.0
	 */
	public function get_setting_section( $sections ) {
		$sections[] = [
			'id'    => 'wpcp_settings_vimeo',
			'title' => __( 'Vimeo Settings', 'wp-content-pilot' )
		];

		return $sections;
	}

	/**
	 * @param $fields
	 *
	 * @return array
	 * @since 1.2.0
	 */
	public function get_setting_fields( $fields ) {
		$fields['wpcp_settings_vimeo'] = [
			array(
				'name'    => 'access_token',
				'label'   => __( 'Vimeo Access Token', 'wp-content-pilot' ),
				'desc'    => sprintf( __( 'Vimeo campaigns won\'t run without Access Token key. <a href="%s" target="_blank">Create a Vimeo APP and get Access Token from here</a>.', 'wp-content-pilot' ), 'https://developer.vimeo.com/api/guides/start' ),
				'type'    => 'password',
				'default' => ''
			),
		];

		return $fields;
    }
    
    /**
     * @param array $tags
     * 
     * @return string
     * @since 1.2.1
     */
    public function get_vimeo_tags($tags = array()){
        
        $tagstext = '';
        
        if(!empty($tags)){
            $tagarray = array();
            foreach($tags as $tag){
                $tagarray[] = trim($tag->tag);
            }
            if(!empty($tagarray)){
                $tagstext = implode(',', $tagarray);
            }
        }

        return $tagstext;

    }

	/**
	 * @return mixed|void
	 * @throws ErrorException
	 */
	public function get_post( $campaign_id ) {
		//if api not set bail
        $access_token = wpcp_get_settings( 'access_token', 'wpcp_settings_vimeo', '' );

		if ( empty( $access_token ) ) {
			wpcp_disable_campaign( $campaign_id );
			$notice = __( 'The Vimeo Access Token is not set so the campaign won\'t run, disabling campaign.', 'wp-content-pilot' );
			wpcp_logger()->error( $notice, $campaign_id );

			return new WP_Error( 'missing-data', $notice );
		}

		//$source_type = wpcp_get_post_meta( $campaign_id, '_vimeo_sort_direction', 'asc' );
		
        $sources = $this->get_campaign_meta( $campaign_id );

        if ( empty( $sources ) ) {
            return new WP_Error( 'missing-data', __( 'Campaign do not have keyword to proceed, please set keyword', 'wp-content-pilot' ) );
        }
		

		foreach ( $sources as $source ) {
			//get links from database
			$links = $this->get_links( $source, $campaign_id );
			if ( empty( $links ) ) {
				wpcp_logger()->debug( 'No generated links now need to generate new links', $campaign_id );
				$this->discover_links( $campaign_id, $source );
				$links = $this->get_links( $source, $campaign_id );
			}

			foreach ( $links as $link ) {
                wpcp_logger()->debug( sprintf( 'Vimeo link#[%s]', $link->url ), $campaign_id );
                

				$this->update_link( $link->id, [ 'status' => 'failed' ] );
                $headerauth = array(
                    "authorization: Bearer ".$access_token
                );
                $curl = $this->setup_curl();
                $curl->setOpt(CURLOPT_HTTPHEADER, $headerauth);
                $endpoint = $link->url;
                
				$curl->get( $endpoint );

				if ( $curl->error ) {
					wpcp_logger()->warning( sprintf( 'Request error in grabbing video details error [ %s ]', $curl->errorMessage ), $campaign_id );
					continue;
				}

                $item = $curl->response;



				$description = wpcp_remove_unauthorized_html( wpcp_remove_emoji( $item->description ) );

                $image_url = '';
				if(isset($item->pictures->sizes) && !empty($item->pictures->sizes)){
                    $size = end($item->pictures->sizes);
                    if($size->link!=''){
                        $image_url = $size->link;
                    }
                }
                
                

                //vimeo pro version transcript
                $transcript = '';
				if ( function_exists( 'wpcp_pro_get_vimeo_transcript' ) ) {
					$transcript = wpcp_pro_get_vimeo_transcript( $link );
				}


				$check_clean_title = wpcp_get_post_meta( $campaign_id, '_clean_title', 'off' );

				if ( 'on' == $check_clean_title ) {
					$title = wpcp_clean_title( $link->title );
				} else {
					$title = html_entity_decode( $link->title, ENT_QUOTES );
				}

				$article = array(
					'title'             => $title,
					'author'            => $item->user->name,
					'image_url'         => $image_url,
					'excerpt'           => $description,
					'language'          => '',
					'content'           => $description,
					'source_url'        => $item->link,
					'published_at'      => date( 'Y-m-d H:i:s', strtotime( @$item->created_time ) ),
					'user_name'         => sanitize_text_field( @$item->user->name ),
					'user_link'         => @$item->user->link ,
					'user_bio'          => @$item->user->bio ,
					'user_short_bio'    => @$item->user->short_bio,
					'tags'              => $this->get_vimeo_tags( @$item->tags ),
					'duration'          => $this->convert_vimeo_duration( @$item->duration ),
					'like_count'        => intval( @$item->metadata->connections->likes->total ),
					'comment_count'     => intval( @$item->metadata->connections->comments->total ),
					'embed_html'        => @$item->embed->html,
					'transcript'        => $transcript,
                );
                


				$this->update_link( $link->id, [ 'status' => 'success' ] );
				wpcp_logger()->info( 'Vimeo processed from campaign', $campaign_id );

				return $article;
			}
		}

		$log_url = admin_url( '/edit.php?post_type=wp_content_pilot&page=wpcp-logs' );

		return new WP_Error( 'campaign-error', __( sprintf( 'No vimeo article generated check <a href="%s">log</a> for details.', $log_url ), 'wp-content-pilot' ) );
	}

	/**
	 * Discover new youtube links
	 *
	 * @param $campaign_id
	 * @param $source
	 *
	 * @return bool
	 * @since 1.2.0
	 */
	protected function discover_links( $campaign_id, $source ) {
        
		$sort_direction     = wpcp_get_post_meta( $campaign_id, '_vimeo_sort_direction', 'asc' );
		$filter      = wpcp_get_post_meta( $campaign_id, '_vimeo_filter', 'categories' );
		$sort  = wpcp_get_post_meta( $campaign_id, '_vimeo_sort', 'date' );
		$access_token      = wpcp_get_settings( 'access_token', 'wpcp_settings_vimeo', 'any' );
		$token_key       = sanitize_key( '_page_' . $campaign_id . '_' . md5( $source ) );
        $page = wpcp_get_post_meta( $campaign_id, $token_key, 1 );
        $per_page = 50;


		$query_args = array(
			'direction'     => $sort_direction,
			'filter'        => $filter,
			'query'         => $source,
			'per_page'      => $per_page,
			'page'          => $page,
        );
        
		$endpoint   = 'https://api.vimeo.com/videos';


		$endpoint = add_query_arg( $query_args, $endpoint );
		wpcp_logger()->debug( sprintf( 'Requesting urls from Vimeo [%s]', $endpoint ) );

        $headerauth = array(
            "authorization: Bearer ".$access_token
        );

		$curl = $this->setup_curl();
        
        $curl->setOpt(CURLOPT_HTTPHEADER, $headerauth);
        
        $curl->get( $endpoint );
        

		if ( $curl->isError() ) {
			$response      = json_decode( $curl->getRawResponse() );
			$error_message = array_pop( $response->error->errors );
			$message       = sprintf( __( 'Vimeo api request failed response [%s]', 'wp-content-pilot' ), $error_message->message );
			wpcp_logger()->error( $message, $campaign_id );
			return false;
		}


		$response = $curl->response;

		$items = $response->data;
		if ( empty( $items ) ) {
			$this->deactivate_key( $campaign_id, $source );
			return false;
		}

		$links = [];
		foreach ( $items as $item ) {


			$url   = esc_url( 'https://api.vimeo.com' . $item->uri );
            
            $title = ! empty( $item->name ) ? $item->name : '';
			if ( $title == 'Private video' ) {
				continue;
			}
			if ( wpcp_is_duplicate_url( $url ) ) {
				continue;
			}
			$skip = apply_filters( 'wpcp_skip_duplicate_title', true, $title, $campaign_id );
			if ( $skip ) {
				continue;
			}
			$links[] = [
				'title'   => wpcp_remove_emoji( $title ),
				'url'     => $url,
				'for'     => $source,
				'camp_id' => $campaign_id,
			];
        }
        

		$total_inserted = $this->inset_links( $links );

		wpcp_update_post_meta( $campaign_id, $token_key, $page+1 );
		wpcp_logger()->info( sprintf( 'Total found links [%d] and accepted [%d]', count( $links ), $total_inserted ), $campaign_id );

		return true;
	}

	/**
	 * since 1.0.0
	 *
	 * @param $youtube_time
	 *
	 * @return string
	 */
	public function convert_vimeo_duration( $youtube_time ) {
		preg_match_all( '/(\d+)/', $youtube_time, $parts );

		// Put in zeros if we have less than 3 numbers.
		if ( count( $parts[0] ) == 1 ) {
			array_unshift( $parts[0], "0", "0" );
		} elseif ( count( $parts[0] ) == 2 ) {
			array_unshift( $parts[0], "0" );
		}

		$sec_init         = $parts[0][2];
		$seconds          = $sec_init % 60;
		$seconds_overflow = floor( $sec_init / 60 );

		$min_init         = $parts[0][1] + $seconds_overflow;
		$minutes          = ( $min_init ) % 60;
		$minutes_overflow = floor( ( $min_init ) / 60 );

		$hours = $parts[0][0] + $minutes_overflow;

		if ( $hours != 0 ) {
			return $hours . ':' . $minutes . ':' . $seconds;
		} else {
			return $minutes . ':' . $seconds;
		}
	}




	public function admin_notice() {
		global $current_screen;
		global $post;
		if ( $current_screen->base == 'post' && 'wp_content_pilot' == $current_screen->post_type && 'vimeo' == get_post_meta( $post->ID, '_campaign_type', true ) ) {
			$access_token = wpcp_get_settings( 'access_token', 'wpcp_settings_vimeo', 'any' );
			if ( ! empty( $access_token ) ) {
				return;
			}

			$class   = 'notice notice-error';
			$message = __( 'Vimeo campaign wont run because you did not set the Access Token yet.', 'wp-content-pilot' );
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		}
	}

	/**
	 * Main WPCP_Vimeo Instance.
	 *
	 * Ensures only one instance of WPCP_Vimeo is loaded or can be loaded.
	 *
	 * @return WPCP_Vimeo Main instance
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

WPCP_Vimeo::instance();
