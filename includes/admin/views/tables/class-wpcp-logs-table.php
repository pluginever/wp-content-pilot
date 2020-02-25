<?php
defined( 'ABSPATH' ) || exit();

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class WPCP_Logs_List_Table extends WP_List_Table {
	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $per_page = 20;

	/**
	 *
	 * Total number of discounts
	 * @var string
	 * @since 1.0.0
	 */
	public $total_count;

	/**
	 * Active number of account
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $error_count;

	/**
	 * Active number of account
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $warning_count;

	/**
	 * Active number of account
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $info_count;

	/**
	 * Base URL
	 * @var string
	 */
	public $base_url;

	/**
	 * EAccounting_Products_List_Table constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'log',
			'plural'   => 'logs',
			'ajax'     => false,
		) );
		$this->base_url = admin_url( 'edit.php?post_type=wp_content_pilot&page=wpcp-logs' );
		$this->process_bulk_action();
	}

	/**
	 * Setup the final data for the table
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function prepare_items() {
		$per_page              = $this->per_page;
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->process_bulk_action();
		$data  = $this->get_results();
		$level = isset( $_GET['level'] ) ? $_GET['level'] : 'any';

		switch ( $level ) {
			case 'info':
				$total_items = $this->info_count;
				break;
			case 'warning':
				$total_items = $this->warning_count;
				break;
			case 'error':
				$total_items = $this->error_count;
				break;
			case 'any':
			default:
				$total_items = $this->total_count;
				break;
		}
		$this->items = $data;
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		) );
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 1.0.0
	 */
	function no_items() {
		echo sprintf( __( 'No %s found.', 'wp-content-pilot' ), $this->_args['plural'] );
	}

	/**
	 * Show the search field
	 *
	 * @param string $text Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>"/>
			<?php submit_button( $text, 'button', false, false, array( 'ID' => 'search-submit' ) ); ?>
		</p>
		<?php
	}

	/**
	 * Retrieve the view types
	 *
	 * @return array $views All the views available
	 * @since 1.0.0
	 */
	public function get_views() {
		$current       = isset( $_GET['level'] ) ? sanitize_key( $_GET['level'] ) : '';
		$total_count   = '&nbsp;<span class="count">(' . $this->total_count . ')</span>';
		$info_count    = '&nbsp;<span class="count">(' . $this->info_count . ')</span>';
		$warning_count = '&nbsp;<span class="count">(' . $this->warning_count . ')</span>';
		$error_count   = '&nbsp;<span class="count">(' . $this->error_count . ')</span>';
		$views         = array(
			'all'     => sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( 'level', $this->base_url ), $current === 'all' || $current == '' ? ' class="current"' : '', __( 'All', 'wp-content-pilot' ) . $total_count ),
			'info'    => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'level', 'info', $this->base_url ), $current === 'info' ? ' class="current"' : '', __( 'Info', 'wp-content-pilot' ) . $info_count ),
			'warning' => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'level', 'warning', $this->base_url ), $current === 'warning' ? ' class="current"' : '', __( 'Warning', 'wp-content-pilot' ) . $warning_count ),
			'error'   => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'level', 'error', $this->base_url ), $current === 'error' ? ' class="current"' : '', __( 'Error', 'wp-content-pilot' ) . $error_count ),
		);

		return $views;
	}

	function extra_tablenav( $which ) {
		if ( $which == "top" ){
			echo sprintf( '<a href="#" class="button button-secondary" id="wpcp-clear-logs" data-nonce="%s">%s</a>',  wp_create_nonce('wpcp_clear_logs'), __('Clear Logs', 'wp-content-pilot'));
		}
	}

	/**
	 * Retrieve the table columns
	 *
	 * @return array $columns Array of all the list table columns
	 * @since 1.0.0
	 */
	public function get_columns() {
		$columns = array(
			'campaign' => __( 'Campaign', 'wp-content-pilot' ),
			'level'    => __( 'Level', 'wp-content-pilot' ),
			'log'      => __( 'Log', 'wp-content-pilot' ),
			'date'     => __( 'Date', 'wp-content-pilot' ),
		);

		return $columns;
	}

	/**
	 * Retrieve the table's sortable columns
	 *
	 * @return array Array of all the sortable columns
	 *
	 * @since 1.0.0
	 */
	public function get_sortable_columns() {
		return array(
			'campaign' => array( 'camp_id', false ),
			'level'    => array( 'level', false ),
			'log'      => array( 'message', false ),
			'date'     => array( 'created_at', false )
		);
	}

	/**
	 * @param object $item
	 * @param string $column_name
	 *
	 * @return string;
	 * @since 1.0.0
	 */
	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'campaign':
				$campaign_id = $item->camp_id ? $item->camp_id : '';
				if ( empty( $campaign_id ) ) {
					return '&mdash;';
				}
				$title = wp_trim_words( get_the_title( $campaign_id ), 2 );
				$url   = admin_url( "post.php?post={$item->camp_id}&action=edit" );

				return sprintf( '<a href="%s">#(%d) - %s</a>', $url, $campaign_id, $title );
				break;
			case 'level':
				return $item->level ? sprintf( '<span class="%s">%s</span>', sanitize_html_class( strtolower( $item->level) ), $item->level ) : '&mdash;';
				break;
			case 'log':
				return $item->message ? strip_tags( $item->message ) : '&mdash;';
				break;
			case 'date':
				return $item->created_at;
			default:
				return '&mdash;';
				break;
		}
	}

	/**
	 * Process the bulk actions
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function process_bulk_action() {

	}

	/**
	 * Retrieve all the data for all the discount codes
	 *
	 * @return array $get_results Array of all the data for the discount codes
	 * @since 1.0.0
	 */
	public function get_results() {
		$per_page = $this->per_page;
		$orderby  = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'created_at';
		$order    = isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : 'DESC';
		$level    = isset( $_GET['level'] ) ? sanitize_key( $_GET['level'] ) : '';
		$search   = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : null;

		$args = array(
			'per_page' => $per_page,
			'page'     => isset( $_GET['paged'] ) ? $_GET['paged'] : 1,
			'orderby'  => $orderby,
			'order'    => $order,
			'level'    => $level,
			'search'   => $search
		);


		if ( array_key_exists( $orderby, $this->get_sortable_columns() ) && 'name' != $orderby ) {
			$args['orderby'] = $orderby;
		}

		$this->total_count   = wpcp_get_logs( array_merge( $args, array( 'level' => '' ) ), true );
		$this->info_count    = wpcp_get_logs( array_merge( $args, array( 'level' => 'info' ) ), true );
		$this->warning_count = wpcp_get_logs( array_merge( $args, array( 'level' => 'warning' ) ), true );
		$this->error_count   = wpcp_get_logs( array_merge( $args, array( 'level' => 'error' ) ), true );

		$results = wpcp_get_logs( $args );

		return $results;
	}

}
