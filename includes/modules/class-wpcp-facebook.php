<?php
// don't call the file directly
defined( 'ABSPATH' ) || exit();

class WPCP_FaceBook extends WPCP_Module {

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
	}

	/**
	 * @return string
	 * @since 1.2.0
	 */
	public function get_campaign_type() {
		return 'facebook';
	}

	/**
	 * @param $modules
	 *
	 * @return array
	 * @since 1.2.0
	 */
	public function register_module( $modules ) {
		$modules['facebook'] = __CLASS__;

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
		return array();
	}

	/**
	 * @return string
	 */
	public function get_default_template() {
		return '';
	}

	/**
	 * @param $post
	 */
	public function add_campaign_fields( $post ) {

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
	public function get_setting_section( $section ) {
		return $section;
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
	 * @since 1.2.0
	 */
	public function save_settings() {

	}

	/**
	 * @return mixed|void
	 */
	public function get_post( $entity = null ) {

		if ( empty( $entity ) ) {
			$entity = wpcp_get_post_meta( $this->campaign_id, 'fb_source_url_id' );
		}

		if ( empty( $entity ) ) {
			wpcp_disable_campaign( $this->campaign_id );
			$message = __( 'Campaign do not have any target page, group or timeline set, disabling campaign', 'wp-content-pilot' );
			wpcp_logger()->error( $message );

			return new WP_Error( 'missing-data', $message );
		}

		$source_type  = wpcp_get_post_meta( $this->campaign_id, 'fb_source_type' );
		$fb_post_from = wpcp_get_post_meta( $this->campaign_id, 'fb_post_from', 'posts' );
		$entity_id    = wpcp_get_post_meta( $this->campaign_id, 'extracted_id' );
		$fb_user      = wpcp_get_settings( 'fb_user', 'wpcp_settings_facebook' );
		$fb_xs        = wpcp_get_settings( 'fb_xs', 'wpcp_settings_facebook' );

		if ( empty( $fb_user ) || empty( $fb_xs ) ) {
			wpcp_disable_campaign( $this->campaign_id );

			$notice = __( 'Facebook cookie is not set with these data campaign wont run, disabling campaign.', 'wp-content-pilot-pro' );
			wpcp_logger()->error( $notice );

			return new WP_Error( 'missing-data', $notice );
		}

		$this->curl = new Curl\Curl();
		$this->curl->setOpt( CURLOPT_FOLLOWLOCATION, true );
		$this->curl->setOpt( CURLOPT_TIMEOUT, 30 );
		$this->curl->setOpt( CURLOPT_RETURNTRANSFER, true );
		$this->curl->setOpt( CURLOPT_USERAGENT, wpcp_get_random_user_agent() );

		if ( ! is_numeric( $entity ) && empty( $entity_id ) ) {
			wpcp_logger()->info( sprintf( 'Requesting facebook for generating entity id [%s]', $entity ) );
			$entity_request = $this->curl->get( $entity );


			if ( $this->curl->error ) {
				wpcp_disable_campaign( $this->campaign_id );
				$notice = sprintf( _( 'Error in retrieving entity id error [%s]', 'wp-content-pilot-pro' ), $this->curl->errorMessage );
				wpcp_logger()->error( $notice );

				return new WP_Error( 'request-error', $notice );
			}

			if ( stristr( $entity_request, 'entity_id' ) ) {
				$entity_id = null;
				if ( preg_match( '/"entity_id":"(?<page_id>\d+)"/i', $entity_request, $match ) ) {
					$entity_id = trim( $match['page_id'] );
				}
				if ( empty( $entity_id ) ) {
					wpcp_disable_campaign( $this->campaign_id );
					$notice = __( 'Error in retrieving entity id', 'wp-content-pilot-pro' );
					wpcp_logger()->error( $notice );

					return new WP_Error( 'request-error', $notice );
				} else {
					update_post_meta( $this->campaign_id, 'extracted_id', $entity_id );
				}
			} elseif ( stristr( $entity_request, 'PageComposerPagelet_' ) ) {
				preg_match_all( '{PageComposerPagelet_(\d*?)"}', $entity_request, $matches );
				$smatch    = $matches[1];
				$entity_id = $smatch[0];
				if ( empty( $entity_id ) ) {
					wpcp_disable_campaign( $this->campaign_id );
					$notice = __( 'Error in retrieving entity id', 'wp-content-pilot-pro' );
					wpcp_logger()->error( $notice );

					return new WP_Error( 'request-error', $notice );
				} else {
					update_post_meta( $this->campaign_id, 'extracted_id', $entity_id );
				}
			} else {
				$message = __( 'Could not find entity id seems not a valid facebook url', 'wp-content-pilot-pto' );
				wpcp_disable_campaign( $this->campaign_id, false );
				wpcp_logger()->error( $message );

				return new WP_Error( 'request-error', $message );
			}
		}

		$endpoint = 'https://mbasic.facebook.com/' . $entity_id;

		$query_args = [];
		if ( 'profile' == $source_type ) {
			$query_args['v'] = 'timeline';
		} elseif ( 'page_events' == $fb_post_from && 'group' == $source_type ) {
			$query_args['view']  = 'events';
			$query_args['refid'] = '18';
		} elseif ( 'page_events' == $fb_post_from && 'group' != $source_type ) {
			$endpoint             .= '/events';
			$query_args['locale'] = 'en_US';
		}

//		$endpoint = add_query_arg( $query_args, $endpoint);
		$this->curl->setCookie( 'xs', $fb_xs);
		$this->curl->setCookie( 'c_user', $fb_user);
//		$request = $this->curl->get( $endpoint );
//
//		if ( $this->curl->error ) {
//			wpcp_disable_campaign( $this->campaign_id );
//			$notice = sprintf( _( 'Error in retrieving entity id error [%s]', 'wp-content-pilot-pro' ), $this->curl->errorMessage );
//			wpcp_logger()->error( $notice );
//			return new WP_Error( 'request-error', $notice );
//		}
//
//		update_option( 'fb_cache', $request);
		$response = get_option( 'fb_cache' );
		if ( ( stristr( $response, 'notifications.php' ) && stristr( $response, $entity_id ) ) || ( stristr( $response, 'notifications.php' ) && $fb_post_from == 'page_events' ) ) {
			$response = str_replace( '&amp;', '&', $response );
			//todo save this cache

			$items = [];
			if ( $fb_post_from !== 'page_events' ) {
				$document = wpcp_str_get_html( $response );
				foreach ( $document->find( 'div[role="article"]' ) as $key => $item ) {

					/* @var $item simple_html_dom_node */

					$item_html = $item->outertext;
					//remove feeling action
					if ( stristr( $item_html, 'og_action_id' ) ) {
						$item_html = preg_replace( '{"og_action_id":"\d*?",}', '', $item_html );
					}

					if ( ( ! stristr( $item_html, 'data-ft=\'{"top_level_post_id' ) && ! stristr( $item_html, 'data-ft=\'{"qid' ) && ! stristr( $item_html, 'data-ft=' ) ) || ! stristr( $item_html, 'top_level_post_id' ) ) {
						continue;
					}

					$items[] = $item_html;
				}

			} else {
				//extract events
				preg_match_all( '{<a href="/events/(\d+)}', $response, $events_matches );

				if ( isset( $events_matches[1] ) ) {
					$items = $events_matches[1];
				} else {
					$items = array();
				}
			}

			wpcp_logger()->info( sprintf( 'Found total [%d] facebook articles', count( $items ) ) );


			foreach ( $items as $item ) {
				//remove image emoji
				$item = preg_replace( '{<img class="\w*?" height="16".*?>}s', '', $item );
				//remove profile pic p32x32
				$item = preg_replace( '{<img src="[^<]*?p32x32[^<]*?>}s', '', $item );

				if ( $fb_post_from != 'events' ) {
					if ( stristr( $item, 'top_level_post_id":' ) ) {
						preg_match( '{top_level_post_id":"(.*?)"}', $item, $pMatches );
					} else {
						preg_match( '{top_level_post_id\.(\d*)}', $item, $pMatches );
					}


					$item_id   = $entity_id . '_' . $pMatches[1];
					$single_id = $pMatches[1];

					$isEvent  = false; //ini
					$id_parts = explode( '_', $item_id );
					$url      = "https://www.facebook.com/{$id_parts[0]}/posts/{$id_parts[1]}";

					if ( ( stristr( $item, 'story_attachment_style":"new_album"' ) || stristr( $item, 'story_attachment_style":"album"' ) ) && stristr( $item, 'photo_id' ) ) {
						wpcp_logger()->info( 'seems to be a photo album' );
						//get photo_id
						preg_match( '{photo_id":"(.*?)"}', $item, $cMatches );

						$is_an_album = true;
						$album_url   = "https://www.facebook.com/{$cMatches[1]}";
					}
				} else {
					//events
					$id_parts = array( $entity_id, $item );
					$url      = "https://www.facebook.com/$item";
					$isEvent  = true;
					$item_id  = $single_id = $item;
				}

				if ( wpcp_is_duplicate_url( $url ) ) {
					continue;
				}

				//get created time
				if ( $fb_post_from != 'page_events' ) {
					$created_time = '';
					preg_match( '{publish_time":(\d*)}', $item, $tMatches );
					if ( isset( $tMatches[1] ) ) {
						$created_time = $tMatches[1];
					}
				} else {
					$created_time = time();
				}

				//real item html if event. now the $item contains the event id only
				if ( $fb_post_from == 'page_events' ) {
					$mbasic_event_url = "https://mbasic.facebook.com/events/" . $item . '?locale=en_US';
					$item             = $this->curl->get( $mbasic_event_url );
					if ( $this->curl->error ) {
						continue;
					}

					$item = preg_replace( '{<img class="\w*?" height="16".*?>}s', '', $item );
				}

				//found images
				preg_match_all( '{<img src=".*?>}', str_replace( '&amp;', '&', $item ), $imgMatchs );
				$all_imgs = $imgMatchs[0];
				foreach ( $all_imgs as $key => $single_img ) {
					if ( stristr( $single_img, 'static' ) || stristr( $single_img, '32x32' ) ) {
						unset( $all_imgs[ $key ] );
					}
				}
				$all_imgs = array_values( $all_imgs );

				// Finding the post type
				if ( $fb_post_from == 'page_events' ) {
					$type = 'event';
				} elseif ( stristr( $item, 'video_redirect' ) || stristr( $item, 'youtube.com%2Fwatch' ) ) {
					$type = "video";
				} elseif ( stristr( $item, '<a href="/notes' ) ) {
					$type = "note";
				} elseif ( stristr( $item, '/events/' ) ) {
					$type = "event";
				} elseif ( stristr( $item, 'offerx_' ) ) {
					$type = "offer";
				} elseif ( stristr( $item, 'l.php?' ) && preg_match( '{<h3 class="[^"]*?">[^<]}', $item ) ) {
					$type = "link";
				} elseif ( count( $all_imgs ) > 1 || stristr( $item, '/photos/' ) ) {
					$type = "photo";
				} elseif ( count( $all_imgs ) > 0 ) {
					$type = "photo";
				} else {
					$type = 'status';
				}

				wpcp_logger()->info( sprintf( 'Facebook Item type is [%s]', $type));

				//todo apply type filter

				//buidling content
				$title = '';
				$content = '';

//				if( $fb_post_from == 'page_events' ){
//					$temp_event_cnt = explode('unit_id_', $item);
//					unset($temp_event_cnt[0]);
//					preg_match_all('!</div></div></div><div class="[^<]*?">([^<].*?)</div>!s', implode(' ', $temp_event_cnt ) ,$contMatches);
//				}elseif( stristr($item, '*s"}\'></div>' ) && stristr($item, 'tn":"H"')  ){
//					//possible sell post
//					preg_match_all('!(<p>.*?</p>)!s', $item,$contMatches);
//
//					//<span class="co">(Sold)</span><span>bla bla title</span>
//					preg_match('!<span class="\w+?">\(.*?\)</span><span>(.*?)</span>!s', $item,$title_matches);
//
//					if( isset($title_matches[1]) && trim($title_matches[1]) != '' ){
//						$title = $title_matches[1];
//					}
//				}else{
//					$item_html = str_replace('{"tn":"*s"}' ,  'target' , $item);
//					$item_dom =wpcp_str_get_html( $item_html);
//					$items = $item_dom->find( '*[data-ft="target"]');
//
//					//faked matching array
//					$contMatches = array( array( $items[0] ) ,array( $items[0] ));

//					var_dump( $item_dom );
/*					preg_match_all('!tn":"\*s"}\'>[\s]*<span[^<]*?>(.*?)</span>.?</?div!s', $item,$contMatches);*/
//				}


				//colored post?
//				$is_colored_post = false;
//				if(  stristr($item,'background-image:url')  ){
//					$is_colored_post = true;
//				}


//				$contMatches = $contMatches[1];
//				if(count($contMatches) == 2){
//					if($contMatches[0] === $contMatches[1]){
//						unset($contMatches[1]);
//					}
//				}

//				if(isset($contMatches[0])) {
//					$matched_text_content = implode('<br>', $contMatches) ;
//					if(stristr($matched_text_content, 'background-image') || stristr($matched_text_content, 'color:rgba') || stristr($matched_text_content, 'font-size')){
//						$matched_text_content = strip_tags($matched_text_content);
//					}
//					if( stristr($matched_text_content, '<p> ') ){
//						$content =  $matched_text_content ;
//					}else{
//						$content =  '<p>'.$matched_text_content.'</p> ';
//					}
//				}

//				echo $content;


				// removed
//				if(stristr($item, 'original_content_id')){
//					preg_match('{"original_content_id":"(\d*?)"}s',$item,$original_id_matches);
//
//					if(isset($original_id_matches[1]) && trim( $original_id_matches[1] ) != ''){
//						$original_post_url = 'https://www.facebook.com/'. $original_id_matches[1];
//						echo '<br>Original post URL:'.$original_post_url;
//					}
//				}




//				$item_response = $this->curl->get( $url);
//				var_dump( $this->curl);
//				echo $item_response;



			}


		}

	}


	protected function discover_links( $campaign_id, $keyword, $page = 1 ) {
		$endpoint = add_query_arg( array(
			'q'      => urlencode( $keyword ),
			'count'  => 10,
			'loc'    => 'en',
			'format' => 'rss',
			'first'  => ( $page * 10 ),
		), 'https://www.bing.com/search' );
		wpcp_logger()->info( sprintf( 'Searching page url [%s]', $endpoint ) );
		$response = $this->get_request( $endpoint );
		if ( is_wp_error( $response ) ) {
			wpcp_logger()->error( $response->get_error_message() );

			return $response;
		}

		if ( ! $response instanceof \SimpleXMLElement ) {
			$response = simplexml_load_string( $response );
		}

		$response = json_encode( $response );
		$response = json_decode( $response, true );

		//check if links exist
		if ( empty( $response ) || ! isset( $response['channel'] ) || ! isset( $response['channel']['item'] ) || empty( $response['channel']['item'] ) ) {
			$message = __( 'Could not find any links from search engine,waiting...', 'wp-content-pilot' );
			wpcp_logger()->info( $message );

			return new WP_Error( 'no-links-found', $message );
		}

		$inserted = 0;
		foreach ( $response['channel']['item'] as $item ) {
			$banned_hosts = wpcp_get_settings( 'banned_hosts', 'wpcp_settings_article' );
			$banned_hosts = preg_split( '/\n/', $banned_hosts );

			foreach ( $banned_hosts as $banned_host ) {
				if ( stristr( $item['link'], $banned_host ) ) {
					continue;
				}
			}

			if ( stristr( $item['link'], 'wikipedia' ) ) {
				continue;
			}

			if ( wpcp_is_duplicate_title( $item['title'] ) ) {
				continue;
			}

			if ( wpcp_is_duplicate_url( $item['link'] ) ) {
				continue;
			}

			$this->insert_link( array(
				'url'          => esc_url( $item['link'] ),
				'title'        => $item['title'],
				'keyword'      => $keyword,
				'pub_date_gmt' => empty( $item['pubDate'] ) ? '' : date( 'Y-m-d H:i:s', strtotime( $item['pubDate'] ) ),
			) );

			$inserted ++;
		}

		if ( $inserted < 1 ) {
			wpcp_logger()->info( 'Could not find any links' );

			return false;
		}

		wpcp_logger()->info( sprintf( 'Total [%d] links found', $inserted ) );

		return true;
	}


	/**
	 * Main WPCP_FaceBook Instance.
	 *
	 * Ensures only one instance of WPCP_FaceBook is loaded or can be loaded.
	 *
	 * @return WPCP_FaceBook Main instance
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

WPCP_FaceBook::instance();
