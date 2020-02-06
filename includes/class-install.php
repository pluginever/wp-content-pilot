<?php
defined( 'ABSPATH' ) || exit();

class WPCP_Install {

	public static function activate() {
		$current_db_version   = get_option( 'wpcp_db_version', null );
		$current_wpcp_version = get_option( 'wpcp_version', null );
		self::create_tables();
		self::populate();
		//save db version
		if ( is_null( $current_wpcp_version ) ) {
			update_option( 'wpcp_version', WPCP_VERSION );
		}
		//save db version
		if ( is_null( $current_db_version ) ) {
			update_option( 'wpcp_db_version', WPCP_VERSION );
		}
		//save install date
		if ( false == get_option( 'wpcp_install_date' ) ) {
			update_option( 'wpcp_install_date', current_time( 'timestamp' ) );
		}
	}

	public static function create_tables() {
		global $wpdb;
		$wpdb->hide_errors();

		$table_schema = [
			"CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpcp_links` (
                `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
                `camp_id` INT(11) NOT NULL,
                `camp_type` varchar(191) DEFAULT NULL,
                `url` text DEFAULT NULL,
                `title` text DEFAULT NULL,
                `keyword` varchar(191) DEFAULT NULL,
                `status` VARCHAR(100) NOT NULL,
                `post_id` INT(11) DEFAULT NULL,
                `pub_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`id`)
            )  CHARACTER SET utf8 COLLATE utf8_general_ci;",



			"CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpcp_logs` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `camp_id` int(11) DEFAULT NULL,
                `level` varchar(20) NOT NULL DEFAULT '',
                `message` text DEFAULT NULL,
                `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`id`)
            )  CHARACTER SET utf8 COLLATE utf8_general_ci;",
		];
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		foreach ( $table_schema as $table ) {
			dbDelta( $table );
		}
	}

	public static function populate() {
		$article_settings = wpcp_get_settings( 'wpcp_settings_article' );
		if ( empty( $article_settings['banned_hosts'] ) ) {
			$hosts                            = array(
				'wikipedia',
				'youtube',
				'google',
				'bing',
			);
			$article_settings['banned_hosts'] = implode( PHP_EOL, $hosts );
			update_option( 'wpcp_settings_article', $article_settings );
		}
	}


	public static function deactivate() {
		wp_clear_scheduled_hook( 'wpcp_per_minute_scheduled_events' );
		wp_clear_scheduled_hook( 'wpcp_daily_scheduled_events' );
	}
}
