<?php

defined( 'ABSPATH' ) || exit;

/**
 * Get modules.
 *
 * @since 1.0.0
 * @return array
 */
function wpcp_get_modules() {
	return \WPContentPilot\Modules::instance()->get_modules();
}

/**
 * Get module.
 *
 * @param string $module Module name.
 *
 * @since 1.0.0
 * @return \WPContentPilot\Module
 */
function wpcp_get_module( $module ) {
	return \WPContentPilot\Modules::instance()->get_module( $module );
}
