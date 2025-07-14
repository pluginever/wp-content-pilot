module.exports = function (grunt) {
	'use strict';

	// Load all grunt tasks matching the `grunt-*` pattern.
	require( 'load-grunt-tasks' )( grunt );

	// Show elapsed time.
	require( '@lodder/time-grunt' )( grunt );
	grunt.initConfig(
		{
			package: grunt.file.readJSON( 'package.json' ),
			// Check textdomain errors.
			checktextdomain: {
				options: {
					text_domain: 'wp-content-pilot',
					keywords: [
						'__:1,2d',
						'_e:1,2d',
						'_x:1,2c,3d',
						'esc_html__:1,2d',
						'esc_html_e:1,2d',
						'esc_html_x:1,2c,3d',
						'esc_attr__:1,2d',
						'esc_attr_e:1,2d',
						'esc_attr_x:1,2c,3d',
						'_ex:1,2c,3d',
						'_n:1,2,4d',
						'_nx:1,2,4c,5d',
						'_n_noop:1,2,3d',
						'_nx_noop:1,2,3c,4d'
					]
				},
				files: {
					src: [
						'**/*.php',
						'!packages/**',
						'!node_modules/**',
						'!tests/**',
						'!vendor/**',
						'!tmp/**'
					],
					expand: true
				}
			},

			// Generate POT files.
			makepot: {
				target: {
					options: {
						domainPath: 'i18n/languages',
						exclude: [ 'packages/*', '.git/*', 'node_modules/*', 'tests/*' ],
						mainFile: 'wp-content-pilot.php',
						potFilename: 'wp-content-pilot.pot',
						potHeaders: {
							'report-msgid-bugs-to': 'https://wpcontentpilot.com/support/',
							poedit: true,
							'x-poedit-keywordslist': true,
						},
						type: 'wp-plugin',
						updateTimestamp: false,
					},
				},
			},
		}
	);

	// Register tasks.
	grunt.registerTask( 'i18n', [ 'checktextdomain', 'makepot' ] );
	grunt.registerTask( 'build', [ 'i18n' ] );
};
