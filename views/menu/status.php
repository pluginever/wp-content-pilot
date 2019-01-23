<?php

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
global $wp_version;

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
	'value' => sprintf(__( '%s seconds', 'wp-content-pilot' ), ini_get('max_execution_time')),
);

$information['language'] = array(
	'label' => __( 'language', 'wp-content-pilot' ),
	'value' => get_bloginfo( 'language' ),
);

?>


<div class="wrap" id="wpcp-page">

	<h1 class="wp-heading-inline"> Status > WP Content Pilot </h1>

	<div class="notice notice-info">
		<p><?php _e( 'Experiencing an issue and need to contact WP Content Pilot support? Click the link below to get debug information you can send to us.', 'wp-content-pilot' ) ?></p>

		<p class="notice-links">
			<a href="#" class="button button-primary"> <?php _e( 'Get System Information', 'wp-content-pilot' ) ?> </a>
		</p>
	</div>

	<table class="wp-list-table widefat fixed" cellspacing="0" style="width:100%;margin-left:auto;margin-right:auto;">
		<thead>
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

</div>
