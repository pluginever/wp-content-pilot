<?php
// don't call the file directly
defined( 'ABSPATH' ) || exit();

class WPCP_Flickr extends WPCP_Module {
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
		add_action( 'wpcp_flickr_campaign_options_meta_fields', 'wpcp_keyword_suggestion_field' );
		add_action( 'wpcp_flickr_campaign_options_meta_fields', 'wpcp_keyword_field' );
		parent::__construct( 'flickr' );
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
			'title'            => __( 'Title', 'wp-content-pilot' ),
			'content'          => __( 'Content', 'wp-content-pilot' ),
			'date'             => __( 'Published date', 'wp-content-pilot' ),
			'image_url'        => __( 'Main image url', 'wp-content-pilot' ),
			'source_url'       => __( 'Source link', 'wp-content-pilot' ),
			'author'           => __( 'Author Name', 'wp-content-pilot' ),
			'author_url'       => __( 'Author Url', 'wp-content-pilot' ),
			'tags'             => __( 'Photo Tags', 'wp-content-pilot' ),
			'views'            => __( 'Photo Views', 'wp-content-pilot' ),
			'date_taken'       => __( 'Photo Taken Date', 'wp-content-pilot' ),
			'date_posted'      => __( 'Photo Posted Date', 'wp-content-pilot' ),
			'user_id'          => __( 'User Id', 'wp-content-pilot' ),
			'square_img'       => __( 'Square Image Url', 'wp-content-pilot' ),
			'large_square_img' => __( 'Large Square Image Url', 'wp-content-pilot' ),
			'thumbnail_img'    => __( 'Thumbnail Image Url', 'wp-content-pilot' ),
			'small_img'        => __( 'Small Image Url', 'wp-content-pilot' ),
			'small_320_img'    => __( 'Small 320 Image Url', 'wp-content-pilot' ),
			'medium_img'       => __( 'Medium Image Url', 'wp-content-pilot' ),
			'medium_640_img'   => __( 'Medium 640 Image Url', 'wp-content-pilot' ),
			'medium_800_img'   => __( 'Medium 800 Image Url', 'wp-content-pilot' ),
			'large_img'        => __( 'Large Image Url', 'wp-content-pilot' ),
			'original_img'     => __( 'Original Image Url', 'wp-content-pilot' ),
		);
	}

	/**
	 * @return string
	 * @since 1.2.0
	 */
	public function get_default_template() {
		$template
			= <<<EOT
<img src="{image_url}" alt="">
<br>
<a href="{image_url}">Posted</a> by <a href="{author_url}">{author}</a>
<br>
{content}
<br>
<strong>Tags:</strong><br>
{tags}
<br>
<a href="{source_url}">Source</a>
EOT;

		return $template;
	}

	/**
	 * @param $post
	 */
	public function add_campaign_option_fields( $post ) {
		echo WPCP_HTML::start_double_columns();
		echo WPCP_HTML::select_input(
			array(
				'name'    => '_search_order',
				'label'   => __( 'Sort Order', 'wp-content-pilot' ),
				'options' => array(
					'relevance'            => __( 'Relevance', 'wp-content-pilot' ),
					'date-posted-asc'      => __( 'Date Posted ASC', 'wp-content-pilot' ),
					'date-posted-desc'     => __( 'Date Posted DESC', 'wp-content-pilot' ),
					'date-taken-asc'       => __( 'Date Taken ASC', 'wp-content-pilot' ),
					'date-taken-desc'      => __( 'Date Taken DESC', 'wp-content-pilot' ),
					'interestingness-desc' => __( 'Interestingness DESC', 'wp-content-pilot' ),
					'interestingness-asc'  => __( 'Interestingness ASC', 'wp-content-pilot' ),
				),
				'desc'    => __( 'Sort order for flickr', 'wp-content-pilot' ),
				'default' => 'relevance',
				'class'   => 'wpcp-select2',
			)
		);
		echo WPCP_HTML::text_input( array(
			'name'  => '_user_id',
			'label' => __( 'Specific User ID', 'wp-content-pilot' ),
			'desc'  => 'Make flickr user id <a target="_blank" href="http://idgettr.com/">here</a>. Example id : 75866656@N00',
		) );
		echo WPCP_HTML::select_input( array(
			'name'    => '_flickr_licenses[]',
			'label'   => __( 'Choose License', 'wp-content-pilot' ),
			'options' => array(
				0  => __( 'All Rights Reserved', 'wp-content-pilot' ),
				1  => __( 'Attribution-NonCommercial-ShareAlike License', 'wp-content-pilot' ),
				2  => __( 'Attribution-NonCommercial License', 'wp-content-pilot' ),
				3  => __( 'Attribution-NonCommercial-NoDerivs License', 'wp-content-pilot' ),
				4  => __( 'Attribution License', 'wp-content-pilot' ),
				5  => __( 'Attribution-ShareAlike License', 'wp-content-pilot' ),
				6  => __( 'Attribution-NoDerivs License', 'wp-content-pilot' ),
				7  => __( 'No known copyright restrictions', 'wp-content-pilot' ),
				8  => __( 'United States Government Work', 'wp-content-pilot' ),
				9  => __( 'Public Domain Dedication (CC0)', 'wp-content-pilot' ),
				10 => __( 'Public Domain Mark', 'wp-content-pilot' ),
			),
			'desc'    => __( 'License Restrictions flickr', 'wp-content-pilot' ),
			'default' => 7,
			'class'   => 'wpcp-select2',
			'attrs'   => array(
				'multiple' => true
			),
		) );
		echo WPCP_HTML::end_double_columns();
	}

	/**
	 * @param $campaign_id
	 * @param $posted
	 */
	public function save_campaign_meta( $campaign_id, $posted ) {
		$flickr_licenses = isset( $posted['_flickr_licenses'] )? sanitize_text_field( $posted['_flickr_licenses'] ): '';
		update_post_meta( $campaign_id, '_search_order', empty( $posted['_search_order'] ) ? 'relevance' : sanitize_key( $posted['_search_order'] ) );
		update_post_meta( $campaign_id, '_user_id', empty( $posted['_user_id'] ) ? '' : $posted['_user_id'] );
		update_post_meta( $campaign_id, '_flickr_licenses', empty( $posted['_flickr_licenses'] ) ? 7 : $flickr_licenses );
	}

	/**
	 * @param $sections
	 *
	 * @return array
	 * @since 1.2.0
	 */
	public function get_setting_section( $sections ) {
		$sections[] = [
			'id'    => 'wpcp_settings_flickr',
			'title' => __( 'Flickr', 'wp-content-pilot' )
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
		$fields['wpcp_settings_flickr'] = [
			array(
				'name'    => 'api_key',
				'label'   => __( 'Flickr API key', 'wp-content-pilot' ),
				'desc'    => sprintf( __( 'Get your flickr API key by following this <a href="%s" target="_blank">link</a>.', 'wp-content-pilot' ), 'https://www.pluginever.com/docs/wp-content-pilot/flickr-campaign-settings/' ),
				'type'    => 'password',
				'default' => ''
			),
		];

		return $fields;
	}

	/**
	 * @param int $campaign_id
	 *
	 * @return mixed|void
	 * @since 1.2.0
	 */
	public function get_post( $campaign_id ) {
		wpcp_logger()->info( __( 'Loaded Flickr Campaign', 'wp-content-pilot' ), $campaign_id );

		wpcp_logger()->info( __( 'Checking flick api key for authentication', 'wp-content-pilot' ), $campaign_id );

		$api_key    = wpcp_get_settings( 'api_key', 'wpcp_settings_flickr', '' );
		$sort_order = wpcp_get_post_meta( $campaign_id, '_search_order', 'relevance' );
		$user_id    = wpcp_get_post_meta( $campaign_id, '_user_id', '' );
		$licenses   = wpcp_get_post_meta( $campaign_id, '_flickr_licenses', 7 );

		if ( empty( $api_key ) ) {
			wpcp_disable_campaign( $campaign_id );
			$notice = __( 'The Flickr api key is not set so the campaign won\'t run, disabling campaign.', 'wp-content-pilot' );
			wpcp_logger()->error( $notice, $campaign_id );

			return new WP_Error( 'missing-data', $notice );
		}

		$keywords = $this->get_campaign_meta( $campaign_id );
		if ( empty( $keywords ) ) {
			return new WP_Error( 'missing-data', __( 'Campaign do not have keyword to proceed, please set keyword', 'wp-content-pilot' ) );
		}

		foreach ( $keywords as $keyword ) {
			wpcp_logger()->info( sprintf( 'Looping through keywords [ %s ]', $keyword ), $campaign_id );

			$total_page_key = $this->get_unique_key( "$keyword-total-page" );
			$page_key       = $this->get_unique_key( $keyword );
			$total_page     = wpcp_get_post_meta( $campaign_id, $total_page_key, '' );
			$page_number    = wpcp_get_post_meta( $campaign_id, $page_key, 1 );

			if ( $page_number >= $total_page && ! empty( $total_page ) ) {
				$msg = sprintf( __( 'Maximum page number reached for the keyword [%s] deactivating the keyword for a week', 'wp-content-pilot' ), $keyword );
				wpcp_logger()->error( $msg, $campaign_id );
				$this->deactivate_key( $campaign_id, $keyword, 7 * 24 * 60 * 60 );

				return new \WP_Error( 'max-page', $msg );
			}

			$query_args = array(
				'text'           => urlencode( $keyword ),
				'api_key'        => $api_key,
				'content_type'   => 'photos',
				'sort'           => $sort_order,
				'media'          => 'photos',
				'per_page'       => 1,
				'page'           => $page_number,
				'format'         => 'json',
				'nojsoncallback' => '1',
				'method'         => 'flickr.photos.search',
				'licenses'       => implode( ",", $licenses )
			);

			if ( $user_id != '' ) {
				$query_args['user_id'] = $user_id;
			}
			$endpoint = add_query_arg( $query_args, 'https://api.flickr.com/services/rest/' );
			wpcp_logger()->info( sprintf( __( 'Looking for data from [%s]', 'wp-content-pilot' ), preg_replace( '/api_key=([^&]+)/m', 'api_key=X', $endpoint ) ), $campaign_id );
			$curl = $this->setup_curl();
			$curl->get( $endpoint );


			if ( $curl->isError() ) {
				$message = sprintf( __( 'Flickr api request failed response [%s]', 'wp-content-pilot' ), $curl->getErrorMessage() );
				wpcp_logger()->error( $message, $campaign_id );
				wpcp_update_post_meta( $campaign_id, $page_key, $page_number + 1 );
				continue;
			}

			wpcp_logger()->info( __( 'Extracting response from request', 'wp-content-pilot' ), $campaign_id );
			$response = $curl->getResponse();

			if ( isset( $response->photos->pages ) && empty( $total_page ) ) {
				wpcp_update_post_meta( $campaign_id, $total_page_key, absint( $response->photos->pages ) );
			}

			if ( empty( $response->photos->photo ) ) {
				$msg = sprintf( __( 'Could not find any result for the keyword %s', 'wp-content-pilot' ), $keywords );
				wpcp_logger()->error( $msg, $campaign_id );
				wpcp_update_post_meta( $campaign_id, $page_key, $page_number + 1 );
				continue;
			}

			$photo = array_pop( $response->photos->photo );
			$title = $photo->title;
			wpcp_logger()->info( __( 'Requesting for images from flickr by title', 'wp-content-pilot' ), $campaign_id );
			$url = esc_url_raw( "https://api.flickr.com/services/rest/?method=flickr.photos.getInfo&api_key={$api_key}&photo_id={$photo->id}&secret={$photo->secret}&format=json&nojsoncallback=1}" );
			$curl->get( $url );

			$response = $curl->getResponse();

			wpcp_logger()->info( __( 'Extracting content from response', 'wp-content-pilot' ), $campaign_id );
			$description            = $response->photo->description->_content;
			$tags                   = ! empty( $response->photo->tags->tag ) ? implode( ', ', wp_list_pluck( $response->photo->tags->tag, 'raw' ) ) : '';
			$image_url              = "http://farm{$response->photo->farm}.staticflickr.com/{$response->photo->server}/{$response->photo->id}_{$response->photo->secret}.jpg";
			$square_image_url       = "http://farm{$response->photo->farm}.staticflickr.com/{$response->photo->server}/{$response->photo->id}_{$response->photo->secret}_s.jpg";
			$large_square_image_url = "http://farm{$response->photo->farm}.staticflickr.com/{$response->photo->server}/{$response->photo->id}_{$response->photo->secret}_q.jpg";
			$thumb_image_url        = "http://farm{$response->photo->farm}.staticflickr.com/{$response->photo->server}/{$response->photo->id}_{$response->photo->secret}_t.jpg";
			$small_image_url        = "http://farm{$response->photo->farm}.staticflickr.com/{$response->photo->server}/{$response->photo->id}_{$response->photo->secret}_n.jpg";
			$small_320_image_url    = "http://farm{$response->photo->farm}.staticflickr.com/{$response->photo->server}/{$response->photo->id}_{$response->photo->secret}_n.jpg";
			$medium_image_url       = "http://farm{$response->photo->farm}.staticflickr.com/{$response->photo->server}/{$response->photo->id}_{$response->photo->secret}_m.jpg";
			$medium_640_image_url   = "http://farm{$response->photo->farm}.staticflickr.com/{$response->photo->server}/{$response->photo->id}_{$response->photo->secret}_z.jpg";
			$medium_800_image_url   = "http://farm{$response->photo->farm}.staticflickr.com/{$response->photo->server}/{$response->photo->id}_{$response->photo->secret}_c.jpg";
			$large_image_url        = "http://farm{$response->photo->farm}.staticflickr.com/{$response->photo->server}/{$response->photo->id}_{$response->photo->secret}_b.jpg";
			$original_image_url     = "http://farm{$response->photo->farm}.staticflickr.com/{$response->photo->server}/{$response->photo->id}_{$response->photo->secret}_o.jpg";
			$source_url             = $response->photo->urls->url[0]->_content;
			//$tags        = wpcp_array_to_html( $tags );
			$date = $response->photo->dates->taken;


			//check if the clean title metabox is checked and perform title cleaning
			$check_clean_title = wpcp_get_post_meta( $campaign_id, '_clean_title', 'off' );

			if ( 'on' == $check_clean_title ) {
				wpcp_logger()->info( __( 'Cleaning title', 'wp-content-pilot' ), $campaign_id );
				$title = wpcp_clean_title( $title );
			} else {
				$title = html_entity_decode( $title, ENT_QUOTES );
			}


			wpcp_logger()->info( sprintf( __( 'Generating flickr article from [ %s ]', 'wp-content-pilot' ), $source_url ), $campaign_id );
			$article = array(
				'title'            => $title,
				'content'          => $description,
				'date'             => $date,
				'image_url'        => $image_url,
				'source_url'       => $source_url,
				'tags'             => $tags,
				'author'           => ( $response->photo->owner->realname != '' ) ? $response->photo->owner->realname : $response->photo->owner->username,
				'author_url'       => "https://www.flickr.com/photos/{$response->photo->owner->nsid}/",
				'views'            => $response->photo->views,
				'user_id'          => $response->photo->owner->nsid,
				'square_img'       => $square_image_url,
				'large_square_img' => $large_square_image_url,
				'thumbnail_img'    => $thumb_image_url,
				'small_img'        => $small_image_url,
				'small_320_img'    => $small_320_image_url,
				'medium_img'       => $medium_image_url,
				'medium_640_img'   => $medium_640_image_url,
				'medium_800_img'   => $medium_800_image_url,
				'large_img'        => $large_image_url,
				'original_img'     => $original_image_url,
			);

			wpcp_logger()->info( __( 'Inserting links into store....', 'wp-content-pilot' ), $campaign_id );
			$this->insert_link( array(
				'for'     => $keyword,
				'title'   => $title,
				'url'     => $source_url,
				'camp_id' => $campaign_id,
				'status'  => 'success',
			) );
			wpcp_update_post_meta( $campaign_id, $page_key, $page_number + 1 );

			wpcp_logger()->info( __( 'Article processed from campaign', 'wp-content-pilot' ), $campaign_id );

			return $article;
		}

		$log_url = admin_url( '/edit.php?post_type=wp_content_pilot&page=wpcp-logs' );

		return new WP_Error( 'campaign-error', __( sprintf( 'No flickr article generated check <a href="%s">log</a> for details.', $log_url ), 'wp-content-pilot' ) );
	}


	/**
	 * Main WPCP_Flickr Instance.
	 *
	 * Ensures only one instance of WPCP_Flickr is loaded or can be loaded.
	 *
	 * @return WPCP_Flickr Main instance
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

WPCP_Flickr::instance();
