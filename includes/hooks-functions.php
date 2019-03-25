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
