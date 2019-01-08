<?php

namespace Pluginever\WPCP\Module;

use Pluginever\WPCP\Core\Item;
use Pluginever\WPCP\Traits\Hooker;

class Youtube extends Item {
    use Hooker;
    /**
     * Youtube api key
     *
     * @var string
     */
    protected $settings;
    protected $youtube_api;

    function setup() {
        $this->settings = get_option('wpcp_settings_youtube', []);
        $youtube_api = $this->settings['api_key'];

        if ( empty( $youtube_api ) ) {
            $msg = __( 'Youtube api is not configured. Please configure youtube settings.', 'wp-content-pilot' );
            wpcp_log( 'critical', $msg );

            return new \WP_Error( 'invalid-youtube-settings', $msg );
        }

        $this->youtube_api = $youtube_api;
    }

    function fetch_links() {

        $page             = $this->get_page_number( '' );
        $order            = wpcp_get_post_meta( $this->campaign_id, '_search_order', 'relevance' );
        $youtube_category = wpcp_get_post_meta( $this->campaign_id, '_youtube_category', 'any' );
        $video_definition = wpcp_get_post_meta( $this->campaign_id, '_video_definition', 'standard' );

        $request = $this->setup_request();

        $request->get( 'https://www.googleapis.com/youtube/v3/search', array(
            'part'              => 'snippet',
            'type'              => 'video',
            'key'               => $this->youtube_api,
            'maxResults'        => 50,
            'q'                 => $this->keyword,
            'category'          => $youtube_category,
            'videoEmbeddable'   => 'true',
            'videoType'         => 'any',
            'relevanceLanguage' => 'en',
            'videoDuration'     => 'any',
            'videoDefinition'   => $video_definition,
            'order'             => $order,
            'pageToken'         => $page,
        ) );

        $response = wpcp_is_valid_response( $request );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        if ( empty( $response->items ) ) {
            $msg = __( 'Could not find any links', 'wp-content-pilot' );
            wpcp_log( 'log', $msg );

            return new \WP_Error( 'no-links-in-response', $msg );
        }

        $links = [];
        foreach ( $response->items as $item ) {
            $links[] = esc_url_raw( "https://www.googleapis.com/youtube/v3/videos?id={$item->id->videoId}&key=API_KEY&part=id,snippet,contentDetails,statistics,player" );
        }

        $this->set_page_number( $response->nextPageToken );

        return $links;

    }

    function fetch_post( $link ) {
        $request = $this->setup_request();
        $url     = str_replace( 'API_KEY', $this->youtube_api, $link->url );

        $request->get( $url );

        $response = wpcp_is_valid_response( $request );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $response_body = is_array( $response->items ) ? array_pop( $response->items ) : $response->items;

        $width  = wpcp_get_post_meta( $this->campaign_id, '_wpcp_player_width', '640' );
        $height = wpcp_get_post_meta( $this->campaign_id, '_wpcp_player_height', '385' );

        $video_setting = [];
        if ( ! empty( get_post_meta( $this->campaign_id, '_wpcp_auto_play', true ) ) ) {
            $video_setting['autoplay'] = 1;
        }
        if ( ! empty( get_post_meta( $this->campaign_id, '_wpcp_disable_suggestion_video', true ) ) ) {
            $video_setting['rel'] = 0;
        }
        if ( ! empty( get_post_meta( $this->campaign_id, '_wpcp_hide_logo', true ) ) ) {
            $video_setting['modestbranding'] = 0;
        }

        $video_embed_url = add_query_arg(
            $video_setting,
            "//www.youtube.com/embed/{$response_body->id}"
        );

        $embed           = "<iframe src='$video_embed_url' width='{$width}' height='{$height}'  allowfullscreen></iframe>";
        $video_watch_url = "https://www.youtube.com/watch?v={$response_body->id}&" . implode( '&', $video_setting );
        $short_code      = "[embed width='{$width}' height='{$height}']{$video_watch_url}[/embed]";

        $this->final_url = $video_watch_url;

        $tags = @isset( $response_body->snippet->tags ) ? @$response_body->snippet->tags : [];

        $duration = self::wpcp_convert_youtube_duration( $response_body->contentDetails->duration );

        $image_url = $this->get_large_thumbnail( $response_body->snippet->thumbnails );

        $tags_html = implode( ', ', $tags );

        $description = $response_body->snippet->description;

        $contents = [
            __( 'Video', 'wp-content-pilot' )       => $short_code,
            __( 'Channel', 'wp-content-pilot' )      => "<a href='https://www.youtube.com/channel/{$response_body->snippet->channelId}'>{$response_body->snippet->channelTitle}</a>",
            __( 'Duration', 'wp-content-pilot' )    => $duration,
            __( 'Description', 'wp-content-pilot' ) => $description,
            __( 'tags', 'wp-content-pilot' )        => $tags_html,
        ];

        $html = '';
        foreach ( $contents as $label => $content ) {
            $html .= "<strong>{$label}:</strong><br>";
            $html .= "{$content}<br>";
        }


        $post = [
            'author'          => '',
            'published'       => wpcp_parse_date_time( $response_body->snippet->publishedAt ),
            'title'           => (string) $response_body->snippet->title,
            'content'         => $html,
            'image_url'       => $image_url,
            'image'           => wpcp_html_make_image_tag( $image_url ),
            'images'          => wpcp_html_make_image_tag( $image_url ),
            'tags_raw'        => $tags,
            'tags'            => $tags_html,
            'video'           => $embed,
            'video_url'       => $video_watch_url,
            'video_shortcode' => $short_code,
            'duration'        => $duration,
            'view_count'      => $response_body->statistics->viewCount,
            'like_count'      => $response_body->statistics->likeCount,
            'dislike_count'   => $response_body->statistics->dislikeCount,
            'favorite_count'  => $response_body->statistics->favoriteCount,
            'comment_count'   => $response_body->statistics->commentCount,
            'channel_url'     => "https://www.youtube.com/channel/{$response_body->snippet->channelId}",
            'channel_title'   => $response_body->snippet->channelTitle,
        ];

        return $post;

    }


    protected function get_large_thumbnail( $thumbnails ) {
        $thumbnails  = @(array) $thumbnails;
        $thumbnails  = array_values( $thumbnails );
        $large_index = count( $thumbnails ) - 1;
        $thumb       = $thumbnails[ $large_index ];

        return $thumb->url;
    }


    public static function wpcp_convert_youtube_duration( $youtube_time ) {
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

}
