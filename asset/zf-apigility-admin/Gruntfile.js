module.exports = function(grunt) {

    grunt.initConfig({

        pkg: grunt.file.readJSON('package.json'),

        src: {
            spa: 'src/index.html',
            template: 'src/html',
            style: 'src/css',
            script: 'src/js',
            fonts: 'vendor/sass-bootstrap-glyphicons/fonts',
            select2: 'vendor/select2'
        },

        dist: {
            spa: 'dist/index.html',
            template: 'dist/html',
            style: 'dist/css',
            script: 'dist/js',
            fonts: 'dist/fonts',
            select2: 'dist/css',
            vendor: {
                css: 'dist/css/vendor-ui.min.css',
                js: {
                    ui: 'dist/js/vendor-ui.min.js',
                    util: 'dist/js/vendor-util.min.js',
                    angular: 'dist/js/angular.min.js'
                }
            }
        },

        jshint: {
            files: [
                '<%= src.script %>/**/*.js'
            ],
            options: {
                jshintrc: '.jshintrc'
            }
        },

        less: {
            dev: {
                files: {
                    '<%= dist.style %>/main.css': '<%= src.style %>/main.less'
                },
                options: {
                    paths: ['<%= src.style %>/**/*.less'],
                    cleancss: true
                }
            },
            prod: {
                files: {
                    '<%= dist.style %>/main.min.css': '<%= src.style %>/main.less'
                },
                options: {
                    paths: ['<%= src.style %>/**/*.less'],
                    cleancss: true,
                    yuicompress: true
                }
            }
        },

        copy: {
            spa: {
                src: '<%= src.spa %>',
                dest: '<%= dist.spa %>'
            },
            template: {
                files: [
                    {
                        expand: true,
                        cwd: '<%= src.template %>',
                        src: ['**/*.html'],
                        dest: '<%= dist.template %>'
                    }
                ]
            },
            fonts: {
                files: [
                    {
                        expand: true,
                        cwd: '<%= src.fonts %>',
                        src: ['*.*'],
                        dest: '<%= dist.fonts %>',
                        rename: function(dest, src) {
                            /* Need to rename files to add a hyphen between the
                             * words. No idea why, just that errors started to
                             * be thrown when we started minifying the CSS
                             */
                            return dest + '/' + src.replace('glyphiconshalflings', 'glyphicons-halflings');
                        }
                    }
                ]
            },
            select2: {
                /* select2 expects to find several images in the css directory
                 * relative to where it was loaded */
                files: [
                    {
                        expand: true,
                        cwd: '<%= src.select2 %>',
                        src: ['*.gif', '*.png'],
                        dest: '<%= dist.select2 %>'
                    }
                ]
            }
        },

        concat: {
            options: {},
            app: {
                src: ['<%= src.script %>/**/*.js'],
                dest: '<%= dist.script %>/app.js'
            },
            vendorUi: {
                src: [
                    'vendor/jquery/jquery.js',
                    'vendor/bootstrap/dist/js/bootstrap.js',
                    'vendor/jquery-ui/ui/jquery-ui.js',
                    'vendor/select2/select2.js'
                ],
                dest: '<%= dist.vendor.js.ui %>'
            },
            vendorUtil: {
                src: [
                    'vendor/lodash/dist/lodash.js',
                    'vendor/q/q.js',
                    'vendor/uri.js/src/URI.js',
                    'vendor/uri.js/src/URITemplate.js',
                    'vendor/hyperagent/dist/hyperagent.js'
                ],
                dest: '<%= dist.vendor.js.util %>'
            },
            vendorAngular: {
                src: [
                    'vendor/angular/angular.js',
                    'vendor/angular-route/angular-route.js',
                    'vendor/angular-sanitize/angular-sanitize.js',
                    'vendor/angular-flash/dist/angular-flash.js',
                    'vendor/angular-ui-sortable/src/sortable.js',
                    'vendor/angular-ui-select2/src/select2.js',
                    'vendor/ng-tags-input/ng-tags-input.js',
                    'vendor/angular-toggle-switch/angular-toggle-switch.js'
                ],
                dest: '<%= dist.vendor.js.angular %>'
            }
        },

        cssmin: {
            minify: {
                files: {
                    '<%= dist.vendor.css %>': [
                        'vendor/bootstrap/dist/css/bootstrap.css',
                        'vendor/sass-bootstrap-glypicons/css/bootstrap-glyphicons.css',
                        'vendor/jquery-ui/themes/ui-lightness/jquery-ui.css',
                        'vendor/select2/select2.css',
                        'vendor/ng-tags-input/ng-tags-input.css',
                        'vendor/angular-toggle-switch/angular-toggle-switch.css',
                        'vendor/angular-toggle-switch/angular-toggle-switch-bootstrap.css'
                    ]
                }
            }
        },

        uglify: {
            options: {
                banner: '/*! <%= pkg.name %> <%= grunt.template.today("dd-mm-yyyy") %> */\n'
            },
            dist: {
                files: {
                    '<%= dist.vendor.js.ui %>': ['<%= concat.vendorUi.dest %>'],
                    '<%= dist.vendor.js.util %>': ['<%= concat.vendorUtil.dest %>'],
                    '<%= dist.vendor.js.angular %>': ['<%= concat.vendorAngular.dest %>'],
                    '<%= dist.script %>/app.min.js': ['<%= concat.app.dest %>']
                }
            }
        },

        watch: {
            gruntfile: {
                files: ['Gruntfile.js'],
                tasks: ['build']
            },
            script: {
                files: ['<%= src.script %>/**/*.js'],
                tasks: ['jshint', 'concat', 'uglify']
            },
            style: {
                files: ['<%= src.style %>/main.less'],
                tasks: ['less:dev', 'less:prod']
            },
            spa: {
                files: ['<%= src.spa %>'],
                tasks: ['copy']
            },
            template: {
                files: ['<%= src.template %>/**/*.html'],
                tasks: ['copy']
            }
        },

        clean: ['dist/**/*']
    });



    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-htmlmin');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-angular-templates');
    grunt.loadNpmTasks('grunt-inline-angular-templates');

    grunt.registerTask('lint', [
        'jshint'
    ]);

    grunt.registerTask('build', [
        'jshint',
        'less:dev',
        'less:prod',
        'cssmin',
        'copy',
        'concat',
        'uglify'
    ]);
};
