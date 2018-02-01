<?php

function wpcp_run_automatic_campaign() {
    $args = array(
        'post_type'  => 'wp_content_pilot',
        'meta_key'   => '_last_run',
        'orderby'    => 'meta_value_num',
        'order'      => 'ASC',
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key'     => '_active',
                'value'   => '1',
                'compare' => '=',
            ),
        ),
    );

    $query = new \WP_Query( $args );
    wp_reset_query();

    if ( ! $query->have_posts() ) {
        wpcp_log( 'dev', 'No campaign found in scheduled task' );

        return;
    }

    $campaigns = wp_list_pluck( $query->posts, 'ID' );

    $last_campaign = get_option( 'wpcp_last_campaign', '' );

    if ( ! empty( $last_campaign ) && count( $campaigns ) > 1 ) {
        unset( $campaigns[ $last_campaign ] );
    }

    foreach ( $campaigns as $campaign_id ) {
        $last_run     = get_post_meta( $campaign_id, '_last_run', true );
        $frequency    = wpcp_get_post_meta( $campaign_id, '_frequency', 0 );
        $target       = wpcp_get_post_meta( $campaign_id, '_target', 0 );
        $posted       = wpcp_get_post_meta( $campaign_id, '_total_posted', 0 );
        $current_time = current_time( 'timestamp' );
        $diff         = $current_time - $last_run;
        if ( $diff < $frequency ) {
            continue;
        }

        if ( $posted >= $target ) {
            wpcp_disable_campaign( $campaign_id );
            continue;
        }

        $campaign = wpcp_run_campaign( $campaign_id );

        if ( is_wp_error( $campaign ) ) {
            wpcp_log( 'critical', __( 'Automatic campaign failed.', 'wpcp' ) );
            wpcp_disable_campaign( $campaign_id );
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
    $posted = wpcp_get_post_meta( $campaign_id, '_total_posted', 0 );
    update_post_meta( $campaign_id, '_total_posted', ( intval( $posted ) + 1 ) );
    update_post_meta( $campaign_id, '_last_keyword', $keyword );
    update_post_meta( $campaign_id, '_last_run', current_time( 'timestamp' ) );
    update_option( 'wpcp_last_campaign', $campaign_id );
    update_option( 'wpcp_last_post', $post_id );
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
function wpcp_maybe_set_featured_image( $post_id, $article, $campaign_id ) {
    if ( empty( wpcp_get_post_meta( $campaign_id, '_set_featured_image' ) ) || empty( $article['image'] ) ) {
        return;
    }

    wpcp_set_featured_image_from_link( esc_url_raw( $article['image'] ), $post_id );

}
