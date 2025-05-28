<?php
/**
 * Content Pilot Upgrades
 * This file is responsible for handling the plugin upgrades.
 *
 * @since   1.0.0
 * @package WPContentPilot
 */

defined( 'ABSPATH' ) || exit();

/**
 * Plugin Upgrade Routine
 *
 * @since 1.0.0
 */
class ContentPilot_Upgrades {

	/**
	 * The upgrades
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private static $upgrades = array(
		'1.0.4' => 'updates/update-1.0.4.php',
		'1.1.2' => 'updates/update-1.1.2.php',
	);

	/**
	 * Get the plugin version
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_version() {
		return get_option( 'wpcp_version' );
	}

	/**
	 * Check if the plugin needs any update
	 *
	 * @since 1.0.0
	 * @return boolean
	 */
	public function needs_update() {
		// Maybe it's the first install.
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
	 * @since 1.0.0
	 * @return void
	 */
	public function perform_updates() {
		$installed_version = $this->get_version();
		$path              = trailingslashit( __DIR__ );

		foreach ( self::$upgrades as $version => $file ) {
			if ( version_compare( $installed_version, $version, '<' ) ) {
				include $path . $file;
				update_option( 'wpcp_version', $version );
			}
		}
	}
}
