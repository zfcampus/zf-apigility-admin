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
                    selectors: function(ContentNegotiationResource) {
                        return ContentNegotiationResource.getList();
                    }
                }
            });
            $routeProvider.when('/global/db-adapters', {
                templateUrl: 'html/global/db-adapters/index.html',
                controller: 'DbAdapterController'
            });
            $routeProvider.when('/global/authentication', {
                templateUrl: 'html/global/authentication/index.html',
                controller: 'AuthenticationController'
            });
            $routeProvider.when('/api/:apiName/:version/overview', {
                templateUrl: 'html/api/overview.html',
                controller: 'ApiOverviewController',
                resolve: {
                    api: function($route, ApiRepository) {
                        return ApiRepository.getApi($route.current.params.apiName, $route.current.params.version);
                    }
                }
            });
            $routeProvider.when('/api/:apiName/:version/authorization', {
                templateUrl: 'html/api/authorization.html',
                controller: 'ApiAuthorizationController',
                resolve: {
                    api: function($route, ApiRepository) {
                        return ApiRepository.getApi($route.current.params.apiName, $route.current.params.version);
                    },
                    apiAuthorizations: function($route, ApiAuthorizationRepository) {
                        return ApiAuthorizationRepository.getApiAuthorization($route.current.params.apiName, $route.current.params.version);
                    },
                    authentication: function(AuthenticationRepository) {
                        return AuthenticationRepository.hasAuthentication();
                    },
                }
            });
            $routeProvider.when('/api/:apiName/:version/rest-services', {
                templateUrl: 'html/api/rest-services/index.html',
                controller: 'ApiRestServicesController',
                resolve: {
                    dbAdapters: function(DbAdapterResource) {
                        return DbAdapterResource.getList();
                    },
                    api: function($route, ApiRepository) {
                        return ApiRepository.getApi($route.current.params.apiName, $route.current.params.version);
                    },
                    filters: function(FiltersServicesRepository) {
                        return FiltersServicesRepository.getList();
                    },
                    validators: function(ValidatorsServicesRepository) {
                        return ValidatorsServicesRepository.getList();
                    },
                    hydrators: function(HydratorServicesRepository) {
                        return HydratorServicesRepository.getList();
                    },
                    selectors: function(ContentNegotiationResource) {
                        return ContentNegotiationResource.getList().then(function(selectors) {
                            var selectorNames = [];
                            angular.forEach(selectors, function(selector) {
                                selectorNames.push(selector.content_name);
                            });
                            return selectorNames;
                        });
                    }
                }
            });
            $routeProvider.when('/api/:apiName/:version/rpc-services', {
                templateUrl: 'html/api/rpc-services/index.html',
                controller: 'ApiRpcServicesController',
                resolve: {
                    api: function($route, ApiRepository) {
                        return ApiRepository.getApi($route.current.params.apiName, $route.current.params.version);
                    },
                    filters: function(FiltersServicesRepository) {
                        return FiltersServicesRepository.getList();
                    },
                    validators: function(ValidatorsServicesRepository) {
                        return ValidatorsServicesRepository.getList();
                    },
                    selectors: function(ContentNegotiationResource) {
                        return ContentNegotiationResource.getList().then(function(selectors) {
                            var selectorNames = [];
                            angular.forEach(selectors, function(selector) {
                                selectorNames.push(selector.content_name);
                            });
                            return selectorNames;
                        });
                    }
                }
            });
            $routeProvider.otherwise({
                redirectTo: '/dashboard'
            });
        }
    );

})();