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
			'title'      => __( 'Title', 'wp-content-pilot' ),
			'content'    => __( 'Content', 'wp-content-pilot' ),
			'date'       => __( 'Published date', 'wp-content-pilot' ),
			'image_url'  => __( 'Main image url', 'wp-content-pilot' ),
			'source_url' => __( 'Source link', 'wp-content-pilot' ),
			'author'     => __( 'Author Name', 'wp-content-pilot' ),
			'author_url' => __( 'Author Url', 'wp-content-pilot' ),
			'tags'       => __( 'Photo Tags', 'wp-content-pilot' ),
			'views'      => __( 'Photo Views', 'wp-content-pilot' ),
			'user_id'    => __( 'User Id', 'wp-content-pilot' ),
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
<a href="{image_url}">Posted</a> by <a href="http://flicker.com/{author_url}">{author}</a>
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

	}

	/**
	 * @param $campaign_id
	 * @param $posted
	 */
	public function save_campaign_meta( $campaign_id, $posted ) {

	}

	/**
	 * @param $section
	 *
	 * @return array
	 * @since 1.2.0
	 */
	public function get_setting_section( $sections ) {
		$sections[] = [
			'id'    => 'wpcp_settings_flickr',
			'title' => __( 'Flickr Settings', 'wp-content-pilot' )
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
				'label'   => __( 'Flickr API Key', 'wp-content-pilot' ),
				'desc'    => sprintf( __( 'Get your Flickr API key by following this <a href="%s" target="_blank">link</a>.', 'wp-content-pilot' ), 'https://www.pluginever.com/docs/wp-content-pilot/flickr-campaign-settings/' ),
				'type'    => 'password',
				'default' => ''
			),
		];

		return $fields;
	}

	/**
	 * @param int $campaign_id
	 * @param array $keywords
	 *
	 * @return mixed|void
	 * @throws ErrorException
	 * @since 1.2.0
	 */
	public function get_post( $campaign_id, $keywords ) {
		wpcp_logger()->info( 'Flickr Campaign Started', $campaign_id );

		$api_key = wpcp_get_settings( 'api_key', 'wpcp_settings_flickr', '' );
		if ( empty( $api_key ) ) {
			wpcp_disable_campaign( $campaign_id );
			$notice = __( 'The Flickr api key is not set so the campaign won\'t run, disabling campaign.', 'wp-content-pilot' );
			wpcp_logger()->error( $notice, $campaign_id );

			return new WP_Error( 'missing-data', $notice );
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
				'text'           => $keyword,
				'api_key'        => $api_key,
				'sort'           => 'relevance',
				'content_type'   => 'photos',
				'media'          => 'photos',
				'per_page'       => 1,
				'page'           => $page_number,
				'format'         => 'json',
				'nojsoncallback' => '1',
				'method'         => 'flickr.photos.search',
			);
			$endpoint   = add_query_arg( $query_args, 'https://api.flickr.com/services/rest/' );
			wpcp_logger()->debug( sprintf( 'Looking for data from [%s]', preg_replace( '/api_key=([^&]+)/m', 'api_key=X', $endpoint) ) , $campaign_id);
			$curl = $this->setup_curl();
			$curl->get( $endpoint );


			if ( $curl->isError() ) {
				$message = sprintf( __( 'Flickr api request failed response [%s]', 'wp-content-pilot' ), $curl->getErrorMessage() );
				wpcp_logger()->error( $message, $campaign_id );
				wpcp_update_post_meta( $campaign_id, $page_key, $page_number + 1 );
				continue;
			}

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
			$title = @$photo->title;
			$url   = esc_url_raw( "https://api.flickr.com/services/rest/?method=flickr.photos.getInfo&api_key={$api_key}&photo_id={$photo->id}&secret={$photo->secret}&format=json&nojsoncallback=1}" );
			$curl->get( $url );

			$response    = $curl->getResponse();
			$description = @$response->photo->description->_content;
			$tags        = ! empty( $response->photo->tags->tag ) ? implode( ', ', wp_list_pluck( @$response->photo->tags->tag, 'raw' ) ) : '';
			$image_url   = "http://farm{$response->photo->farm}.staticflickr.com/{$response->photo->server}/{$response->photo->id}_{$response->photo->secret}.jpg";
			$source_url  = $response->photo->urls->url[0]->_content;
			$tags        = wpcp_array_to_html( $tags );

			if ( wpcp_is_duplicate_url( $source_url ) ) {
				wpcp_update_post_meta( $campaign_id, $page_key, $page_number + 1 );
				continue;
			}
			wpcp_logger()->info( sprintf( 'Generating flickr article from [ %s ]', $source_url ), $campaign_id );
			$article = array(
				'title'      => $title,
				'content'    => $description,
				'date'       => '',
				'image_url'  => $image_url,
				'source_url' => $source_url,
				'tags'       => $tags,
				'author'     => $response->photo->owner->realname,
				'author_url' => "https://www.flickr.com/photos/{$response->photo->owner->nsid}/",
				'views'      => $response->photo->views,
				'user_id'    => $response->photo->owner->nsid,
			);

			$this->insert_link( array(
				'keyword' => $keyword,
				'title'   => $title,
				'url'     => $source_url,
				'camp_id' => $campaign_id,
			) );
			wpcp_update_post_meta( $campaign_id, $page_key, $page_number + 1 );

			wpcp_logger()->info( 'Article processed from campaign', $campaign_id );

			return $article;
		}

		$log_url = admin_url('/edit.php?post_type=wp_content_pilot&page=wpcp-logs');
		return new WP_Error( 'campaign-error', __( sprintf('No flickr article generated check <a href="%s">log</a> for details.', $log_url ), 'wp-content-pilot' ) );
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
