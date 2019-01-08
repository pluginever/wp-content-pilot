<?php
/**
 * Run automatic campaign
 *
 * @since 1.0.0
 */
function wpcp_run_automatic_campaign() {
    global $wpdb;
    $sql   = "select * from {$wpdb->posts} p  left join {$wpdb->postmeta} m on p.id = m.post_id having m.meta_key = '_active' AND p.post_status = 'publish' AND m.meta_value = '1'";
    $posts = $wpdb->get_results( $sql );

    if ( ! $posts ) {
        wpcp_log( 'dev', 'No campaign found in scheduled task' );

        return;
    }

    $campaigns = wp_list_pluck( $posts, 'ID' );
    wpcp_log( 'dev', $campaigns );
    $last_campaign = get_option( 'wpcp_last_campaign', '' );

    if ( ! empty( $last_campaign ) && count( $campaigns ) > 1 ) {
        unset( $campaigns[ $last_campaign ] );
    }

    foreach ( $campaigns as $campaign_id ) {
        wpcp_log( 'dev', 'Running automatic campaign ' . $campaign_id );
        $last_run     = get_post_meta( $campaign_id, '_last_run', true );
        $frequency    = wpcp_get_post_meta( $campaign_id, '_frequency', 0 );
        $target       = wpcp_get_post_meta( $campaign_id, '_campaign_target', 0 );
        $posted       = wpcp_get_post_meta( $campaign_id, '_post_count', 0 );
        $current_time = current_time( 'timestamp' );
        $diff         = $current_time - $last_run;
        if ( $diff < $frequency ) {
            wpcp_log( 'dev', 'skipping campaign its too early' );
            continue;
        }

        if ( $posted >= $target ) {
            wpcp_log( 'dev', 'campaign is complete.' );
            wpcp_disable_campaign( $campaign_id );
            continue;
        }

        $campaign = wpcp_run_campaign( $campaign_id );

        if ( is_wp_error( $campaign ) ) {
            wpcp_log( 'dev', __( 'Automatic campaign failed.', 'wpcp' ) );
            wpcp_log( 'critical', $campaign->get_error_message() );
        }
    }

}

/**
 * Update the campaign status after
 * successful run of a campaign
 *
 * @since 1.0.0
 *
 * @param $post_id
 * @param $campaign_id
 * @param $keyword
 *
 */
function update_campaign_status( $post_id, $campaign_id, $keyword ) {
    $posted = wpcp_get_post_meta( $campaign_id, '_post_count', 0 );
    update_post_meta( $campaign_id, '_post_count', ( intval( $posted ) + 1 ) );
    update_post_meta( $campaign_id, '_last_keyword', $keyword );
    update_post_meta( $campaign_id, '_last_run', current_time( 'timestamp' ) );
    update_post_meta( $campaign_id, '_campaign_id', $campaign_id );
    update_option( 'wpcp_last_campaign', $campaign_id );
    update_option( 'wpcp_last_post', $post_id );
}

/**
 * Set post categories
 *
 * @since 1.0.3
 *
 * @param int    $post_id
 * @param int    $campaign_id
 * @param string $keyword
 * @param array  $postarr
 *
 * @return void
 */
function wpcp_set_post_categories( $post_id, $campaign_id, $keyword, $postarr ) {
    if ( 'post' !== $postarr['post_type'] ) {
        return;
    }

    $categories = wpcp_get_post_meta( $campaign_id, '_post_categories', [] );

    if ( ! empty( $categories ) ) {
        wp_set_post_categories( $post_id, $categories );
    }
}

/**
 * Mark the link as failed before fetching post
 * after the successful mark as success
 *
 * @since 1.0.0
 *
 * @param $link
 *
 */
function wpcp_mark_link_as_failed( $link ) {
    wpcp_update_link( $link->id, [ 'status' => 3 ] );
}

/**
 * Mark the link as success as fetched the post
 *
 * @since 1.0.0
 *
 * @param $link
 *
 */
function wpcp_mark_link_as_success( $link ) {
    wpcp_update_link( $link->id, [ 'status' => 2 ] );
}

/**
 * @since 1.0.0
 *
 * @param $post_id
 * @param $article
 * @param $campaign_id
 *
 */
function wpcp_maybe_set_featured_image( $post_id, $campaign_id, $keyword, $postarr, $article ) {
    error_log( 'wpcp_maybe_set_featured_image' );
    error_log( empty( $article ) );
    if ( empty( wpcp_get_post_meta( $campaign_id, '_set_featured_image' ) ) || empty( $article['image_url'] ) ) {
        return;
    }

    wpcp_set_featured_image_from_link( esc_url_raw( $article['image_url'] ), $post_id );

}

/**
 * @since 1.0.0
 *
 * @param $campaign_id
 * @param $keyword
 *
 */
function wpcp_log_disable_keyword( $campaign_id, $keyword ) {
    wpcp_log( 'log', __( "Keyword: {$keyword} has been removed.", 'wpcp' ) );
}

/**
 * @since 1.0.0
 *
 * @param $campaign_id
 *
 */
function wpcp_log_campaign_disable( $campaign_id ) {
    $title = get_the_title( $campaign_id );
    wpcp_log( 'log', __( $title . ' Campaign has been disabled', 'wpcp' ) );
}

/**
 * Handle campaign activation/de-activation
 *
 * @since 1.0.1
 *
 * @param $new_status
 * @param $old_status
 * @param $post \WP_Post
 *
 */
function wpcp_handle_campaign_post_status( $new_status, $old_status, $post ) {

    if ( $post->post_type == 'wp_content_pilot' ) {
        if ( $new_status == 'publish' ) {
            update_post_meta( $post->ID, '_active', '1' );
        } else {
            update_post_meta( $post->ID, '_active', '0' );
        }
    }
}

/**
 * Limit the title length
 *
 * @since 1.0.4
 *
 * @param $post_id
 * @param $campaign_id
 *
 * @return bool
 */
function wpcp_limit_title_length( $post_id, $campaign_id ) {
    $limit_to_length = wpcp_get_post_meta( $campaign_id, '_title_limit', 0 );
    if ( empty( $limit_to_length ) ) {
        return false;
    }
    $title = get_the_title( $post_id );
    if ( strlen( $title ) < $limit_to_length ) {
        return false;
    }

    $updated_title = substr( $title, 0, $limit_to_length );
    $result        = wp_update_post( array(
        'ID'         => $post_id,
        'post_title' => $updated_title,
    ), true );

    if ( is_wp_error( $result ) ) {
        wpcp_log( 'critical', __( "could not update title because {$result->get_error_message()}" ) );

        return false;
    }

    return true;
}

/**
 * Limit the content length
 *
 * @since 1.0.4
 *
 * @param $post_id
 * @param $campaign_id
 *
 * @return bool
 */
function wpcp_limit_content_length( $post_id, $campaign_id ) {
    $limit_to_length = wpcp_get_post_meta( $campaign_id, '_content_limit', 0 );
    if ( empty( $limit_to_length ) ) {
        return false;
    }

    $post = get_post( $post_id );

    $content = $post->post_content;

    if ( strlen( $content ) < $limit_to_length ) {
        return false;
    }

    $updated_content = substr( $content, 0, $limit_to_length );
    $result          = wp_update_post( array(
        'ID'           => $post_id,
        'post_content' => $updated_content,
    ), true );

    if ( is_wp_error( $result ) ) {
        wpcp_log( 'critical', __( "could not update content because {$result->get_error_message()}" ) );

        return false;
    }

    return true;
}
