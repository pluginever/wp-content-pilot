<?php
/**
 * Sanitizes a string key for WPCP Settings
 *
 * Keys are used as internal identifiers. Alphanumeric characters, dashes, underscores, stops, colons and slashes are allowed
 * since 1.0.0
 *
 * @param $key
 *
 * @return string
 */
function wpcp_sanitize_key( $key ) {

	return preg_replace( '/[^a-zA-Z0-9_\-\.\:\/]/', '', $key );
}

/**
 * get all the modules
 *
 * @since 1.0.0
 * @return array
 */
function wpcp_get_modules() {
	$modules = [];
	foreach ( content_pilot()->modules->get_modules() as $module_name => $module ) {
		$modules[ $module_name ] = $module['title'];
	}

	return $modules;
}
