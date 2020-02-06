<?php
defined( 'ABSPATH' ) || exit();

function wpcp_get_logs( $args = array(), $count = false ) {
	global $wpdb;
	$default     = array(
		'search'   => '',
		'level'     => '',
		'orderby'  => 'created_at',
		'order'    => 'DESC',
		'per_page' => 20,
		'page'     => 1,
		'offset'   => 0,
	);
	$args        = wp_parse_args( $args, $default );
	$query_from  = "FROM $wpdb->wpcp_logs";
	$query_where = 'WHERE 1=1';

	if(!empty( $args['level'])){
		$level = strtoupper( $args['level']);
		$query_where .= $wpdb->prepare( " AND level=%s ", $level);
	}
	//ordering
	$order         = isset( $args['order'] ) ? esc_sql( strtoupper( $args['order'] ) ) : 'ASC';
	$order_by      = esc_sql( $args['orderby'] );
	$query_orderby = sprintf( " ORDER BY %s %s", $order_by, $order );
	if($args['orderby'] !== 'id'){
		$query_orderby .= ' , id DESC ';
	}

	// limit
	if ( isset( $args['per_page'] ) && $args['per_page'] > 0 ) {
		if ( $args['offset'] ) {
			$query_limit = $wpdb->prepare( 'LIMIT %d, %d', $args['offset'], $args['per_page'] );
		} else {
			$query_limit = $wpdb->prepare( 'LIMIT %d, %d', $args['per_page'] * ( $args['page'] - 1 ), $args['per_page'] );
		}
	}

	if ( $count ) {
		return $wpdb->get_var( "SELECT count($wpdb->wpcp_logs.id) $query_from $query_where" );
	}


	return $wpdb->get_results( "SELECT * $query_from $query_where $query_orderby $query_limit" );
}
