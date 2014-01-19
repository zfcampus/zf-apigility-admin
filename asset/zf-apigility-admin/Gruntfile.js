module.exports = function(grunt) {

    grunt.initConfig({

        pkg: grunt.file.readJSON('package.json'),

        src: {
            spa: 'src/index.html',
            template: 'src/html',
            style: 'src/css',
            script: 'src/js'
        },

        dist: {
            spa: 'dist/index.html',
            template: 'dist/html',
            style: 'dist/css',
            script: 'dist/js'
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
            }
        },

        concat: {
            options: {

            },
            js: {
                src: ['<%= src.script %>/**/*.js'],
                dest: '<%= dist.script %>/app.js'
            }
        },

        uglify: {
            options: {
                banner: '/*! <%= pkg.name %> <%= grunt.template.today("dd-mm-yyyy") %> */\n'
            },
            dist: {
                files: {
                    '<%= dist.script %>/app.min.js': ['<%= concat.js.dest %>']
                }
            }
        },

        watch: {
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
        }
    });

    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-clean');
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
        'copy',
        'concat',
        'uglify'
    ]);
};
