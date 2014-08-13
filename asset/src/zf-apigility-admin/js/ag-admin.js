(function() {
  'use strict';

  /**
   * Declare and configure modules
   */
  angular.module('ag-admin', [
    'ui.bootstrap',
    'ui.router',
    'ngSanitize',
    'ngTagsInput',
    'angular-flash.service',
    'angular-flash.flash-alert-directive',
    'ui.sortable',
    'ui.select2',
    'toggle-switch',
    'templates-main'
  ]).config(
    function($provide, $stateProvider, $urlRouterProvider) {
      // setup the API Base Path (this should come from initial ui load/php)
      $provide.value('apiBasePath', angular.element('body').data('api-base-path') || '/admin/api');

      $stateProvider.state('ag', {
        abstract: true,
        views: {
          breadcrumbs: {
            templateUrl: 'html/breadcrumbs.html',
            controller: ['$rootScope', '$scope', '$state', function ($rootScope, $scope, $state) {
              $scope.breadcrumbs = [];

              var home = {
                title: 'Home',
                href: 'ag.dashboard',
                active: false
              };

              var generateBreadcrumb = function (state) {
                if (!state.url || !state.data) {
                  return false;
                }

                if (!state.data.pageTitle && !state.data.breadcrumb) {
                  return false;
                }

                var title = '';
                
                if (typeof state.data.breadcrumb === 'string') {
                  title = state.data.breadcrumb;
                } else if (typeof state.data.breadcrumb === 'function') {
                  title = state.data.breadcrumb($state.params);
                } else if (state.data.pageTitle) {
                  title = state.data.pageTitle;
                }

                var breadcrumb = {
                  title: title,
                  href: state.name,
                  active: false
                };
                return breadcrumb;
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
                $scope.params = $state.params;
              };

              $rootScope.$on('$stateChangeSuccess', createBreadcrumbs);

              createBreadcrumbs();
            }]
          },
          title: {
            template: '<h1 ng-bind="pageTitle"></h1>',
            controller: ['$rootScope', '$scope', '$state', function ($rootScope, $scope, $state) {

              var update = function (oldval, newval) {
                if (oldval === newval || !newval) {
                  return;
                }

                var pageTitle;
                if ($state.current && $state.current.data && $state.current.data.pageTitle) {
                  if (typeof $state.current.data.pageTitle === 'string') {
                    pageTitle = $state.current.data.pageTitle;
                  } else if (typeof $state.current.data.pageTitle === 'function') {
                    pageTitle = $state.current.data.pageTitle($state.params);
                  }
                }
                $scope.pageTitle = pageTitle;
              };

              $rootScope.$on('$stateChangeSuccess', function () {
                update(null, $state.$current.name);
              });

              update(null, $state);
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
      $stateProvider.state('ag.dashboard', {
        url: '/',
        data: {
          pageTitle: 'Apigility'
        },
        resolve: {
          dashboard: ['DashboardRepository', function (DashboardRepository) {
            return DashboardRepository.fetch();
          }]
        },
        views: {
          'content@': {
            templateUrl: 'html/index.html',
            controller: 'DashboardController'
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
        resolve: {
          dashboard: ['SettingsDashboardRepository', function (SettingsDashboardRepository) {
            return SettingsDashboardRepository.fetch();
          }]
        },
        views: {
          'content@': {
            templateUrl: 'html/settings/dashboard.html',
            controller: 'SettingsDashboardController'
          }
        }
      });
      $stateProvider.state('ag.settings.authentication', {
        url: '/authentication?edit',
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
        url: '/content-negotiation?selector&edit',
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
        url: '/db-adapters?adapter&edit',
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
            templateUrl: 'html/api/index.html',
            controller: 'ApiController'
          },
          'sidebar@': {
            templateUrl: 'html/api/index-sidebar.html',
            controller: 'CreateApiButtonController'
          }
        }
      });

      $stateProvider.state('ag.api.version', {
        url: '/:apiName/v:version',
        data: {
          pageTitle: function (params) {
            return params.apiName + ' (v' + params.version + ')';
          },
          breadcrumb: function (params) {
            return params.apiName + ' (v' + params.version + ')'; 
          }
        },
        resolve: {
          api: ['$stateParams', 'ApiRepository', function($stateParams, ApiRepository) {
            return ApiRepository.getApi($stateParams.apiName, $stateParams.version, true);
          }],
          /* The following are not deps for this state, but are common deps for
           * both REST and RPC service screens.
           */
          filters: ['FiltersServicesRepository', function (FiltersServicesRepository) {
            return FiltersServicesRepository.getList();
          }],
          validators: ['ValidatorsServicesRepository', function (ValidatorsServicesRepository) {
            return ValidatorsServicesRepository.getList();
          }],
          selectors: ['ContentNegotiationResource', function (ContentNegotiationResource) {
            return ContentNegotiationResource.getList().then(
              function (selectors) {
                var selectorNames = [];
                angular.forEach(selectors, function (selector) {
                  selectorNames.push(selector.content_name);
                });
                return selectorNames;
              }
            );
          }]
        },
        views: {
          'content@': {
            templateUrl: 'html/api/overview.html',
            controller: 'ApiOverviewController'
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

      $stateProvider.state('ag.api.version.authorization', {
        url: '/authorization',
        data: {
          pageTitle: 'Authorization',
          breadcrumb: false
        },
        resolve: {
          apiAuthorizations: ['$stateParams', 'ApiAuthorizationRepository', function ($stateParams, ApiAuthorizationRepository) {
            return ApiAuthorizationRepository.getApiAuthorization($stateParams.apiName, $stateParams.version, true);
          }],
          authentication: ['AuthenticationRepository', function (AuthenticationRepository) {
            return AuthenticationRepository.hasAuthentication();
          }]
        },
        views: {
          'content@': {
            templateUrl: 'html/api/authorization.html',
            controller: 'ApiAuthorizationController'
          },
        }
      });
      $stateProvider.state('ag.api.version.rest', {
        url: '/rest-services?service&view&edit',
        data: {
          pageTitle: 'REST Services',
          breadcrumb: false
        },
        resolve: {
          dbAdapters: ['DbAdapterResource', function (DbAdapterResource) {
            return DbAdapterResource.getList();
          }],
          hydrators: ['HydratorServicesRepository', function (HydratorServicesRepository) {
            return HydratorServicesRepository.getList();
          }]
        },
        views: {
          'content@': {
            templateUrl: 'html/api/rest-services/index.html',
            controller: 'ApiRestServicesController'
          },
        }
      });

      $stateProvider.state('ag.api.version.rpc', {
        url: '/rpc-services?service&view&edit',
        data: {
          pageTitle: 'RPC Services',
          breadcrumb: false
        },
        views: {
          'content@': {
            templateUrl: 'html/api/rpc-services/index.html',
            controller: 'ApiRpcServicesController'
          }
        }
      });

      $urlRouterProvider.otherwise('/');
    }
  );

})();
