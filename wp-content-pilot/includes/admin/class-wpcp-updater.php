<?php
defined( 'ABSPATH' ) || exit();

class WPCP_Updater {
	/**
	 * The upgrades
	 *
	 * @var array
	 */
	private static $upgrades = array(
		'1.0.4' => 'updates/update-1.0.4.php',
		'1.1.2' => 'updates/update-1.1.2.php',
		'1.2.0' => 'updates/update-1.2.0.php',
		'1.2.3' => 'updates/update-1.2.3.php',
		'1.2.4' => 'updates/update-1.2.4.php',
		'1.2.7' => 'updates/update-1.2.7.php',
	);

	/**
	 * Get the plugin version
	 *
	 * @return string
	 */
	public function get_version() {
		return get_option( 'wpcp_version' );
	}

	/**
	 * Check if the plugin needs any update
	 *
	 * @return boolean
	 */
	public function needs_update() {
		// may be it's the first install
		if ( ! $this->get_version() ) {
			return false;
		}

		if ( version_compare( $this->get_version(), WPCP_VERSION, '<' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Perform all the necessary upgrade routines
	 *
	 * @return void
	 */
	function perform_updates() {
		$installed_version = $this->get_version();
		$path              = trailingslashit( dirname( __FILE__ ) );

		foreach ( self::$upgrades as $version => $file ) {
			if ( version_compare( $installed_version, $version, '<' ) ) {
				include $path . $file;
				update_option( 'wpcp_version', $version );
			}
		}
	}


}
