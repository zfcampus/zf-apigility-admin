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
      'src/vendor/jquery/jquery.js',
      'src/vendor/angular/angular.js',
      'src/vendor/angular-mocks/angular-mocks.js',
      'src/vendor/angular-sanitize/angular-sanitize.js',
      'src/vendor/angular-route/angular-route.js',
      'src/vendor/lodash/dist/lodash.js',
      'src/vendor/hyperagent/dist/hyperagent.js',
      'src/vendor/ng-tags-input/ng-tags-input.js',
      'src/vendor/angular-flash/dist/angular-flash.js',
      'src/vendor/angular-ui-sortable/src/sortable.js',
      'src/vendor/angular-ui-select2/src/select2.js',
      'src/vendor/angular-toggle-switch/angular-toggle-switch.js',
      'src/js/*.js',
      'src/js/**/*.js',
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
