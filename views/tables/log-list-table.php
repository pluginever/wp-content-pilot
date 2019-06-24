<?php
if ( ! class_exists ( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table class
 */
class WPCP_Log_List_Table extends \WP_List_Table {

    function __construct() {
        parent::__construct( array(
            'singular' => 'log',
            'plural'   => 'logs',
            'ajax'     => false
        ) );
    }

    function get_table_classes() {
        return array( 'widefat', 'fixed', 'striped', $this->_args['plural'] );
    }

    /**
     * Message to show if no designation found
     *
     * @return void
     */
    function no_items() {
        _e( 'No log found', 'wp-content-pilot' );
    }

    /**
     * Default column values if no callback found
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    function column_default( $item, $column_name ) {

        switch ( $column_name ) {
            case 'camp_id':
                if( $item->camp_id ){
                    $title = get_the_title($item->camp_id) ."({$item->camp_id })";
                    $permalink = admin_url("post.php?post={$item->camp_id}&action=edit");
                    return "<a href='{$permalink}' target='_blank'>{$title}</a>";
                }else{
                    return ' - ';
                }

            case 'level':
                return $item->log_level == 'log'? __('Normal', 'wp-content-pilot') : $item->log_level;

            case 'message':
                return stripslashes($item->message);

            case 'date':
                return $item->created_at;

            default:
                return isset( $item->$column_name ) ? $item->$column_name : '';
        }
    }

    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'camp_id'      => __( 'Campaign', 'wp-content-pilot' ),
            'level'      => __( 'Level', 'wp-content-pilot' ),
            'message'      => __( 'Message', 'wp-content-pilot' ),
            'date'      => __( 'Date', 'wp-content-pilot' ),

        );

        return $columns;
    }


    /**
     * Get sortable columns
     *
     * @return array
     */
    function get_sortable_columns() {
        $sortable_columns = array(
            'name' => array( 'name', true ),
        );

        return $sortable_columns;
    }

    /**
     * Set the bulk actions
     *
     * @return array
     */
    function get_bulk_actions() {
        return array();
    }


    function get_logs($args){
        $defaults = array(
            'limit'     => 20,
            'offset'     => 0,
        );

        $args      = wp_parse_args( $args, $defaults );

        global $wpdb;
        $cache_key = 'wpcp-log-all';
        $items     = wp_cache_get( $cache_key, 'wp-content-pilot' );

        if ( false === $items ) {
            $prepare = $wpdb->prepare('SELECT * FROM wp_wpcp_logs ORDER BY `created_at` DESC LIMIT %d, %d', $args['offset'],$args['limit'] );

            $items = $wpdb->get_results( $prepare );
            wp_cache_set( $cache_key, $items, 'wp-content-pilot' );
        }

        return $items;
    }

    /**
     * Prepare the class items
     *
     * @return void
     */
    function prepare_items() {

        $columns               = $this->get_columns();
        $hidden                = array( );
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $per_page              = 50;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page -1 ) * $per_page;
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '2';

        // only ncessary because we have sample data
        $args = array(
            'offset' => $offset,
            'number' => $per_page,
            'orderby' => 'id',
            'order'      => 'DESC',
        );

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
            $args['order']   = $_REQUEST['order'] ;
        }

        $this->items  = $this->get_logs( $args );
        global $wpdb;
        $this->set_pagination_args( array(
            'total_items' => $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wpcp_logs' ),
            'per_page'    => $per_page
        ) );
    }
}
