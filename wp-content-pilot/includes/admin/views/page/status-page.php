<?php
defined('ABSPATH')|| exit();
global $wpdb;
global $wp_version;

$loaded_extensions      = get_loaded_extensions();
$extensions             = array();
$extensions['dom']      = in_array( 'dom', $loaded_extensions ) ? '<span style="color: green">dom</span>' : '<span style="color: red">dom</span>';
$extensions['xml']      = in_array( 'xml', $loaded_extensions ) ? '<span style="color: green">xml</span>' : '<span style="color: red">xml</span>';
$extensions['mbstring'] = in_array( 'mbstring', $loaded_extensions ) ? '<span style="color: green">mbstring</span>' : '<span style="color: red">mbstring</span>';
$extensions['curl'] = in_array( 'curl', $loaded_extensions ) ? '<span style="color: green">curl</span>' : '<span style="color: red">curl</span>';


// Test POST requests.
function wpcp_test_post_reponse() {

	$post_response = wp_safe_remote_post(
		'https://www.paypal.com/cgi-bin/webscr',
		array(
			'timeout'     => 10,
			'httpversion' => '1.1',
			'body'        => array(
				'cmd' => '_notify-validate',
			),
		)
	);


	if ( ! is_wp_error( $post_response ) && $post_response['response']['code'] >= 200 && $post_response['response']['code'] < 300 ) {
		return true;
	}

	return false;
}

// Test GET requests.
function wpcp_test_get_reponse() {

	$get_response = wp_safe_remote_get( 'https://woocommerce.com/wc-api/product-key-api?request=ping&network=' . ( is_multisite() ? '1' : '0' ) );

	if ( ! is_wp_error( $get_response ) && $get_response['response']['code'] >= 200 && $get_response['response']['code'] < 300 ) {
		return true;
	}

	return false;
}

$information = array();

$information['wordpress_version'] = array(
	'label' => __( 'WordPress version', 'wp-content-pilot' ),
	'value' => ! empty( $wp_version ) ? $wp_version : '-',
);

$information['wpcp_version'] = array(
	'label' => __( 'WP Content Pilot version', 'wp-content-pilot' ),
	'value' => WPCP_VERSION,
);

$information['php_version'] = array(
	'label' => __( 'PHP version', 'wp-content-pilot' ),
	'value' => ! empty( PHP_VERSION ) ? PHP_VERSION : '-',
);

$information['mysql_version'] = array(
	'label' => __( 'MySQL version', 'wp-content-pilot' ),
	'value' => $wpdb->get_var( "SELECT VERSION() AS version" ),
);

$information['curl_version'] = array(
	'label' => __( 'cURL version', 'wp-content-pilot' ),
	'value' => function_exists( 'curl_version' ) ? curl_version()['version'] : '',
);

$information['curlssl_version'] = array(
	'label' => __( 'cURL SSL version', 'wp-content-pilot' ),
	'value' => function_exists( 'curl_version' ) ? curl_version()['ssl_version'] : '',
);

$information['cron_url'] = array(
	'label' => __( 'WP-Cron url', 'wp-content-pilot' ),
	'value' => site_url( 'wp-cron.php' ),
);

$information['docroot'] = array(
	'label' => __( 'Document root', 'wp-content-pilot' ),
	'value' => $_SERVER['DOCUMENT_ROOT'],
);

$information['server'] = array(
	'label' => __( 'SERVER', 'wp-content-pilot' ),
	'value' => $_SERVER['SERVER_SOFTWARE'],
);

$information['os'] = array(
	'label' => __( 'Operating System', 'wp-content-pilot' ),
	'value' => PHP_OS,
);

$information['maxexectime'] = array(
	'label' => __( 'Maximum execution time', 'wp-content-pilot' ),
	'value' => sprintf( __( '%s seconds', 'wp-content-pilot' ), ini_get( 'max_execution_time' ) ),
);

$information['language'] = array(
	'label' => __( 'Language', 'wp-content-pilot' ),
	'value' => get_bloginfo( 'language' ),
);

$information['mysql_encoding'] = array(
	'label' => __( 'MySQL Client encoding', 'wp-content-pilot' ),
	'value' => ! empty( DB_CHARSET ) ? DB_CHARSET : '-',
);

$information['max_upload'] = array(
	'label' => __( 'Max Upload Size', 'wp-content-pilot' ),
	'value' => ini_get( 'memory_limit' ),
);

$information['remote_post'] = array(
	'label' => __( 'Remote Post', 'wp-content-pilot' ),
	'value' => wpcp_test_post_reponse() ? 'ON' : 'OFF',
);

$information['remote_get'] = array(
	'label' => __( 'Remote Get', 'wp-content-pilot' ),
	'value' => wpcp_test_get_reponse() ? 'ON' : 'OFF',
);
global $wpdb;

$information['links_table'] = array(
	'label' => __( 'Links Table', 'wp-content-pilot' ),
	'value' => !empty($wpdb->query("DESCRIBE {$wpdb->prefix}wpcp_links")) ? 'Yes' : 'No',
);

$information['log_table'] = array(
	'label' => __( 'Log Table', 'wp-content-pilot' ),
	'value' => !empty($wpdb->query("DESCRIBE {$wpdb->prefix}wpcp_logs")) ? 'Yes' : 'No',
);

$information['per_minute_cron'] = array(
	'label' => __( 'Per Minute Cron Installed', 'wp-content-pilot' ),
	'value' => !empty(wp_get_scheduled_event('wpcp_per_minute_scheduled_events')) ? 'Yes' : 'No',
);
$cron_status = wpcp_check_cron_status();
$information['cron_running'] = array(
	'label' => __( 'Is CRON running', 'wp-content-pilot' ),
	'value' => is_wp_error($cron_status)? 'No '.esc_html($cron_status->get_error_message()): 'Yes' ,
);


$information['loaded_extensions']   = array(
	'label' => __( 'Loaded PHP Extensions', 'wp-content-pilot' ),
	'value' => implode( ', ', $loaded_extensions ),
);
$information['required_extensions'] = array(
	'label' => __( 'Required PHP Extensions', 'wp-content-pilot' ),
	'value' => implode( ', ', $extensions ),
);

//todo check if wpcp_links and wpcp_logs table exists or not

/**
 * Campagin Information
 */

$campaigns_info['count'] = array(
	'label' => __( 'Total Campaigns', 'wp-content-pilot' ),
);
$campaigns               = $wpdb->get_results( $wpdb->prepare( "SELECT count(pm.post_id) AS total_post,pm.meta_value as status  FROM {$wpdb->prefix}posts p  INNER JOIN {$wpdb->prefix}postmeta pm  ON p.ID = pm.post_id WHERE p.post_type = %s AND p.post_status = %s AND pm.meta_key = %s GROUP BY pm.meta_value", 'wp_content_pilot', 'publish', '_campaign_status' ) );
$total                   = 0;
$string                  = '';
if ( ! empty( $campaigns ) ) {
	foreach ( $campaigns as $campaign ) {
		$total  += $campaign->total_post;
		$string .= "$campaign->status $campaign->total_post, ";
	}
	$total = sprintf( '%d ( %s)', intval( $total ), sanitize_text_field( $string ) );
}
$campaigns_info['count']['value'] = $total;

?>


<div class="wrap" id="wpcp-page">

	<h1 class="wp-heading-inline"> <?php _e( 'Status > WP Content Pilot', 'wp-content-pilot' ) ?> </h1>

	<div class="notice notice-info">
		<p><?php _e( 'Experiencing an issue and need to contact WP Content Pilot support? Click the link below to get debug information you can send to us.', 'wp-content-pilot' ) ?></p>

		<textarea id="system-info" class="widefat" readonly rows="15" style="display: none; color: #32373c; background-color: #eee; padding: 30px;">##System Information## &#013<?php
			foreach ( $information as $info ) {
				echo sprintf( '%s: %s &#013&#013', $info['label'], $info['value'] );
			}

			echo sprintf('##Campaigns Information## &#013 %s: %s', $campaigns_info['count']['label'], $campaigns_info['count']['value'])

			?></textarea>

		<p class="notice-links">
			<a href="#" id="get-info" class="button button-primary show"> <?php _e( 'Get System Information', 'wp-content-pilot' ) ?> </a>
		</p>
	</div>

	<table class="wp-list-table widefat fixed" cellspacing="0" style="width:100%;margin-left:auto;margin-right:auto;">
		<thead>
		<tr>
			<th colspan="2"><h4 style="margin: 5px 0"><?php _e( 'System Information', 'wp-content-pilot' ) ?></h4></th>
		</tr>
		<tr>
			<th width="35%"><?php _e( 'Setting', 'wp-content-pilot' ) ?></th>
			<th><?php _e( 'Value', 'wp-content-pilot' ) ?></th>
		</tr>
		</thead>

		<tfoot>
		<tr>
			<th><?php _e( 'Setting', 'wp-content-pilot' ) ?></th>
			<th><?php _e( 'Value', 'wp-content-pilot' ) ?></th>
		</tr>
		</tfoot>

		<tbody>

		<?php foreach ( $information as $info ) { ?>
			<tr>
				<td><?php echo $info['label'] ?></td>
				<td><?php echo $info['value'] ?></td>
			</tr>
		<?php } ?>

		</tbody>
	</table>

	<table class="wp-list-table widefat fixed" cellspacing="0" style="margin-top: 15px;width:100%;margin-left:auto;margin-right:auto;">
		<thead>
		<tr>
			<th colspan="2"><h4 style="margin: 5px 0"><?php _e( 'Campaigns Information', 'wp-content-pilot' ) ?></h4></th>
		</tr>
		<tr>
			<th width="35%"><?php _e( 'Label', 'wp-content-pilot' ) ?></th>
			<th><?php _e( 'Value', 'wp-content-pilot' ) ?></th>
		</tr>
		</thead>

		<tfoot>
		<tr>
			<th><?php _e( 'Label', 'wp-content-pilot' ) ?></th>
			<th><?php _e( 'Value', 'wp-content-pilot' ) ?></th>
		</tr>
		</tfoot>

		<tbody>

		<?php foreach ($campaigns_info as $info){ ?>
			<tr>
				<td><?php echo $info['label'] ?></td>
				<td><?php echo $info['value'] ?></td>
			</tr>
		<?php } ?>

		</tbody>
	</table>

</div>

<script type="text/javascript">
	jQuery(document).ready(function ($) {

		var $info = $('#system-info');

		$('#get-info.show').click(function (e) {
			e.preventDefault();
			$info.show();
			$(this).text('<?php _e( "Copy System Information", "wp-content-pilot" ) ?>');
			$(this).removeClass('show');
			setTimeout(function () {
				$('#get-info').addClass('copy')
			}, 1000);
		});

		$(document).on('click', '#get-info.copy', function (e) {
			e.preventDefault();
			$info.select();
			document.execCommand("copy");
			alert('<?php _e( 'Copied', 'wp-content-pilot' ) ?>');
		});

	});
</script>
