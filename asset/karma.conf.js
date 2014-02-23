// Karma configuration
// http://karma-runner.github.io/0.10/config/configuration-file.html

module.exports = function(config) {
  config.set({
    // base path, that will be used to resolve files and exclude
    basePath: '',

    // testing framework to use (jasmine/mocha/qunit/...)
    frameworks: ['jasmine'],

    // list of files / patterns to load in the browser
    files: [
      'src/zf-apigility-admin/vendor/jquery/jquery.js',
      'src/zf-apigility-admin/vendor/lodash/dist/lodash.js',
      'src/zf-apigility-admin/vendor/angular/angular.js',
      'src/zf-apigility-admin/vendor/angular-mocks/angular-mocks.js',
      'src/zf-apigility-admin/vendor/angular-ui-router/release/angular-ui-router.js',
      'src/zf-apigility-admin/vendor/angular-sanitize/angular-sanitize.js',
      'src/zf-apigility-admin/vendor/ng-tags-input/ng-tags-input.js',
      'src/zf-apigility-admin/vendor/angular-flash/dist/angular-flash.js',
      'src/zf-apigility-admin/vendor/angular-ui-sortable/sortable.js',
      'src/zf-apigility-admin/vendor/angular-ui-select2/src/select2.js',
      'src/zf-apigility-admin/vendor/angular-toggle-switch/angular-toggle-switch.js',
      'src/zf-apigility-admin/js/*.js',
      'src/zf-apigility-admin/js/**/*.js',
      'test/mock/**/*.js',
      'test/spec/**/*.js'
    ],

    // list of files / patterns to exclude
    exclude: [],

    // web server port
    port: 8080,

    // level of logging
    // possible values: LOG_DISABLE || LOG_ERROR || LOG_WARN || LOG_INFO || LOG_DEBUG
    logLevel: config.LOG_INFO,


    // enable / disable watching file and executing tests whenever any file changes
    autoWatch: false,


    // Start these browsers, currently available:
    // - Chrome
    // - ChromeCanary
    // - Firefox
    // - Opera
    // - Safari (only Mac)
    // - PhantomJS
    // - IE (only Windows)
    browsers: ['Chrome'],


    // Continuous Integration mode
    // if true, it capture browsers, run tests and exit
    singleRun: false
  });
};
