// Generated on 2013-12-20 using generator-angular 0.6.0
'use strict';

// # Globbing
// for performance reasons we're only matching one level down:
// 'test/spec/{,**/}*.js'
// use this if you want to recursively match all subfolders:
// 'test/spec/**/*.js'

module.exports = function(grunt) {

    // Load grunt tasks automatically
    require('load-grunt-tasks')(grunt);

    // Time how long tasks take. Can help when optimizing build times
    require('time-grunt')(grunt);

    // Define the configuration for all the tasks
    grunt.initConfig({

        // Project settings
        yeoman: {
            app: 'src/zf-apigility-admin',
            dist: 'dist/zf-apigility-admin'
        },

        // Watches files for changes and runs tasks based on the changed files
        watch: {
            js: {
                files: ['<%= yeoman.app %>/js/{,**/}*.js'],
                tasks: ['newer:jshint:all']
            },
            jsTest: {
                files: ['test/spec/{,**/}*.js'],
                tasks: ['newer:jshint:test', 'karma']
            },
            less: {
                files: ['<%= yeoman.app %>/less/{,**/}*.less'],
                tasks: ['less:server']
            },
            gruntfile: {
                files: ['Gruntfile.js']
            },
            livereload: {
                options: {
                    livereload: true
                },
                files: [
                    '<%= yeoman.app %>/{,**/}*.html',
                    '<%= yeoman.app %>/css/{,**/}*.css'
                ]
            },
            html2js: {
                files: ['<%= yeoman.app %>/html/{,**/}*.html'],
                tasks: ['html2js']
            }
        },

        // Make sure code styles are up to par and there are no obvious mistakes
        jshint: {
            options: {
                jshintrc: '.jshintrc',
                reporter: require('jshint-stylish'),
                ignores: ['<%= yeoman.app %>/js/templates.js'],
            },
            all: [
                'Gruntfile.js',
                '<%= yeoman.app %>/js/{,**/}*.js'
            ],
            test: {
                options: {
                    jshintrc: 'test/.jshintrc'
                },
                src: ['test/spec/{,**/}*.js']
            }
        },

        // Empties folders to start fresh
        clean: {
            dist: {
                files: [{
                    dot: true,
                    src: [
                        '.tmp',
                        '<%= yeoman.dist %>/*',
                        '!<%= yeoman.dist %>/.git*'
                    ]
                }]
            },
            server: '.tmp'
        },

        // Compiles Less to CSS
        less: {
            options: {
                // Adds additional paths for import (so can import bower_components as well)
                paths: [
                    '<%= yeoman.app %>/less',
                    '<%= yeoman.app %>/vendor'
                ]
            },
            server: {
                files: {
                    '<%= yeoman.app %>/css/main.css': '<%= yeoman.app %>/less/main.less',
                    '<%= yeoman.app %>/css/vendor.css': '<%= yeoman.app %>/less/vendor.less'
                },
                options: {
                    cleancss: false
                }
            }
            // Dist is handled by usemin/cssmin of the above 'server' config
        },

        // Reads HTML for usemin blocks to enable smart builds that automatically
        // concat, minify and revision files. Creates configurations in memory so
        // additional tasks can operate on them
        useminPrepare: {
            html: '<%= yeoman.app %>/index.html',
            options: {
                dest: '<%= yeoman.dist %>'
            }
        },

        // Performs rewrites based on rev and the useminPrepare configuration
        usemin: {
            html: ['<%= yeoman.dist %>/{,**/}*.html'],
            css: ['<%= yeoman.dist %>/css/{,**/}*.css'],
            options: {
                assetsDirs: ['<%= yeoman.dist %>']
            }
        },

        htmlmin: {
            dist: {
                options: {
                    // Optional configurations that you can uncomment to use
                    // removeCommentsFromCDATA: true,
                    // collapseBooleanAttributes: true,
                    // removeAttributeQuotes: true,
                    // removeRedundantAttributes: true,
                    // useShortDoctype: true,
                    // removeEmptyAttributes: true,
                    // removeOptionalTags: true*/
                },
                files: [{
                    expand: true,
                    cwd: '<%= yeoman.app %>',
                    src: ['*.html', 'html/**/*.html'],
                    dest: '<%= yeoman.dist %>'
                }]
            }
        },

        // Allow the use of non-minsafe AngularJS files. Automatically makes it
        // minsafe compatible so Uglify does not destroy the ng references
        ngmin: {
            dist: {
                files: [{
                    expand: true,
                    cwd: '.tmp/concat/js',
                    src: '*.js',
                    dest: '.tmp/concat/js'
                }]
            }
        },

        // Copies remaining files to places other tasks can use
        copy: {
            dist: {
                files: [{
                    expand: true,
                    dot: true,
                    cwd: '<%= yeoman.app %>',
                    dest: '<%= yeoman.dist %>',
                    src: [
                        '*.{ico,png,txt}',
                        '.htaccess',
                        'img/{,**/}*.webp',
                        'img/{,**/}*.png',
                        'fonts/*',
                        'js/data/**/*.json'
                    ]
                }, {
                    expand: true,
                    dot: false,
                    cwd: '<%= yeoman.app %>/vendor/select2',
                    dest: '<%= yeoman.dist %>/css/',
                    src: [
                        '*.{png,gif}',
                    ]
                }]
            }
        },

        // Run some tasks in parallel to speed up the build process
        concurrent: {
            server: [
                'less:server'
            ],
            test: [
                'less:server'
            ],
            dist: [
                'less:server',
                'htmlmin'
            ]
        },

        // Test settings
        karma: {
            unit: {
                configFile: 'karma.conf.js',
                singleRun: true
            }
        },

        //Collect all html views into single template
        html2js: {
          options: {
            base: '<%= yeoman.app %>'
          },
          main: {
            src: ['<%= yeoman.app %>/html/**/*.html'],
            dest: '<%= yeoman.app %>/js/templates.js'
          },
        },
    });

    grunt.registerTask('monkeyPatches', function () {
        // monkeypatch FileProcessor to include utf-8
        var FileProcessor = require('grunt-usemin/lib/fileprocessor');
        FileProcessor.prototype.replaceWithOld = FileProcessor.prototype.replaceWith;
        FileProcessor.prototype.replaceWith = function replaceWith(block) {
            var script = FileProcessor.prototype.replaceWithOld(block);
            if (script.match(/<script src/)) {
                script = script.replace('></script>', ' charset="utf-8"></script>');
            }
            return script;
        };
    });

    grunt.registerTask('serve', function(target) {
        grunt.task.run([
            'clean:server',
            'concurrent:server',
            'watch'
        ]);
    });

    grunt.registerTask('test', [
        'clean:server',
        'concurrent:test',
        'karma'
    ]);

    grunt.registerTask('build', [
        'clean:dist',
        'html2js',
        'useminPrepare',
        'concurrent:dist',
        'concat',
        'ngmin',
        'copy:dist',
        'cssmin',
        'uglify',
        'monkeyPatches',
        'usemin'
    ]);

    grunt.registerTask('default', [
        'newer:jshint',
        'test',
        'build'
    ]);
};
