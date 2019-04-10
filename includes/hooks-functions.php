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
 * @param $author
 * @param $campaign_id
 *
 * @return array|null|string
 */
function wpcp_set_post_author( $author, $campaign_id ) {
	$custom_author =  wpcp_get_post_meta( $campaign_id, '_author', 1 );
	if(!empty($custom_author)){
		return $custom_author;
	}
	 return $author;
}

add_filter( 'wpcp_post_author', 'wpcp_set_post_author', 10, 2 );
