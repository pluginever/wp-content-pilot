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


