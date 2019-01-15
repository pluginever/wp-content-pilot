<?php

class WPCP_Install {
	public function __construct() {
		register_activation_hook( WPCP_FILE, array( $this, 'activate' ) );
		register_deactivation_hook( WPCP_FILE, array( $this, 'deactivate' ) );
	}

	public function activate() {
		$current_db_version   = get_option( 'wpcp_db_version', null );
		$current_wpcp_version = get_option( 'wpcp_version', null );
		$this->create_tables();
		$this->populate();
		$this->create_cron_jobs();
		//save db version
		if ( is_null( $current_wpcp_version ) ) {
			update_option( 'wpcp_version', WPCP_VERSION );
		}
		//save db version
		if ( is_null( $current_db_version ) ) {
			update_option( 'wpcp_db_version', '1.00' );
		}
		//save install date
		if ( false == get_option( 'wpcp_install_date' ) ) {
			update_option( 'wpcp_install_date', current_time( 'timestamp' ) );
		}
	}

	public function create_tables() {
		global $wpdb;
		$collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			if ( ! empty( $wpdb->charset ) ) {
				$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {
				$collate .= " COLLATE $wpdb->collate";
			}
		}
		//todo remake table structure

		$table_schema = [
			"CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpcp_links` (
                `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
                `camp_id` INT(11) NOT NULL,
                `post_id` INT(11) DEFAULT NULL,
                `keyword` varchar(191) DEFAULT NULL,
                `camp_type` varchar(191) DEFAULT NULL,
                `status` VARCHAR(100) NOT NULL,
                `url` VARCHAR (255) DEFAULT NULL,
                `title` VARCHAR (255) DEFAULT NULL,
                `image` VARCHAR (255) DEFAULT NULL,
                `content` longtext DEFAULT NULL,
                `raw_content` longtext DEFAULT NULL,
                `score` INT(3) DEFAULT 0,
                `gmt_date` datetime DEFAULT CURRENT_TIMESTAMP,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                  UNIQUE (url)
            ) $collate;",
			"CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpcp_logs` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `camp_id` int(11) DEFAULT NULL,
                `keyword` varchar(255) DEFAULT NULL,
                `log_level` varchar(20) NOT NULL DEFAULT '',
                `message` varchar(255) DEFAULT NULL,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) $collate;",
		];
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		foreach ( $table_schema as $table ) {
			dbDelta( $table );
		}
	}

	public function populate() {
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

	/**
	 * Create cron jobs
	 *
	 * @return void
	 */
	public function create_cron_jobs() {
		wp_schedule_event( time(), 'once_a_minute', 'wpcp_per_minute_scheduled_events' );
		wp_schedule_event( time(), 'daily', 'wpcp_daily_scheduled_events' );
	}

	public function deactivate() {
		wp_clear_scheduled_hook( 'wpcp_per_minute_scheduled_events' );
		wp_clear_scheduled_hook( 'wpcp_daily_scheduled_events' );
	}
}
