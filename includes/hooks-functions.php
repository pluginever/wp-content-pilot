<?php
function wpcp_set_post_taxonomy( $taxonomies, $campaign_id ) {
	$categories = wpcp_get_post_meta( $campaign_id, '_categories', [] );

	if ( ! empty( $categories ) ) {
		$categories = array_map( 'intval', $categories );
		if ( ! isset( $taxonomies['category'] ) ) {
			$taxonomies['category'] = [];
		}
		$taxonomies['category'] = array_merge( $taxonomies['category'], $categories );
	}
	$tags = wpcp_get_post_meta( $campaign_id, '_tags', [] );
	if ( ! empty( $tags ) ) {
		$tags = array_map( 'intval', $tags );
		if ( ! isset( $taxonomies['post_tag'] ) || empty( $taxonomies['post_tag'] ) ) {
			$taxonomies['post_tag'] = [];
		}
		$taxonomies['post_tag'] = array_merge( $taxonomies['post_tag'], $tags );
	}

	return $taxonomies;
}

add_filter( 'wpcp_post_taxonomy', 'wpcp_set_post_taxonomy', 10, 2 );

/**
 * Set the author as the settings
 * since 1.0.0
 *
 * @param $author
 * @param $campaign_id
 *
 * @return array|null|string
 */
function wpcp_set_post_author( $author, $campaign_id ) {
	$custom_author = wpcp_get_post_meta( $campaign_id, '_author', 1 );
	if ( ! empty( $custom_author ) ) {
		return $custom_author;
	}

	return $author;
}

add_filter( 'wpcp_post_author', 'wpcp_set_post_author', 10, 2 );

/**
 * 5 Star Rating banner.
 *
 * @param string $text
 *
 * @return string
 * @since 1.0.4
 *
 */
function wpcp_admin_footer_text( $text ) {
	$screen = get_current_screen();

	if ( 'wp_content_pilot' == $screen->post_type ) {
		$star_url = 'https://wordpress.org/support/plugin/wp-content-pilot/reviews/?filter=5#new-post';
		$text     = sprintf( __( 'If you like <strong>WP Content Pilot</strong> please leave us a <a href="%s" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a> rating. Your Review is very important to us as it helps us to grow more.', 'wp-content-pilot' ), $star_url );
	}

	return $text;
}

add_filter( 'admin_footer_text', 'wpcp_admin_footer_text' );

/**
 * Send notification mail after post insert
 *
 * @param $post_id
 * @param $campaign_id
 * @param $article
 * @param $keyword
 *
 * @since 1.0.9
 *
 */
function wpcp_post_publish_mail_notification( $post_id, $campaign_id, $article, $keyword ) {
	$send_mail = wpcp_get_settings( 'post_publish_mail', 'wpcp_settings_misc', '' );
	if ( $send_mail != 'on' ) {
		return ;
	}
	$author_id = get_post_field( 'post_author', $post_id );
	$to        = get_the_author_meta( 'user_email', $author_id );
	$title     = $article['title'];
	$excerpt   = $article['excerpt'];
	$post_link = get_the_permalink( $post_id );
	$subject   = __( 'Post Publish', 'wp-content-pilot' );
	$body      = sprintf( __( "<h4>Post Title: %s</h4>
                    <h5>Post Excerpt</h5>
                    <p>%s</p>
                    <a href='%s'>View Post</a>", 'wp-content-pilot' ), esc_html( $title ), $excerpt, esc_url( $post_link )
	);
	$headers   = array( 'Content-Type: text/html; charset=UTF-8' );

	wp_mail( $to, $subject, $body, $headers );
}

add_action( 'wpcp_after_post_publish', 'wpcp_post_publish_mail_notification', 10, 4 );
