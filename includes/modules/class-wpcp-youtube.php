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
		//option fields
		add_action( 'wpcp_youtube_campaign_options_meta_fields', 'wpcp_keyword_suggestion_field' );
		add_action( 'wpcp_youtube_campaign_options_meta_fields', 'wpcp_keyword_field' );

		parent::__construct( 'youtube' );

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
			'rating'         => __( 'Video rating', 'wp-content-pilot' ),
			'view_count'     => __( 'Total Views', 'wp-content-pilot' ),
			'like_count'     => __( 'Total Likes', 'wp-content-pilot' ),
			'dislike_count'  => __( 'Total Dislikes', 'wp-content-pilot' ),
			'favorite_count' => __( 'Total Favourites', 'wp-content-pilot' ),
			'comment_count'  => __( 'Total Comments', 'wp-content-pilot' ),
			'embed_html'     => __( 'HTML Embed Code ', 'wp-content-pilot' ),
			'download_url'   => __( 'Video Download url', 'wp-content-pilot' ),
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
				'global'   => __( 'Global', 'wp-content-pilot' ),
				'playlist' => __( 'From Playlist', 'wp-content-pilot' ),
				'channel'  => __( 'From Channel', 'wp-content-pilot' ),
			),
			'default' => 'global',
		) );

		echo WPCP_HTML::text_input( array(
			'name'        => '_youtube_playlist_id',
			'placeholder' => __( 'Example: PLiMD4qj5M_C2DLLi00-D2jnHt9eGPNqgs', 'wp-content-pilot' ),
			'label'       => __( 'Youtube Playlist/Channel ID', 'wp-content-pilot' ),
			'tooltip'     => __( 'eg. playlist id is PLiMD4qj5M_C2DLLi00-D2jnHt9eGPNqgs', 'wp-content-pilot' ),
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
				'any'            => __( 'Any', 'wp-content-pilot' ),
				'creativeCommon' => __( 'Creative Common', 'wp-content-pilot' ),
				'youtube'        => __( 'Standard', 'wp-content-pilot' ),
			)
		) );

		echo WPCP_HTML::select_input( array(
			'name'    => '_youtube_duration',
			'label'   => __( 'Video Duration', 'wp-content-pilot' ),
			'default' => 'any',
			'options' => array(
				'any'    => __( 'Any', 'wp-content-pilot' ),
				'long'   => __( 'Long (longer than 20 minutes)', 'wp-content-pilot' ),
				'medium' => __( 'Medium (between four and 20 minutes)', 'wp-content-pilot' ),
				'short'  => __( 'Short (less than four minutes)', 'wp-content-pilot' ),
			)
		) );

		echo WPCP_HTML::select_input( array(
			'name'    => '_youtube_definition',
			'label'   => __( 'Video Definition', 'wp-content-pilot' ),
			'default' => 'any',
			'options' => array(
				'any'      => __( 'Any', 'wp-content-pilot' ),
				'high'     => __( 'High', 'wp-content-pilot' ),
				'standard' => __( 'Standard', 'wp-content-pilot' ),
			)
		) );

		echo WPCP_HTML::select_input( array(
			'name'    => '_youtube_video_type',
			'label'   => __( 'Video Type', 'wp-content-pilot' ),
			'default' => 'any',
			'options' => array(
				'any'     => __( 'Any', 'wp-content-pilot' ),
				'episode' => __( 'Episode', 'wp-content-pilot' ),
				'movie'   => __( 'Movie', 'wp-content-pilot' ),
			)
		) );

		echo WPCP_HTML::select_input( array(
			'name'    => '_youtube_safe_search',
			'label'   => __( 'Safe Search', 'wp-content-pilot' ),
			'default' => 'moderate',
			'options' => array(
				'moderate' => __( 'Moderate', 'wp-content-pilot' ),
				'none'     => __( 'None (will not filter)', 'wp-content-pilot' ),
				'strict'   => __( 'Strict (Exclude all restricted content)', 'wp-content-pilot' ),
			)
		) );

		echo WPCP_HTML::end_double_columns();

		echo WPCP_HTML::checkbox_input( array(
			'label' => __( 'Youtube - Auto hyperlink urls within the description', 'wp-content-pilot' ),
			'name'  => '_youtube_description_hyperlink',
		) );

		echo WPCP_HTML::checkbox_input( array(
			'label' => __( 'Post only live videos only', 'wp-content-pilot' ),
			'name'  => '_youtube_live_videos',
		) );

		echo WPCP_HTML::checkbox_input( array(
			'label' => __( 'Post videos only with closed captions', 'wp-content-pilot' ),
			'name'  => '_youtube_closed_captions',
		) );
	}

	/**
	 * @param $campaign_id
	 * @param $posted
	 */
	public function save_campaign_meta( $campaign_id, $posted ) {
		wpcp_update_post_meta( $campaign_id, '_youtube_search_type', empty( $posted['_youtube_search_type'] ) ? '' : sanitize_text_field( $posted['_youtube_search_type'] ) );
		wpcp_update_post_meta( $campaign_id, '_youtube_playlist_id', empty( $posted['_youtube_playlist_id'] ) ? '' : sanitize_text_field( $posted['_youtube_playlist_id'] ) );
		wpcp_update_post_meta( $campaign_id, '_youtube_category', empty( $posted['_youtube_category'] ) ? '' : sanitize_text_field( $posted['_youtube_category'] ) );
		wpcp_update_post_meta( $campaign_id, '_youtube_search_orderby', empty( $posted['_youtube_search_orderby'] ) ? '' : sanitize_text_field( $posted['_youtube_search_orderby'] ) );
		wpcp_update_post_meta( $campaign_id, '_youtube_license', empty( $posted['_youtube_license'] ) ? '' : sanitize_text_field( $posted['_youtube_license'] ) );
		wpcp_update_post_meta( $campaign_id, '_youtube_duration', empty( $posted['_youtube_duration'] ) ? '' : sanitize_text_field( $posted['_youtube_duration'] ) );
		wpcp_update_post_meta( $campaign_id, '_youtube_definition', empty( $posted['_youtube_definition'] ) ? '' : sanitize_text_field( $posted['_youtube_definition'] ) );
		wpcp_update_post_meta( $campaign_id, '_youtube_video_type', empty( $posted['_youtube_video_type'] ) ? '' : sanitize_text_field( $posted['_youtube_video_type'] ) );
		wpcp_update_post_meta( $campaign_id, '_youtube_description_hyperlink', empty( $posted['_youtube_description_hyperlink'] ) ? '' : sanitize_text_field( $posted['_youtube_description_hyperlink'] ) );
		wpcp_update_post_meta( $campaign_id, '_youtube_safe_search', empty( $posted['_youtube_safe_search'] ) ? '' : sanitize_text_field( $posted['_youtube_safe_search'] ) );
		wpcp_update_post_meta( $campaign_id, '_youtube_live_videos', empty( $posted['_youtube_live_videos'] ) ? '' : sanitize_key( $posted['_youtube_live_videos'] ) );
		wpcp_update_post_meta( $campaign_id, '_youtube_closed_captions', empty( $posted['_youtube_closed_captions'] ) ? '' : sanitize_key( $posted['_youtube_closed_captions'] ) );
	}

	/**
	 * @param $sections
	 *
	 * @return array
	 * @since 1.2.0
	 */
	public function get_setting_section( $sections ) {
		$sections[] = [
			'id'    => 'wpcp_settings_youtube',
			'title' => __( 'Youtube', 'wp-content-pilot' )
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
		$fields['wpcp_settings_youtube'] = [
			array(
				'name'    => 'api_key',
				'label'   => __( 'Youtube API key', 'wp-content-pilot' ),
				'desc'    => sprintf( __( 'Youtube campaigns won\'t run without API key. <a href="%s" target="_blank">Learn how to get one</a>.', 'wp-content-pilot' ), 'https://www.pluginever.com/docs/wp-content-pilot/set-up-youtube-api-key-for-wp-content-pilot/' ),
				'type'    => 'password',
				'default' => ''
			),
		];

		return $fields;
	}

	/**
	 * @param $campaign_id
	 *
	 * @return mixed|void
	 * @throws ErrorException
	 * @since 1.2.0
	 */
	public function get_post( $campaign_id ) {
		//if api not set bail
		$api_key = wpcp_get_settings( 'api_key', 'wpcp_settings_youtube', '' );
		if ( empty( $api_key ) ) {
			wpcp_disable_campaign( $campaign_id );
			$notice = __( 'The YouTube api key is not set so the campaign won\'t run, disabling campaign.', 'wp-content-pilot' );
			wpcp_logger()->error( $notice, $campaign_id );

			return new WP_Error( 'missing-data', $notice );
		}
		wpcp_logger()->info( __( 'Loaded Youtube campaign', 'wp-content-pilot' ), $campaign_id );

		wpcp_logger()->info( __( 'Checking youtube search type...', 'wp-content-pilot' ), $campaign_id );

		$source_type = wpcp_get_post_meta( $campaign_id, '_youtube_search_type', 'global' );
		if ( $source_type == "playlist" || $source_type == "channel" ) {
			$sources = $this->get_campaign_meta( $campaign_id, '_youtube_playlist_id' );
			if ( empty( $sources ) ) {
				return new WP_Error( 'missing-data', __( 'Campaign do not have playlist URL to proceed, please set playlist URL', 'wp-content-pilot' ) );
			}
		} else {
			$sources = $this->get_campaign_meta( $campaign_id );
			if ( empty( $sources ) ) {
				return new WP_Error( 'missing-data', __( 'Campaign do not have keyword to proceed, please set keyword', 'wp-content-pilot' ) );
			}
		}

		foreach ( $sources as $source ) {
			//get links from database
			wpcp_logger()->info( __( 'Checking for links in store...', 'wp-content-pilot' ), $campaign_id );

			if ( $this->is_deactivated_key( $campaign_id, $source ) ) {
				$message = sprintf( __( 'This keyword deactivated for 1 hr because last time could not find any article with url [%s]', 'wp-content-pilot' ), $source );
				wpcp_logger()->info( $message, $campaign_id );
				continue;
			}

			wpcp_logger()->info( __( 'Checking cached links in store', 'wp-content-pilot' ), $campaign_id );

			$links = $this->get_links( $source, $campaign_id );
			if ( empty( $links ) ) {
				wpcp_logger()->info( __( 'No cached links in store. Generating new links...', 'wp-content-pilot' ), $campaign_id );
				$this->discover_links( $campaign_id, $source );
				$links = $this->get_links( $source, $campaign_id );

			}

			wpcp_logger()->info( __( 'Looping through cached links for article', 'wp-content-pilot' ), $campaign_id );

			foreach ( $links as $link ) {
				wpcp_logger()->info( sprintf( __( 'Youtube link#[%s]', 'wp-content-pilot' ), $link->url ), $campaign_id );
				$link_parts = explode( 'v=', $link->url );
				$video_id   = $link_parts[1];

				$this->update_link( $link->id, [ 'status' => 'failed' ] );

				wpcp_logger()->info( __( 'Making request for getting youtube video content', 'wp-content-pilot' ), $campaign_id );
				$curl     = $this->setup_curl();
				$endpoint = "https://www.googleapis.com/youtube/v3/videos?id={$video_id}&key={$api_key}&part=id,snippet,contentDetails,statistics,player";
				$curl->get( $endpoint );

				if ( $curl->error ) {
					wpcp_logger()->error( sprintf( __( 'Request error in grabbing video details error [ %s ]', 'wp-content-pilot' ), $curl->errorMessage ), $campaign_id );
					continue;
				}

				wpcp_logger()->info( __( 'Extracting article content from response', 'wp-content-pilot' ), $campaign_id );
				$items = $curl->response->items;
				$item  = array_pop( $items );

				wpcp_logger()->info( __( 'Removing unauthorized html content from description', 'wp-content-pilot' ), $campaign_id );
				$description = wpcp_remove_unauthorized_html( wpcp_remove_emoji( $item->snippet->description ) );

				wpcp_logger()->info( __( 'Extracting thumbnail', 'wp-content-pilot' ), $campaign_id );
				$image_url = '';
				if ( ! empty( $item->snippet->thumbnails ) && is_object( $item->snippet->thumbnails ) ) {
					$last_image = end( $item->snippet->thumbnails );
					$image_url  = ! empty( $last_image->url ) ? esc_url( $last_image->url ) : '';
				}

				wpcp_logger()->info( __( 'Extracting transcript', 'wp-content-pilot' ), $campaign_id );
				$transcript = '';
				if ( function_exists( 'wpcp_pro_get_youtube_transcript' ) ) {
					$transcript = wpcp_pro_get_youtube_transcript( $link );
				}

				wpcp_logger()->info( __( 'Extracting hyperlink description', 'wp-content-pilot' ), $campaign_id );
				$hyperlink_description = wpcp_get_post_meta( $campaign_id, '_youtube_description_hyperlink', '' );
				if ( 'on' == $hyperlink_description ) {
					$description = wpcp_hyperlink_text( $description );
				}

				$check_clean_title = wpcp_get_post_meta( $campaign_id, '_clean_title', 'off' );

				if ( 'on' == $check_clean_title ) {
					wpcp_logger()->info( __( 'Cleaning title', 'wp-content-pilot' ), $campaign_id );
					$title = wpcp_clean_title( $link->title );
				} else {
					$title = html_entity_decode( $link->title, ENT_QUOTES );
				}

				wpcp_logger()->info( __( 'Preparing different parts for article', 'wp-content-pilot' ), $campaign_id );

				$article = array(
					'title'          => $title,
					'author'         => $item->snippet->channelTitle,
					'image_url'      => $image_url,
					'excerpt'        => $description,
					'language'       => '',
					'content'        => $description,
					'source_url'     => $link->url,
					'published_at'   => date( 'Y-m-d H:i:s', strtotime( @$item->snippet->publishedAt ) ),
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
					'download_url'   => 'https://www.youtubepp.com/watch?v=' . $item->id,
				);

				$rating = ! empty( @$item->statistics->likeCount ) ? intval( @$item->statistics->likeCount ) / ( intval( @$item->statistics->likeCount ) + intval( @$item->statistics->dislikeCount ) ) : 0;
				$rating = ! empty( $rating ) ? $rating * 5 : 0;
				$rating = ! empty( $rating ) ? number_format( $rating, 2 ) : 0;

				$article['rating'] = $rating;

				$this->update_link( $link->id, [ 'status' => 'success' ] );
				wpcp_logger()->info( __( 'Article processed from campaign', 'wp-content-pilot' ), $campaign_id );

				return $article;
			}
		}

		$log_url = admin_url( '/edit.php?post_type=wp_content_pilot&page=wpcp-logs' );

		return new WP_Error( 'campaign-error', __( sprintf( 'No youtube article generated check <a href="%s">log</a> for details.', $log_url ), 'wp-content-pilot' ) );
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
		$category     = wpcp_get_post_meta( $campaign_id, '_youtube_category', 'all' );
		$orderby      = wpcp_get_post_meta( $campaign_id, '_youtube_search_orderby', 'relevance' );
		$search_type  = wpcp_get_post_meta( $campaign_id, '_youtube_search_type', 'global' );
		$playlist_id  = wpcp_get_post_meta( $campaign_id, '_youtube_playlist_id', '' );
		$license_type = wpcp_get_post_meta( $campaign_id, '_youtube_license', '' );
		$duration     = wpcp_get_post_meta( $campaign_id, '_youtube_duration', 'any' );
		$definition   = wpcp_get_post_meta( $campaign_id, '_youtube_definition', 'any' );
		$video_type   = wpcp_get_post_meta( $campaign_id, '_youtube_video_type', 'any' );
		$api_key      = wpcp_get_settings( 'api_key', 'wpcp_settings_youtube', 'any' );

		$token_key       = sanitize_key( '_page_' . $campaign_id . '_' . md5( $source ) );
		$next_page_token = wpcp_get_post_meta( $campaign_id, $token_key, '' );
		$safe_search     = wpcp_get_post_meta( $campaign_id, '_youtube_safe_search', 'moderate' );
		$live_videos     = wpcp_get_post_meta( $campaign_id, '_youtube_live_videos', 'no' );
		$closed_captions = wpcp_get_post_meta( $campaign_id, '_youtube_closed_captions', 'no' );

		$query_args = array(
			'part'              => 'snippet',
			'type'              => 'video',
			'key'               => $api_key,
			'maxResults'        => 50,
			'q'                 => urlencode( $source ),
			'category'          => $category,
			'videoEmbeddable'   => 'true',
			'videoType'         => $video_type,
			'relevanceLanguage' => 'en',
			'videoDuration'     => $duration,
			'videoLicense'      => $license_type,
			'videoDefinition'   => $definition,
			'order'             => $orderby,
			'pageToken'         => $next_page_token,
			'safeSearch'        => $safe_search
		);
		if ( 'on' == $live_videos ) {
			$query_args['eventType'] = 'live';
		}

		if ( 'on' == $closed_captions ) {
			$query_args['videoCaption'] = 'closedCaption';
		}

		$endpoint = 'https://www.googleapis.com/youtube/v3/search';
		if ( $search_type === 'playlist' && ! empty( $playlist_id ) ) {
			$query_args['playlistId'] = $playlist_id;
			unset( $query_args['q'] );
			$endpoint = 'https://www.googleapis.com/youtube/v3/playlistItems';
		} elseif ( $search_type === 'channel' && ! empty( $playlist_id ) ) {
			$query_args['channelId'] = $playlist_id;
			unset( $query_args['q'] );
		}

		$endpoint = add_query_arg( $query_args, $endpoint );
		wpcp_logger()->info( sprintf( __( 'Requesting urls from Youtube [%s]', 'wp-content-pilot' ), preg_replace( '/key=([^&]+)/m', 'key=X', $endpoint ) ) );

		$curl = $this->setup_curl();
		$curl->get( $endpoint );

		if ( $curl->isError() ) {
			$response      = json_decode( $curl->getRawResponse() );
			$error_message = array_pop( $response->error->errors );
			$message       = sprintf( __( 'Youtube api request failed response [%s]', 'wp-content-pilot' ), $error_message->message );
			wpcp_logger()->error( $message, $campaign_id );

			return false;
		}

		wpcp_logger()->info( __( 'Extracting response from request', 'wp-content-pilot' ), $campaign_id );
		$response = $curl->response;

		if ( isset( $response->nextPageToken ) && trim( $response->nextPageToken ) != '' ) {
			wpcp_update_post_meta( $campaign_id, $token_key, $response->nextPageToken );
		} else {
			$this->deactivate_key( $campaign_id, $source );
		}

		$items = $response->items;
		if ( empty( $items ) ) {
			$this->deactivate_key( $campaign_id, $source );

			return false;
		}

		$links = [];
		wpcp_logger()->info( __( 'Finding links from response and storing.....', 'wp-content-pilot' ), $campaign_id );
		foreach ( $items as $item ) {
			$video_id = '';
			if ( stristr( $endpoint, 'playlistItems' ) ) {
				$video_id = $item->snippet->resourceId->videoId;
			} else {
				$video_id = $item->id->videoId;
			}

			$url   = esc_url( 'https://www.youtube.com/watch?v=' . $video_id );
			$title = ! empty( $item->snippet->title ) ? $item->snippet->title : '';
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

		wpcp_update_post_meta( $campaign_id, $token_key, @$response->nextPageToken );

		wpcp_logger()->info( sprintf( __( 'Total found links [%d] and accepted [%d] and rejected [%d]', 'wp-content-pilot' ), count( $links ), $total_inserted, ( count( $links ) - $total_inserted ) ), $campaign_id );

		return true;
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
			$message = __( 'Youtube campaign wont run because you did not set the API details yet.', 'wp-content-pilot' );
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
