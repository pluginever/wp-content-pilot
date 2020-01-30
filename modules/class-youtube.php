<?php
/**
 * Youtube Class
 *
 * @package     WP Content Pilot
 * @subpackage  Youtube
 * @copyright   Copyright (c) 2019, MD Sultan Nasir Uddin(manikdrmc@gmail.com)
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */

defined( 'ABSPATH' ) || exit();

class WPCP_Youtube extends WPCP_Campaign {


	protected $api_key;

	/**
	 * WPCP_Youtube constructor.
	 */
	public function __construct() {
		add_filter( 'wpcp_modules', array( $this, 'register_module' ) );
		add_action( 'wpcp_campaign_youtube_options_meta_fields', 'wpcp_keyword_suggestion_field' );
		add_action( 'wpcp_campaign_youtube_options_meta_fields', 'wpcp_keyword_field' );
		add_action( 'wpcp_campaign_youtube_options_meta_fields', array( $this, 'campaign_option_fields' ) );
		add_action( 'wpcp_campaign_youtube_options_meta_fields', 'wpcp_strip_links_field' );
		add_action( 'wpcp_campaign_youtube_options_meta_fields', 'wpcp_featured_image_field' );
		add_action( 'wpcp_campaign_youtube_options_meta_fields', 'wpcp_external_link_field' );
		add_action( 'wpcp_campaign_youtube_options_meta_fields', 'wpcp_featured_image_random_field' );


		add_action( 'wpcp_update_campaign_settings_youtube', array( $this, 'update_campaign_settings' ), 10, 2 );
		add_action( 'wpcp_fetching_campaign_contents', array( $this, 'prepare_contents' ) );

		add_filter( 'wpcp_replace_template_tags', array( $this, 'replace_template_tags' ), 10, 2 );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		add_filter( 'wpcp_campaign_additional_settings_field_args', array(
			$this,
			'additional_settings_fields'
		), 10, 3 );
	}

	/**
	 * Get WPCP_Envato default template tags
	 *
	 * @return string
	 * @since 1.0.0
	 *
	 */
	public static function get_default_template() {
		$template
			= <<<EOT
{embed_html}
<br>{content}
<br> <a href="{source_url}" target="_blank">Source</a>
EOT;

		return $template;
	}

	/**
	 * Register youtube module
	 *
	 * @param $modules
	 *
	 * @return mixed
	 * @since 1.0.0
	 *
	 */
	public function register_module( $modules ) {
		$modules['youtube'] = [
			'title'       => __( 'Youtube', 'wp-content-pilot' ),
			'description' => __( 'Scraps videos based on keywords from youtube', 'wp-content-pilot' ),
			'supports'    => self::get_template_tags(),
			'callback'    => __CLASS__,
		];

		return $modules;
	}

	/**
	 * Supported template tags
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public static function get_template_tags() {
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
		);
	}

	/**
	 * Conditionally show meta fields
	 *
	 * @param $post_id
	 * @param $campaign_type
	 *
	 * @return bool
	 * @since 1.0.0
	 *
	 */
	public function campaign_option_fields( $post ) {

		echo WPCP_HTML::select_input( array(
			'name'    => '_youtube_search_type',
			'label'   => __( 'Search Type', 'wp-content-pilot' ),
			'desc'    => __( 'Use global search for all result or use specific channel if you want to limit to that channel.', 'wp-content-pilot' ),
			'options' => array(
				'global'  => __( 'Global', 'wp-content-pilot' ),
				'channel' => __( 'From Specific Channel', 'wp-content-pilot' ),
			),
			'default' => 'global',
		) );

		echo WPCP_HTML::text_input( array(
			'name'        => '_youtube_channel_id',
			'placeholder' => __( 'Example: UCIQOOX3ReApm-KTZ66eMVzQ', 'wp-content-pilot' ),
			'label'       => __( 'Channel ID', 'wp-content-pilot' ),
			'desc'        => __( 'eg. channel id is "UCIQOOX3ReApm-KTZ66eMVzQ" for https://www.youtube.com/channel/UCIQOOX3ReApm-KTZ66eMVzQ', 'wp-content-pilot' ),
		) );

		echo WPCP_HTML::select_input( array(
			'name'    => '_youtube_category',
			'label'   => __( 'Category', 'wp-content-pilot' ),
			'options' => $this->get_youtube_categories(),
			'default' => 'all',
		) );

		echo WPCP_HTML::select_input( array(
			'name'    => '_youtube_search_orderby',
			'label'   => __( 'Search Order By', 'wp-content-pilot' ),
			'options' => array(
				'relevance' => __( 'Relevance', 'wp-content-pilot' ),
				'date'      => __( 'Date', 'wp-content-pilot' ),
				'title'     => __( 'Title', 'wp-content-pilot' ),
				'viewCount' => __( 'View Count', 'wp-content-pilot' ),
				'rating'    => __( 'Rating', 'wp-content-pilot' ),
			),
		) );

		echo WPCP_HTML::select_input( array(
			'name'    => '_youtube_search_order',
			'label'   => __( 'Search Order', 'wp-content-pilot' ),
			'default' => 'asc',
			'options' => array(
				'asc'  => 'ASC',
				'desc' => 'DESC',
			)
		) );


	}

	/**
	 * Add additional settings option for youtube
	 *
	 * @param $args
	 * @param $type
	 *
	 * @return array
	 * @since 1.0.4
	 *
	 */
	public function additional_settings_fields( $args, $type, $post_id ) {
		if ( 'youtube' != $type ) {
			return $args;
		}

		unset( $args['options']['_remove_images'] );
		unset( $args['options']['_strip_links'] );
		unset( $args['options']['_skip_no_image'] );

		if ( defined( 'WPCP_PRO_VERSION' ) ) {
			$args['options']['_video_transcription'] = __( 'Video transcription as content', 'wp-content-pilot' );
			$video_transcription                     = get_post_meta( $post_id, '_video_transcription', true );
			if ( 'on' == $video_transcription ) {
				$args['value'][] = '_video_transcription';
			}
		}

		return $args;
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

	/**
	 * update campaign settings
	 *
	 * @param $post_id
	 * @param $posted
	 *
	 * @since 1.0.0
	 *
	 */
	public function update_campaign_settings( $post_id, $posted ) {
		$video_transcription = empty( $posted['_video_transcription'] ) ? '' : sanitize_key( $posted['_video_transcription'] );

		update_post_meta( $post_id, '_youtube_search_type', empty( $posted['_youtube_search_type'] ) ? 'global' : sanitize_text_field( $posted['_youtube_search_type'] ) );
		update_post_meta( $post_id, '_youtube_channel_id', empty( $posted['_youtube_channel_id'] ) ? '' : sanitize_text_field( $posted['_youtube_channel_id'] ) );
		update_post_meta( $post_id, '_youtube_category', empty( $posted['_youtube_category'] ) ? 'all' : sanitize_text_field( $posted['_youtube_category'] ) );
		update_post_meta( $post_id, '_youtube_search_orderby', empty( $posted['_youtube_search_orderby'] ) ? '' : sanitize_key( $posted['_youtube_search_orderby'] ) );
		update_post_meta( $post_id, '_youtube_search_order', empty( $posted['_youtube_search_order'] ) ? '' : sanitize_key( $posted['_youtube_search_order'] ) );
		update_post_meta( $post_id, '_video_transcription', $video_transcription );


	}

	/**
	 * Hook in background process and prepare contents
	 *
	 * @param $link
	 *
	 * @return bool|\WP_Error
	 * @since 1.0.0
	 *
	 */
	public function prepare_contents( $link ) {

		if ( 'youtube' != $link->camp_type ) {
			return false;
		}

		$api_key = wpcp_get_settings( 'api_key', 'wpcp_settings_youtube', '' );

		$video_id = $link->raw_content;

		$url = esc_url_raw( "https://www.googleapis.com/youtube/v3/videos?id={$video_id}&key={$api_key}&part=id,snippet,contentDetails,statistics,player" );

		$request = wpcp_remote_get( $url );

		$response = wpcp_retrieve_body( $request );

		if ( is_wp_error( $response ) ) {
			wpcp_update_link( $link->id, array(
				'status' => 'http_error',
			) );
			$this->youtube_api_error_log( $request, $link->camp_id );

			return $response;
		}
		$item = array_pop( $response->items );

		$description = wp_kses_post( $this->remove_imoji( @$item->snippet->description ) );

		$article = array(
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
			'excerpt'        => wp_trim_words( trim( $description ), 55 ),
		);

		$update = wpcp_update_link( $link->id, array(
			'content'     => $description,
			'raw_content' => serialize( $article ),
			'score'       => wpcp_get_read_ability_score( $description ),
			'status'      => 'ready',
		) );

		//if found any database error
		if ( ! $update ) {
			wpcp_update_link( $link->id, array(
				'status' => 'failed'
			) );
		}

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
	 * Replace additional template tags
	 *
	 * @param $content
	 * @param $article
	 *
	 * @return mixed
	 * @since 1.0.0
	 *
	 */
	public function replace_template_tags( $content, $article ) {

		if ( 'youtube' !== $article['campaign_type'] ) {
			return $content;
		}

		$link        = wpcp_get_link( $article['link_id'] );
		$raw_content = maybe_unserialize( $link->raw_content );

		foreach ( $raw_content as $tag => $tag_content ) {
			$content = str_replace( '{' . $tag . '}', $tag_content, $content );
		}

		return $content;
	}

	public function remove_imoji( $text ) {
		$regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
		$text           = preg_replace( $regexEmoticons, '', $text );

		// Match Miscellaneous Symbols and Pictographs
		$regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
		$text         = preg_replace( $regexSymbols, '', $text );

		// Match Transport And Map Symbols
		$regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
		$text           = preg_replace( $regexTransport, '', $text );

		return $text;
	}

	public function setup() {

		$api_key = wpcp_get_settings( 'api_key', 'wpcp_settings_youtube', '' );

		if ( empty( $api_key ) ) {

			$msg = __( 'Youtube API Key is not set, The campaign won\'t work without API Key.', 'wp-content-pilot' );
			wpcp_log( $msg );

			return new \WP_Error( 'invalid-api-settings', $msg );
		}

		$this->api_key = $api_key;

		return true;
	}

	/**
	 * since 1.0.0
	 * @return array|WP_Error
	 */
	public function discover_links() {

		$page        = $this->get_page_number( '' );
		$category    = wpcp_get_post_meta( $this->campaign_id, '_youtube_category', 'all' );
		$orderby     = wpcp_get_post_meta( $this->campaign_id, '_youtube_search_orderby', 'relevance' );
		$search_type = wpcp_get_post_meta( $this->campaign_id, '_youtube_search_type', 'global' );
		$channel_id  = wpcp_get_post_meta( $this->campaign_id, '_youtube_channel_id', '' );

		$maxResults = wpcp_perpage_data_fetch_limit( $this->campaign_id );

		$query_args = array(
			'part'              => 'snippet',
			'type'              => 'video',
			'key'               => $this->api_key,
			'maxResults'        => $maxResults,
			'q'                 => $this->keyword,
			'category'          => $category,
			'videoEmbeddable'   => 'true',
			'videoType'         => 'any',
			'relevanceLanguage' => 'en',
			'videoDuration'     => 'any',
			'order'             => $orderby,
			'pageToken'         => $page,
		);

		if ( $search_type === 'channel' && ! empty( $channel_id ) ) {
			$query_args['channelId'] = $channel_id;
		}

		$request = wpcp_remote_get( 'https://www.googleapis.com/youtube/v3/search', $query_args );

		$response = wpcp_retrieve_body( $request );

		if ( is_wp_error( $response ) ) {
			$error  = $this->youtube_api_error_log( $request, $this->campaign_id );

			return new WP_Error('youtube-api-error', sprintf('Youtube campaign failed because %s', $error));

		}

		$items = $response->items;

		$links = [];

		foreach ( $items as $item ) {

			$image = '';

			$url = esc_url( 'https://www.youtube.com/watch?v=' . $item->id->videoId );

			$title   = @ ! empty( $item->snippet->title ) ? @sanitize_text_field( $item->snippet->title ) : '';
			$content = @ ! empty( $item->snippet->description ) ? @esc_html( $item->snippet->description ) : '';

			if ( ! empty( $item->snippet->thumbnails ) && is_object( $item->snippet->thumbnails ) ) {
				$last_image = end( $item->snippet->thumbnails );

				$image = @ ! empty( $last_image->url ) ? esc_url( $last_image->url ) : '';

			}

			$links[] = array(
				'title'       => $title,
				'content'     => $content,
				'url'         => $url,
				'image'       => $image,
				'raw_content' => $item->id->videoId,
				'score'       => '0',
				'gmt_date'    => gmdate( 'Y-m-d H:i:s', strtotime( $item->snippet->publishedAt ) ),
				'status'      => 'fetched',
			);
		}
		if ( isset( $response->nextPageToken ) ) {
			$this->set_page_number( $response->nextPageToken );
		}

		return $links;

	}

	/**
	 * since 1.0.0
	 *
	 * @param $link
	 *
	 * @return array
	 */
	public function get_post( $link ) {

		$article = array(
			'title'         => $link->title,
			'content'       => $link->content,
			'image_url'     => $link->image,
			'source_url'    => $link->url,
			'date'          => $link->gmt_date ? get_date_from_gmt( $link->gmt_date ) : current_time( 'mysql' ),
			'score'         => $link->score,
			'campaign_id'   => $link->camp_id,
			'campaign_type' => $link->camp_type,
			'link_id'       => $link->id
		);

		return $article;
	}

	/**
	 * Admin Scripts
	 *
	 * All scripts for admin and youtube.
	 *
	 * @return void
	 * @since 1.0.4
	 */
	public function admin_scripts() {
		?>
		<script>
			window.addEventListener('load', function () {
				(function ($) {
					var checkSearchType = function () {
						if ($('#_youtube_search_type').val() === 'channel') {
							$('._youtube_channel_id_field').slideDown();
						} else {
							$('._youtube_channel_id_field').slideUp();
						}
					};

					checkSearchType();

					$('body').on('change', '#_youtube_search_type', checkSearchType);
					$('body').on('wpcpcontentloaded', checkSearchType);
				})(jQuery)
			});
		</script>
		<?php
	}

	public function youtube_api_error_log( $request, $camp_id ) {
		if ( isset( $request->response->error->errors ) ) {
			$error = @current( $request->response->error->errors );
			wpcp_log( "campaign #ID{$camp_id} {$error->domain} -- {$error->reason}", 'Critical' );
			return @$error->reason;
		}
	}

}
