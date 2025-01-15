<?php
defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

// Load WP_List_Table if not loaded.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
/**
 * Class WPCP_Logs_List_Table
 *
 * @since 1.0.0
 */
class WPCP_Logs_List_Table extends WP_List_Table {
	/**
	 * Number of results to show per page.
	 *
	 * @var string $per_page Per page item count.
	 *
	 * @since 1.0.0
	 */
	public $per_page = 20;

	/**
	 *
	 * Total number of discounts.
	 *
	 * @var string $total_count Total number of discounts.
	 *
	 * @since 1.0.0
	 */
	public $total_count;

	/**
	 * Active number of error.
	 *
	 * @var string $error_count Active number of error.
	 *
	 * @since 1.0.0
	 */
	public $error_count;

	/**
	 * Active number of warning.
	 *
	 * @var string $warning_count Active number of warning.
	 *
	 * @since 1.0.0
	 */
	public $warning_count;

	/**
	 * Active number of info.
	 *
	 * @var string $info_count Active number of info.
	 *
	 * @since 1.0.0
	 */
	public $info_count;

	/**
	 * Base URL.
	 *
	 * @var string Base URL.
	 *
	 * @since 1.0.0
	 */
	public $base_url;

	/**
	 * EAccounting_Products_List_Table constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'log',
				'plural'   => 'logs',
				'ajax'     => false,
			)
		);
		$this->base_url = admin_url( 'edit.php?post_type=wp_content_pilot&page=wpcp-logs' );
		$this->process_bulk_action();
	}

	/**
	 * Set up the final data for the table.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function prepare_items() {
		$per_page              = $this->per_page;
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->process_bulk_action();
		$data  = $this->get_results();
		$level = isset( $_GET['level'] ) ? sanitize_text_field( wp_unslash( $_GET['level'] ) ) : 'any';

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
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);
	}

	/**
	 * Message to be displayed when there are no items.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function no_items() {
		printf( /* translators: 1: Plural of log or logs string */ esc_html__( 'No %s found.', 'wp-content-pilot' ), esc_html( $this->_args['plural'] ) );
	}

	/**
	 * Show the search field.
	 *
	 * @param string $text Label for the search box.
	 * @param string $input_id ID of the search box.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) . '" />';
		}
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>"/>
			<?php submit_button( $text, 'button', false, false, array( 'ID' => 'search-submit' ) ); ?>
		</p>
		<?php
	}

	/**
	 * Retrieve the view types.
	 *
	 * @since 1.0.0
	 * @return array $views All the views available.
	 */
	public function get_views() {
		$current       = isset( $_GET['level'] ) ? sanitize_key( $_GET['level'] ) : '';
		$total_count   = '&nbsp;<span class="count">(' . $this->total_count . ')</span>';
		$info_count    = '&nbsp;<span class="count">(' . $this->info_count . ')</span>';
		$warning_count = '&nbsp;<span class="count">(' . $this->warning_count . ')</span>';
		$error_count   = '&nbsp;<span class="count">(' . $this->error_count . ')</span>';
		$views         = array(
			'all'     => sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( 'level', $this->base_url ), 'all' === $current || '' === $current ? ' class="current"' : '', __( 'All', 'wp-content-pilot' ) . $total_count ),
			'info'    => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'level', 'info', $this->base_url ), 'info' === $current ? ' class="current"' : '', __( 'Info', 'wp-content-pilot' ) . $info_count ),
			'warning' => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'level', 'warning', $this->base_url ), 'warning' === $current ? ' class="current"' : '', __( 'Warning', 'wp-content-pilot' ) . $warning_count ),
			'error'   => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'level', 'error', $this->base_url ), 'error' === $current ? ' class="current"' : '', __( 'Error', 'wp-content-pilot' ) . $error_count ),
		);

		return $views;
	}

	/**
	 * Extra table nav.
	 *
	 * @param string $which The position string.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function extra_tablenav( $which ) {
		if ( 'top' === $which ) {
			printf( '<a href="#" class="button button-secondary" id="wpcp-clear-logs" data-nonce="%s">%s</a>', sanitize_key( wp_create_nonce( 'wpcp_clear_logs' ) ), esc_html__( 'Clear Logs', 'wp-content-pilot' ) );
		}
	}

	/**
	 * Retrieve the table columns.
	 *
	 * @since 1.0.0
	 * @return array $columns Array of all the list table columns.
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
	 * Retrieve the table's sortable columns.
	 *
	 * @since 1.0.0
	 * @return array Array of all the sortable columns.
	 */
	public function get_sortable_columns() {
		return array(
			'campaign' => array( 'camp_id', false ),
			'level'    => array( 'level', false ),
			'log'      => array( 'message', false ),
			'date'     => array( 'created_at', false ),
		);
	}

	/**
	 * Default columns.
	 *
	 * @param object $item Item.
	 * @param string $column_name Column name.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'campaign':
				$campaign_id = $item->camp_id ?? '';
				if ( empty( $campaign_id ) ) {
					return '&mdash;';
				}
				$title = wp_trim_words( get_the_title( $campaign_id ), 2 );
				$url   = admin_url( "post.php?post={$item->camp_id}&action=edit" );

				return sprintf( '<a href="%s">#(%d) - %s</a>', $url, $campaign_id, $title );
			case 'level':
				return $item->level ? sprintf( '<span class="%s">%s</span>', sanitize_html_class( strtolower( $item->level ) ), $item->level ) : '&mdash;';
			case 'log':
				return $item->message ? wp_kses_post( $item->message ) : '&mdash;';
			case 'date':
				return $item->created_at;
			default:
				return '&mdash;';
		}
	}

	/**
	 * Process the bulk actions.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function process_bulk_action() {}

	/**
	 * Retrieve all the data for all the discount codes.
	 *
	 * @since 1.0.0
	 * @return array $get_results Array of all the data for the discount codes.
	 */
	public function get_results() {
		$per_page = $this->per_page;
		$orderby  = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'created_at';
		$order    = isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : 'DESC';
		$level    = isset( $_GET['level'] ) ? sanitize_key( $_GET['level'] ) : '';
		$search   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : null;

		$args = array(
			'per_page' => $per_page,
			'page'     => isset( $_GET['paged'] ) ? intval( wp_unslash( $_GET['paged'] ) ) : 1,
			'orderby'  => $orderby,
			'order'    => $order,
			'level'    => $level,
			'search'   => $search,
		);

		if ( array_key_exists( $orderby, $this->get_sortable_columns() ) && 'name' !== $orderby ) {
			$args['orderby'] = $orderby;
		}

		$this->total_count   = wpcp_get_logs( array_merge( $args, array( 'level' => '' ) ), true );
		$this->info_count    = wpcp_get_logs( array_merge( $args, array( 'level' => 'info' ) ), true );
		$this->warning_count = wpcp_get_logs( array_merge( $args, array( 'level' => 'warning' ) ), true );
		$this->error_count   = wpcp_get_logs( array_merge( $args, array( 'level' => 'error' ) ), true );

		return wpcp_get_logs( $args );
	}
}
