(function() {
    'use strict';

    /**
     * Declare and configure modules
     */
    angular.module('ag-admin', [
        'ngRoute',
        'ngSanitize',
        'ngTagsInput',
        'angular-flash.service',
        'angular-flash.flash-alert-directive',
        'ui.sortable',
        'ui.select2',
        'toggle-switch'
    ]).config(
        function($routeProvider, $provide) {
            // setup the API Base Path (this should come from initial ui load/php)
            $provide.value('apiBasePath', angular.element('body').data('api-base-path') || '/admin/api');

            $routeProvider.when('/dashboard', {
                templateUrl: 'html/index.html',
                controller: 'DashboardController'
            });
            $routeProvider.when('/global/content-negotiation', {
                templateUrl: 'html/global/content-negotiation/index.html',
                controller: 'ContentNegotiationController',
                resolve: {
                    selectors: ['ContentNegotiationResource', function(ContentNegotiationResource) {
                        return ContentNegotiationResource.getList();
                    }]
                }
            });
            $routeProvider.when('/global/db-adapters', {
                templateUrl: 'html/global/db-adapters/index.html',
                controller: 'DbAdapterController',
                resolve: {
                    dbAdapters: ['DbAdapterResource', function (DbAdapterResource) {
                        return DbAdapterResource.getList();
                    }]
                }
            });
            $routeProvider.when('/global/authentication', {
                templateUrl: 'html/global/authentication/index.html',
                controller: 'AuthenticationController'
            });
            $routeProvider.when('/api/:apiName/:version/overview', {
                templateUrl: 'html/api/overview.html',
                controller: 'ApiOverviewController',
                resolve: {
                    api: ['$route', 'ApiRepository', function($route, ApiRepository) {
                        return ApiRepository.getApi($route.current.params.apiName, $route.current.params.version);
                    }]
                }
            });
            $routeProvider.when('/api/:apiName/:version/authorization', {
                templateUrl: 'html/api/authorization.html',
                controller: 'ApiAuthorizationController',
                resolve: {
                    api: ['$route', 'ApiRepository', function ($route, ApiRepository) {
                        return ApiRepository.getApi($route.current.params.apiName, $route.current.params.version);
                    }],
                    apiAuthorizations: ['$route', 'ApiAuthorizationRepository', function ($route, ApiAuthorizationRepository) {
                        return ApiAuthorizationRepository.getApiAuthorization($route.current.params.apiName, $route.current.params.version);
                    }],
                    authentication: ['AuthenticationRepository', function (AuthenticationRepository) {
                        return AuthenticationRepository.hasAuthentication();
                    }]
                }
            });
            $routeProvider.when('/api/:apiName/:version/rest-services', {
                templateUrl: 'html/api/rest-services/index.html',
                controller: 'ApiRestServicesController',
                resolve: {
                    dbAdapters: ['DbAdapterResource', function (DbAdapterResource) {
                        return DbAdapterResource.getList();
                    }],
                    api: ['$route', 'ApiRepository', function ($route, ApiRepository) {
                        return ApiRepository.getApi($route.current.params.apiName, $route.current.params.version);
                    }],
                    filters: ['FiltersServicesRepository', function (FiltersServicesRepository) {
                        return FiltersServicesRepository.getList();
                    }],
                    validators: ['ValidatorsServicesRepository', function (ValidatorsServicesRepository) {
                        return ValidatorsServicesRepository.getList();
                    }],
                    hydrators: ['HydratorServicesRepository', function (HydratorServicesRepository) {
                        return HydratorServicesRepository.getList();
                    }],
                    selectors: ['ContentNegotiationResource', function (ContentNegotiationResource) {
                        return ContentNegotiationResource.getList().then(function (selectors) {
                            var selectorNames = [];
                            angular.forEach(selectors, function (selector) {
                                selectorNames.push(selector.content_name);
                            });
                            return selectorNames;
                        });
                    }]
                }
            });
            $routeProvider.when('/api/:apiName/:version/rpc-services', {
                templateUrl: 'html/api/rpc-services/index.html',
                controller: 'ApiRpcServicesController',
                resolve: {
                    api: ['$route', 'ApiRepository', function ($route, ApiRepository) {
                        return ApiRepository.getApi($route.current.params.apiName, $route.current.params.version);
                    }],
                    filters: ['FiltersServicesRepository', function (FiltersServicesRepository) {
                        return FiltersServicesRepository.getList();
                    }],
                    validators: ['ValidatorsServicesRepository', function (ValidatorsServicesRepository) {
                        return ValidatorsServicesRepository.getList();
                    }],
                    selectors: ['ContentNegotiationResource', function (ContentNegotiationResource) {
                        return ContentNegotiationResource.getList().then(function (selectors) {
                            var selectorNames = [];
                            angular.forEach(selectors, function (selector) {
                                selectorNames.push(selector.content_name);
                            });
                            return selectorNames;
                        });
                    }]
                }
            });
            $routeProvider.otherwise({
                redirectTo: '/dashboard'
            });
        }
    );

})();
