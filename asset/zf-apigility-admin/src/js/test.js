(function() {
  'use strict';

  /**
   * Declare and configure modules
   */
  angular.module('ag-admin', [
    'ui.router',
    'ngSanitize',
    'ngTagsInput',
    'angular-flash.service',
    'angular-flash.flash-alert-directive',
    'ui.sortable',
    'ui.select2',
    'toggle-switch'
  ]).config(
    function($provide, $stateProvider, $urlRouterProvider) {
      // setup the API Base Path (this should come from initial ui load/php)
      $provide.value('apiBasePath', angular.element('body').data('api-base-path') || '/admin/api');

      $stateProvider.state('ag', {
        abstract: true,
        views: {
          breadcrumbs: {
            templateUrl: 'html/breadcrumbs.html',
            controller: ['$scope', '$state', '$stateParams', function ($scope, $state, $stateParams) {
              $scope.breadcrumbs = [];

              var home = {
                title: 'Home',
                href: 'ag.settings.overview',
                active: false
              };

              var generateBreadcrumb = function (state) {
                if (state.abstract || !state.url || !state.data || !state.data.pageTitle) {
                  return false;
                }

                return {
                  title: state.data.pageTitle,
                  href: $state.url,
                  active: false
                };
              };

              var createBreadcrumbs = function () {
                var breadcrumbs = [];
                var active = $state.$current.name;

                var state = $state.$current;
                while (state.name !== 'ag') {
                  var breadcrumb = generateBreadcrumb(state);

                  if (breadcrumb) {
                    if (state.name === active) {
                      breadcrumb.active = true;
                    }
                    breadcrumbs.unshift(breadcrumb);
                  }

                  if (!state.parent) {
                    break;
                  }

                  state = state.parent;
                }

                breadcrumbs.unshift(home);

                var uris = [];
                breadcrumbs = breadcrumbs.filter(function (breadcrumb) {
                  if (uris.lastIndexOf(breadcrumb.href) === -1) {
                    uris.push(breadcrumb.href);
                    return true;
                  }

                  return false;
                });

                $scope.breadcrumbs = breadcrumbs;
                $scope.params = $stateParams;
              };

              $scope.$watch(function () {
                return $state.current.name;
              }, createBreadcrumbs);

              createBreadcrumbs();
            }]
          },
          title: {
            template: '<h1 ng-bind="pageTitle"></h1>',
            controller: ['$scope', '$state', function ($scope, $state) {

              var update = function (oldval, newval) {
                if (oldval === newval) {
                  return;
                }

                var pageTitle;
                if ($state.current && $state.current.data && $state.current.data.pageTitle) {
                  pageTitle = $state.current.data.pageTitle;
                }
                $scope.pageTitle = pageTitle;
              };

              $scope.$watch(function () {
                return $state.current.name;
              }, update);

              update($state);
            }]
          },
          sidebar: {
            templateUrl: 'html/settings/sidebar.html',
            controller: ['$scope', '$state', function ($scope, $state) {
              var update = function () {
                $scope.active = $state.current.name;
              };

              $scope.$watch(function () {
                return $state.current.name;
              }, update);
              update();
            }]
          }
        }
      });
      $stateProvider.state('ag.settings', {
        abstract: true,
        url: '/settings'
      });
      $stateProvider.state('ag.settings.overview', {
        url: '/overview',
        data: {
          pageTitle: 'Settings'
        },
        views: {
          'content@': {
            templateUrl: 'html/settings/dashboard.html'
          }
        }
      });
      $stateProvider.state('ag.settings.authentication', {
        url: '/authentication',
        data: {
          pageTitle: 'Authentication'
        },
        views: {
          'content@': {
            templateUrl: 'html/settings/authentication/index.html',
            controller: 'AuthenticationController'
          }
        }
      });
      $stateProvider.state('ag.settings.content-negotiation', {
        url: '/content-negotiation',
        data: {
          pageTitle: 'Content Negotiation'
        },
        resolve: {
          selectors: ['ContentNegotiationResource', function(ContentNegotiationResource) {
            return ContentNegotiationResource.getList();
          }]
        },
        views: {
          'content@': {
            templateUrl: 'html/settings/content-negotiation/index.html',
            controller: 'ContentNegotiationController'
          }
        }
      });
      $stateProvider.state('ag.settings.db-adapters', {
        url: '/db-adapters',
        data: {
          pageTitle: 'Database Adapters'
        },
        resolve: {
          dbAdapters: ['DbAdapterResource', function (DbAdapterResource) {
            return DbAdapterResource.getList();
          }]
        },
        views: {
          'content@': {
            templateUrl: 'html/settings/db-adapters/index.html',
            controller: 'DbAdapterController'
          }
        }
      });

      $stateProvider.state('ag.api', {
        url: '/api',
        data: {
          pageTitle: 'APIs'
        },
        views: {
          'content@': {
            template: '<h4>APIs</h4>'
          },
          'sidebar@': {
            templateUrl: 'html/api/sidebar.html',
            controller: ['$scope', '$state', function ($scope, $state) {
              var update = function () {
                $scope.active = $state.current.name;
              };

              $scope.$watch(function () {
                return $state.current.name;
              }, update);
              update();
            }]
          }
        }
      });

      $urlRouterProvider.otherwise('/settings/overview');
    }
  );

})();
