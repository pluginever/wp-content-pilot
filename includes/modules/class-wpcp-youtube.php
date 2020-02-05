<?php
// don't call the file directly
defined( 'ABSPATH' ) || exit();

class WPCP_Youtube extends WPCP_Module {

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
		add_filter( 'wpcp_modules', array( $this, 'register_module' ) );

		//option fields
		add_action( 'wpcp_youtube_campaign_options_meta_fields', 'wpcp_keyword_suggestion_field' );
		add_action( 'wpcp_youtube_campaign_options_meta_fields', 'wpcp_keyword_field' );
		add_action( 'wpcp_youtube_campaign_options_meta_fields', array( $this, 'add_campaign_option_fields' ) );

		//admin notice
		add_action( 'admin_notices', array( $this, 'admin_notice' ) );

		add_action( 'wpcp_youtube_update_campaign_settings', array( $this, 'save_campaign_meta' ), 10, 2 );
	}

	/**
	 * @return string
	 * @since 1.2.0
	 */
	public function get_campaign_type() {
		return 'youtube';
	}

	/**
	 * @param $modules
	 *
	 * @return array
	 * @since 1.2.0
	 */
	public function register_module( $modules ) {
		$modules['youtube'] = __CLASS__;

		return $modules;
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
			'title'          => __( 'Title', 'wp-content-pilot' ),
			'excerpt'        => __( 'Summary', 'wp-content-pilot' ),
			'content'        => __( 'Content', 'wp-content-pilot' ),
			'image_url'      => __( 'Main image url', 'wp-content-pilot' ),
			'source_url'     => __( 'Source link', 'wp-content-pilot' ),
			'video_id'       => __( 'Video Id', 'wp-content-pilot' ),
			'channel_id'     => __( 'Channel Id', 'wp-content-pilot' ),
			'channel_title'  => __( 'Channel Name', 'wp-content-pilot' ),
			'tags'           => __( 'Video Tags', 'wp-content-pilot' ),
			'duration'       => __( 'Video Duration', 'wp-content-pilot' ),
			'view_count'     => __( 'Total Views', 'wp-content-pilot' ),
			'like_count'     => __( 'Total Likes', 'wp-content-pilot' ),
			'dislike_count'  => __( 'Total Dislikes', 'wp-content-pilot' ),
			'favorite_count' => __( 'Total Favourites', 'wp-content-pilot' ),
			'comment_count'  => __( 'Total Comments', 'wp-content-pilot' ),
			'embed_html'     => __( 'HTML Embed Code ', 'wp-content-pilot' ),
			'transcript'     => __( 'Video transcript available in PRO', 'wp-content-pilot' ),
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
			'name'    => '_youtube_search_type',
			'label'   => __( 'Youtube Search Type', 'wp-content-pilot' ),
			'tooltip' => __( 'Use global search for all result or use specific channel if you want to limit to that channel.', 'wp-content-pilot' ),
			'options' => array(
				'global'  => __( 'Global', 'wp-content-pilot' ),
				'channel' => __( 'From Specific Channel', 'wp-content-pilot' ),
			),
			'default' => 'global',
		) );

		echo WPCP_HTML::text_input( array(
			'name'        => '_youtube_channel_id',
			'placeholder' => __( 'Example: UCIQOOX3ReApm-KTZ66eMVzQ', 'wp-content-pilot' ),
			'label'       => __( 'Youtube Channel ID', 'wp-content-pilot' ),
			'tooltip'     => __( 'eg. channel id is "UCIQOOX3ReApm-KTZ66eMVzQ" for https://www.youtube.com/channel/UCIQOOX3ReApm-KTZ66eMVzQ', 'wp-content-pilot' ),
		) );

		echo WPCP_HTML::select_input( array(
			'name'    => '_youtube_category',
			'label'   => __( 'Youtube Category', 'wp-content-pilot' ),
			'options' => $this->get_youtube_categories(),
			'default' => 'all',
		) );

		echo WPCP_HTML::select_input( array(
			'name'    => '_youtube_search_orderby',
			'label'   => __( 'Youtube Search Order', 'wp-content-pilot' ),
			'options' => array(
				'relevance' => __( 'Relevance', 'wp-content-pilot' ),
				'date'      => __( 'Date', 'wp-content-pilot' ),
				'title'     => __( 'Title', 'wp-content-pilot' ),
				'viewCount' => __( 'View Count', 'wp-content-pilot' ),
				'rating'    => __( 'Rating', 'wp-content-pilot' ),
			),
		) );

		echo WPCP_HTML::select_input( array(
			'name'    => '_youtube_license',
			'label'   => __( 'Video License', 'wp-content-pilot' ),
			'default' => 'any',
			'options' => array(
				'any'            => 'Any',
				'creativeCommon' => 'Creative Common',
				'youtube'        => 'Standard',
			)
		) );

		echo WPCP_HTML::select_input( array(
			'name'    => '_youtube_duration',
			'label'   => __( 'Video Duration', 'wp-content-pilot' ),
			'default' => 'any',
			'options' => array(
				'any'    => 'Any',
				'long'   => 'Long (longer than 20 minutes)',
				'medium' => 'Medium (between four and 20 minutes)',
				'short'  => 'Short (less than four minutes)',
			)
		) );

		echo WPCP_HTML::select_input( array(
			'name'    => '_youtube_definition',
			'label'   => __( 'Video Definition', 'wp-content-pilot' ),
			'default' => 'any',
			'options' => array(
				'any'      => 'Any',
				'high'     => 'High',
				'standard' => 'Standard',
			)
		) );

		echo WPCP_HTML::select_input( array(
			'name'    => '_youtube_video_type',
			'label'   => __( 'Video Type', 'wp-content-pilot' ),
			'default' => 'any',
			'options' => array(
				'any'     => 'Any',
				'episode' => 'Episode',
				'movie'   => 'Movie',
			)
		) );

		echo WPCP_HTML::end_double_columns();

		echo WPCP_HTML::checkbox_input( array(
			'label' => __( 'Auto hyperlink urls within the youtube description', 'wp-content-pilot' ),
			'name'  => '_youtube_description_hyperlink',
		) );
	}

	/**
	 * @param $campaign_id
	 * @param $posted
	 */
	public function save_campaign_meta( $campaign_id, $posted ) {
		wpcp_update_post_meta( $campaign_id, '_youtube_search_type', empty( $posted['_youtube_search_type'] ) ? '' : sanitize_text_field( $posted['_youtube_search_type'] ) );
		wpcp_update_post_meta( $campaign_id, '_youtube_channel_id', empty( $posted['_youtube_channel_id'] ) ? '' : sanitize_text_field( $posted['_youtube_channel_id'] ) );
		wpcp_update_post_meta( $campaign_id, '_youtube_category', empty( $posted['_youtube_category'] ) ? '' : sanitize_text_field( $posted['_youtube_category'] ) );
		wpcp_update_post_meta( $campaign_id, '_youtube_search_orderby', empty( $posted['_youtube_search_orderby'] ) ? '' : sanitize_text_field( $posted['_youtube_search_orderby'] ) );
		wpcp_update_post_meta( $campaign_id, '_youtube_license', empty( $posted['_youtube_license'] ) ? '' : sanitize_text_field( $posted['_youtube_license'] ) );
		wpcp_update_post_meta( $campaign_id, '_youtube_duration', empty( $posted['_youtube_duration'] ) ? '' : sanitize_text_field( $posted['_youtube_duration'] ) );
		wpcp_update_post_meta( $campaign_id, '_youtube_definition', empty( $posted['_youtube_definition'] ) ? '' : sanitize_text_field( $posted['_youtube_definition'] ) );
		wpcp_update_post_meta( $campaign_id, '_youtube_video_type', empty( $posted['_youtube_video_type'] ) ? '' : sanitize_text_field( $posted['_youtube_video_type'] ) );
		wpcp_update_post_meta( $campaign_id, '_youtube_description_hyperlink', empty( $posted['_youtube_description_hyperlink'] ) ? '' : sanitize_text_field( $posted['_youtube_description_hyperlink'] ) );
	}

	/**
	 * @param $section
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
	 * @return mixed|void
	 */
	public function get_post( $keywords = null ) {
		//if api not set bail
		$api_key = wpcp_get_settings( 'api_key', 'wpcp_settings_youtube', '' );
		if ( empty( $api_key ) ) {
			wpcp_disable_campaign( $this->campaign_id );

			$notice = __( 'The YouTube api key is not set by the campaign won\'t run, disabling campaign.', 'wp-content-pilot-pro' );

			wpcp_logger()->error( $notice, $this->campaign_id );

			return new WP_Error( 'missing-data', $notice );
		}

		$last_keyword = wpcp_get_post_meta( $this->campaign_id, '_last_keyword', '' );

		foreach ( $keywords as $keyword ) {
			wpcp_logger()->debug( sprintf( 'Looping through keywords [ %s ]', $keyword ) );
			//if more than 1 then unset last one
			if ( count( $keywords ) > 1 && $last_keyword == $keyword ) {
				wpcp_logger()->debug( sprintf( 'Keywords more than 1 and [ %s ] this keywords used last time so skipping it ', $keyword ),$this->campaign_id );
				continue;
			}



			//get links from database
			$links = $this->get_links( $keyword );
			if ( empty( $links ) ) {
				wpcp_logger()->debug( 'No generated links now need to generate new links',$this->campaign_id );
				$discovered_link = $this->discover_links( $this->campaign_id, $keyword );
				$links           = $this->get_links( $keyword );
			}

			wpcp_logger()->debug( 'Starting to process youtube article',$this->campaign_id );

			wpcp_logger()->info( 'Campaign Started', $this->campaign_id );

			foreach ( $links as $link ) {
				wpcp_logger()->debug( sprintf( 'Youtube link#[%s]', $link->url ),$this->campaign_id );
				$link_parts = explode( 'v=', $link->url );
				$video_id   = $link_parts[1];

				$article = [];
				$this->update_link( $link->id, [ 'status' => 'failed' ] );

				$curl     = $this->setup_curl();
				$endpoint = "https://www.googleapis.com/youtube/v3/videos?id={$video_id}&key={$api_key}&part=id,snippet,contentDetails,statistics,player";
				$curl->get( $endpoint );

				if ( $curl->error ) {
					wpcp_logger()->warning( sprintf( 'Request error in grabbing video details error [ %s ]', $curl->errorMessage ), $this->campaign_id );
					continue;
				}

				$items = @$curl->response->items;
				$item  = array_pop( $items );

				$description = wpcp_remove_unauthorized_html( wpcp_remove_emoji( @$item->snippet->description ) );

				$image_url = '';
				if ( ! empty( $item->snippet->thumbnails ) && is_object( $item->snippet->thumbnails ) ) {
					$last_image = end( $item->snippet->thumbnails );
					$image_url  = @ ! empty( $last_image->url ) ? esc_url( $last_image->url ) : '';
				}

				$transcript = '';
				if ( function_exists( 'wpcp_pro_get_youtube_transcript' ) ) {
					$transcript = wpcp_pro_get_youtube_transcript( $link );
				}

				$hyperlink_description = wpcp_get_post_meta( $this->campaign_id, '_youtube_description_hyperlink', '' );
				if ( 'on' == $hyperlink_description ) {
					$description = wpcp_hyperlink_text( $description );
				}

				$article = array(
					'title'        => wpcp_clean_title( $link->title ),
					'author'       => $item->snippet->channelTitle,
					'image_url'    => $image_url,
					'excerpt'      => $description,
					'language'     => '',
					'content'      => $description,
					'source_url'   => $link->url,
					'published_at' => date( 'Y-m-d H:i:s', strtotime( @$item->snippet->publishedAt ) ),

					'video_id'       => sanitize_key( @$item->id ),
					'channel_id'     => sanitize_key( @$item->snippet->channelId ),
					'channel_title'  => sanitize_text_field( @$item->snippet->channelTitle ),
					'tags'           => implode( ',', (array) @$item->snippet->tags ),
					'duration'       => $this->convert_youtube_duration( @$item->contentDetails->duration ),
					'view_count'     => intval( @$item->statistics->viewCount ),
					'like_count'     => intval( @$item->statistics->likeCount ),
					'dislike_count'  => intval( @$item->statistics->dislikeCount ),
					'favorite_count' => intval( @$item->statistics->favoriteCount ),
					'comment_count'  => intval( @$item->statistics->commentCount ),
					'embed_html'     => @$item->player->embedHtml,
					'transcript'     => $transcript,
				);

				wpcp_logger()->info( 'Successfully generated youtube article',$this->campaign_id );
				wpcp_update_post_meta( $this->campaign_id, '_last_keyword', $keyword );

				return $article;
			}
		}
		return new WP_Error( 'campaign-error', __( 'No youtube article generated check log for details.', 'wp-content-pilot' ) );


	}

	/**
	 * Discover new youtube links
	 *
	 * @param $campaign_id
	 * @param $keyword
	 *
	 * @return bool
	 * @since 1.2.0
	 */
	protected function discover_links( $campaign_id, $keyword ) {
		$category     = wpcp_get_post_meta( $campaign_id, '_youtube_category', 'all' );
		$orderby      = wpcp_get_post_meta( $campaign_id, '_youtube_search_orderby', 'relevance' );
		$search_type  = wpcp_get_post_meta( $campaign_id, '_youtube_search_type', 'global' );
		$channel_id   = wpcp_get_post_meta( $campaign_id, '_youtube_channel_id', '' );
		$license_type = wpcp_get_post_meta( $campaign_id, '_youtube_license', '' );
		$duration     = wpcp_get_post_meta( $campaign_id, '_youtube_duration', 'any' );
		$definition   = wpcp_get_post_meta( $campaign_id, '_youtube_definition', 'any' );
		$video_type   = wpcp_get_post_meta( $campaign_id, '_youtube_video_type', 'any' );
		$api_key      = wpcp_get_settings( 'api_key', 'wpcp_settings_youtube', 'any' );

		$token_key       = sanitize_key( '_page_' . $campaign_id . '_' . md5( $keyword ) );
		$next_page_token = wpcp_get_post_meta( $campaign_id, $token_key, '' );


		$query_args = array(
			'part'              => 'snippet',
			'type'              => 'video',
			'key'               => $api_key,
			'maxResults'        => 50,
			'q'                 => $keyword,
			'category'          => $category,
			'videoEmbeddable'   => 'true',
			'videoType'         => $video_type,
			'relevanceLanguage' => 'en',
			'videoDuration'     => $duration,
			'videoLicense'      => $license_type,
			'videoDefinition'   => $definition,
			'order'             => $orderby,
			'pageToken'         => $next_page_token,
		);
		$endpoint   = 'https://www.googleapis.com/youtube/v3/search';
		if ( $search_type === 'channel' && ! empty( $channel_id ) ) {
			$query_args['playlistId'] = $channel_id;
			$endpoint                 = 'https://www.googleapis.com/youtube/v3/playlistItems';
		}


		$endpoint = add_query_arg( $query_args, $endpoint );
		wpcp_logger()->debug( sprintf( 'Requesting urls from Youtube [%s]', $endpoint ) );
		$curl = $this->setup_curl();
		$curl->get( $endpoint );
		if ( $curl->isError() ) {
			$response      = json_decode( $curl->getRawResponse() );
			$error_message = array_pop( $response->error->errors );
			$message       = sprintf( __( 'Youtube api request failed response [%s]', 'wp-content-pilot' ), $error_message->message );
			wpcp_logger()->error( $message );

			return false;
		}

		$response = $curl->response;
		if ( isset( $response->nextPageToken ) && trim( $response->nextPageToken ) != '' ) {
			wpcp_update_post_meta( $campaign_id, $token_key, $response->nextPageToken );
		} else {
			$this->deactivate_key( $campaign_id, $keyword );
		}

		$items = $response->items;
		if ( empty( $items ) ) {
			$this->deactivate_key( $campaign_id, $keyword );

			return false;
		}

		$inserted = 0;
		foreach ( $items as $item ) {
			$video_id = '';
			if ( stristr( $endpoint, 'playlistItems' ) ) {
				$video_id = $item->snippet->resourceId->videoId;
			} else {
				$video_id = $item->id->videoId;
			}

			$url   = esc_url( 'https://www.youtube.com/watch?v=' . $video_id );
			$title = @ ! empty( $item->snippet->title ) ? @sanitize_text_field( $item->snippet->title ) : '';
			if ( $title == 'Private video' ) {
				continue;
			}
			if ( wpcp_is_duplicate_url( $url ) ) {
				continue;
			}
			if ( wpcp_is_duplicate_title( $title ) ) {
				continue;
			}

			if ( false != $this->insert_link( array(
					'title'   => wpcp_remove_emoji( $title ),
					'url'     => $url,
					'keyword' => $keyword,
				) ) ) {
				$inserted ++;
			}
		}

		wpcp_logger()->info( sprintf( 'Total found links [%d] and accepted [%d]', count( $items ), $inserted ), $campaign_id );

		return $inserted;
	}

	/**
	 * since 1.0.0
	 *
	 * @param $youtube_time
	 *
	 * @return string
	 */
	public function convert_youtube_duration( $youtube_time ) {
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

	/**
	 * Get all youtube categories
	 *
	 * @return array
	 * @since 1.0.0
	 *
	 */
	public function get_youtube_categories() {
		$categories = array(
			'all' => 'All',
			'1'   => 'Film & Animation',
			'2'   => 'Autos & Vehicles',
			'10'  => 'Music',
			'15'  => 'Pets & Animals',
			'17'  => 'Sports',
			'18'  => 'Short Movies',
			'19'  => 'Travel & Events',
			'20'  => 'Gaming',
			'21'  => 'Videoblogging',
			'22'  => 'People & Blogs',
			'23'  => 'Comedy',
			'24'  => 'Entertainment',
			'25'  => 'News & Politics',
			'26'  => 'Howto & Style',
			'27'  => 'Education',
			'28'  => 'Science & Technology',
			'29'  => 'Nonprofits & Activism',
			'30'  => 'Movies',
			'31'  => 'Anime/Animation',
			'32'  => 'Action/Adventure',
			'33'  => 'Classics',
			'34'  => 'Comedy',
			'35'  => 'Documentary',
			'36'  => 'Drama',
			'37'  => 'Family',
			'38'  => 'Foreign',
			'39'  => 'Horror',
			'40'  => 'Sci-Fi/Fantasy',
			'41'  => 'Thriller',
			'42'  => 'Shorts',
			'43'  => 'Shows',
			'44'  => 'Trailers'
		);

		return $categories;
	}


	public function admin_notice() {
		global $current_screen;
		global $post;
		if ( $current_screen->base == 'post' && 'wp_content_pilot' == $current_screen->post_type && 'youtube' == get_post_meta( $post->ID, '_campaign_type', true ) ) {
			$api_key = wpcp_get_settings( 'api_key', 'wpcp_settings_youtube', '' );
			if ( ! empty( $api_key ) ) {
				return;
			}

			$class   = 'notice notice-error';
			$message = __( 'Youtube campaign wont run because you did not set the API details yet.', 'sample-text-domain' );
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		}
	}


	/**
	 * Main WPCP_Youtube Instance.
	 *
	 * Ensures only one instance of WPCP_Youtube is loaded or can be loaded.
	 *
	 * @return WPCP_Youtube Main instance
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

WPCP_Youtube::instance();