module.exports = function (grunt) {
	'use strict';
	// Load multiple grunt tasks using globbing patterns
	require('load-grunt-tasks')(grunt);
	var sass = require('node-sass');
	grunt.initConfig({

		// Setting folder templates.
		dirs: {
			css: 'assets/css',
			fonts: 'assets/fonts',
			images: 'assets/images',
			js: 'assets/js'
		},

		// Compile all .scss files.
		sass: {
			options: {
				implementation: sass,
				sourceMap: false,
				outputStyle: 'compressed'
			},
			dist: {
				files: [{
					expand: true,
					cwd: '<%= dirs.css %>/',
					src: ['*.scss'],
					dest: '<%= dirs.css %>/',
					ext: '.css'
				}]
			}
		},

		// Minify all .css files.
		cssmin: {
			minify: {
				expand: true,
				cwd: '<%= dirs.css %>/',
				src: ['*.css'],
				dest: '<%= dirs.css %>/',
				ext: '.css'
			}
		},

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
					'**/*.php',               // Include all files
					'!node_modules/**',       // Exclude node_modules/
					'!tests/**',              // Exclude tests/
					'!vendor/**',             // Exclude vendor/
					'!tmp/**'                 // Exclude tmp/
				],
				expand: true
			}
		},

		// Generate POT files.
		makepot: {
			options: {
				type: 'wp-plugin',
				domainPath: 'i18n/languages',
				potHeaders: {
					'language-team': 'LANGUAGE <EMAIL@ADDRESS>'
				}
			},
			dist: {
				options: {
					potFilename: 'wp-content-pilot.pot',
					exclude: [
						'vendor/.*',
						'tests/.*',
						'tmp/.*'
					]
				}
			}
		},

		// Verify build
		shell: {
			command: [ 'rm -rf @next', 'npm install', 'npm run build', 'composer install --no-dev', 'rsync -rc --exclude-from="./.distignore" "." "./@next/" --delete --delete-excluded', 'echo ', 'echo === NOW COMPARE WITH ORG/GIT VERSION===' ].join(' && ')
		}

	});

	// Register tasks.
	grunt.registerTask('build', ['sass', 'cssmin', 'checktextdomain', 'makepot']);
};
