<?php

namespace Pluginever\WPCP\Module;

use Pluginever\WPCP\Core\Item;

class Youtube extends Item {
    /**
     * Youtube api key
     *
     * @var string
     */
    protected $youtube_api;

    function setup() {
        $youtube_api = wpcp_get_settings( 'youtube_api', 'AIzaSyC98M8WDqskLTtK8w90uhqY6NLjppuLfsw' );

        if ( empty( $youtube_api ) ) {
            $msg = __( 'Youtube api is not configured. Please configure youtube settings.', 'wpcp' );
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
            $msg = __( 'Could not find any links', 'wpcp' );
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

        $image = $this->get_large_thumbnail( $response_body->snippet->thumbnails );

        $post = [
            'author'          => '',
            'published'       => wpcp_parse_date_time( $response_body->snippet->publishedAt ),
            'title'           => (string) $response_body->snippet->title,
            'content'         => $response_body->snippet->description,
            'image'           => $image,
            'images'          => (array) $image,
            'tags'            => $tags,
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
