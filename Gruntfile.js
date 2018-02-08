module.exports = function(grunt) {
    var pkg = grunt.file.readJSON('package.json');
    var bannerTemplate = '/**\n' +
        ' * <%= pkg.title %> - v<%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %>\n' +
        ' * <%= pkg.homepage %>\n' +
        ' *\n' +
        ' * Copyright (c) <%= grunt.template.today("yyyy") %>;\n' +
        ' * Licensed GPLv2+\n' +
        ' */\n';

    var compactBannerTemplate = '/**\n' +
        ' * <%= pkg.title %> - v<%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %> | <%= pkg.homepage %> | Copyright (c) <%= grunt.template.today("yyyy") %>; | Licensed GPLv2+\n' +
        ' */\n';

    // Project configuration
    grunt.initConfig({

    pkg: grunt.file.readJSON('package.json'),

    concat: {
        options: {
            stripBanners: true,
            banner: bannerTemplate
        },
            wp_content_pilot: {
                src: [
                    'assets/js/vendor/*.js',
                    'assets/js/src/wp-content-pilot.js',
                ],
                    dest: 'assets/js/wp-content-pilot.js'
            }
        },
    // jshint
    jshint: {
        options: {
            jshintrc: '.jshintrc',
                reporter: require('jshint-stylish')
        },
        main: [
            'assets/js/src/*.js',
        ]
    },

    uglify: {
        all: {
            files: {
                'assets/js/wp-content-pilot.min.js': ['assets/js/wp-content-pilot.js']
            },
            options: {
                banner: compactBannerTemplate,
                mangle: false
            },
            compress: {
                drop_console: true
            }
        }
    },

        sass:   {
            all: {
                files: {
                    'assets/css/wp-content-pilot.css': 'assets/css/sass/wp-content-pilot.scss'
                }
            }
        },


        cssmin: {
            options: {
                banner: bannerTemplate
            },
            minify: {
                expand: true,

                cwd: 'assets/css/',
                src: ['wp-content-pilot.css'],

                dest: 'assets/css/',
                ext: '.min.css'
            }
        },
        imagemin: {
            static: {
                options: {
                    optimizationLevel: 3,
                        svgoPlugins: [{removeViewBox: false}],
                        use: [] // Example plugin usage
                },
                files: {
                }
            },
            dynamic: {
                files: [{
                    expand: true,
                    cwd: 'assets/images/src/',
                    src: ['**/*.{png,jpg,gif,svg}'],
                    dest: 'assets/images/'
                }]
            }
        },

        watch:  {
             options: {
                livereload: true,
            },

            sass: {
                files: ['assets/css/sass/*.scss'],
                tasks: ['sass', 'cssmin'],
                options: {
                    debounceDelay: 500
                }
            },

            scripts: {
                files: ['assets/js/src/**/*.js', 'assets/js/vendor/**/*.js'],
                tasks: ['jshint', 'concat', 'uglify'],
                options: {
                    debounceDelay: 500
                }
            }
        },

        /**
         * check WP Coding standards
         * https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards
         */
        phpcs: {
            application: {
                dir: [
                    '**/*.php',
                    '!**/node_modules/**'
                ]
            },
            options: {
                bin: '~/phpcs/scripts/phpcs',
                standard: 'WordPress'
            }
        },
           // Generate POT files.
        makepot: {
            target: {
                options: {
                    exclude: ['build/.*', 'node_modules/*', 'assets/*'],
                        domainPath: '/i18n/languages/', // Where to save the POT file.
                        potFilename: 'wp-content-pilot.pot', // Name of the POT file.
                        type: 'wp-plugin', // Type of project (wp-plugin or wp-theme).
                        potHeaders: {
                        'report-msgid-bugs-to': 'http://pluginever.com/support/',
                            'language-team': 'LANGUAGE <support@pluginever.com>'
                    }
                }
            }
        },
            // Clean up build directory
        clean: {
            main: ['build/']
        },
          copy: {
        main: {
            src: [
                '**',
                '!node_modules/**',
                '!**/js/src/**',
                '!**/css/src/**',
                '!**/js/vendor/**',
                '!**/css/vendor/**',
                '!**/images/src/**',
                '!**/sass/**',
                '!**/tests/**',
                '!**/test/**',
                '!**/samples/**',
                '!**/docs/**',
                '!build/**',
                '!**/*.md',
                '!**/*.map',
                '!**/package.map',
                '!**/package-lock.map',
                '!**/Gruntfile.map',
                '!**/composer.json',
                '!**/LICENSE',
                '!**/*.sh',
                '!.idea/**',
                '!bin/**',
                '!.git/**',
                '!Gruntfile.js',
                '!package.json',
                '!composer.json',
                '!composer.lock',
                '!debug.log',
                '!.gitignore',
                '!.gitmodules',
                '!npm-debug.log',
                '!plugin-deploy.sh',
                '!export.sh',
                '!config.codekit',
                '!nbproject/*',
                '!tests/**',
                '!.csscomb.json',
                '!.editorconfig',
                '!.jshintrc',
                '!.tmp'
            ],
                dest: 'build/'
        }
    },
      compress: {
        main: {
            options: {
                mode: 'zip',
                    archive: './build/wp-content-pilot-' + pkg.version + '.zip'
            },
            expand: true,
                cwd: 'build/',
                src: ['**/*'],
                dest: 'wp-content-pilot'
        }
    },
    server: {
        options: {
            message: 'Server is ready!'
        }
    }



});

// Load other tasks
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-imagemin');
    grunt.loadNpmTasks('grunt-notify');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks( 'grunt-wp-i18n' );
    grunt.loadNpmTasks('grunt-contrib-compress');

    grunt.loadNpmTasks('grunt-contrib-sass');

    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.loadNpmTasks('grunt-phpcs');

    // Default task.

    grunt.registerTask( 'default', ['jshint', 'concat', 'uglify', 'sass', 'cssmin', 'imagemin', 'notify:server'] );


    grunt.registerTask('release', ['makepot', 'zip']);
    grunt.registerTask('zip', ['clean', 'copy', 'compress']);
    grunt.util.linefeed = '\n';
};
