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

            $stateProvider.state('dashboard', {
                url: '/dashboard',
                templateUrl: 'html/index.html',
                controller: 'DashboardController'
            });
            $stateProvider.state('dashboard.content-negotiation', {
                url: '^/global/content-negotiation',
                templateUrl: 'html/global/content-negotiation/index.html',
                controller: 'ContentNegotiationController',
                resolve: {
                    selectors: ['ContentNegotiationResource', function(ContentNegotiationResource) {
                        return ContentNegotiationResource.getList();
                    }]
                }
            });
            $stateProvider.state('dashboard.db-adapters', {
                url: '^/global/db-adapters',
                templateUrl: 'html/global/db-adapters/index.html',
                controller: 'DbAdapterController',
                resolve: {
                    dbAdapters: ['DbAdapterResource', function (DbAdapterResource) {
                        return DbAdapterResource.getList();
                    }]
                }
            });
            $stateProvider.state('dashboard.authentication', {
                url: '^/global/authentication',
                templateUrl: 'html/global/authentication/index.html',
                controller: 'AuthenticationController'
            });
            $stateProvider.state('api', {
                abstract: true,
                url: '/api/:apiName/:version',
                template: '<ui-view/>',
                resolve: {
                    api: ['$stateParams', 'ApiRepository', function($stateParams, ApiRepository) {
                        return ApiRepository.getApi($stateParams.apiName, $stateParams.version);
                    }]
                }
            });
            $stateProvider.state('api.overview', {
                url: '/overview',
                templateUrl: 'html/api/overview.html',
                controller: 'ApiOverviewController'
            });
            $stateProvider.state('api.authorization', {
                url: '/authorization',
                templateUrl: 'html/api/authorization.html',
                controller: 'ApiAuthorizationController',
                resolve: {
                    apiAuthorizations: ['$stateParams', 'ApiAuthorizationRepository', function ($stateParams, ApiAuthorizationRepository) {
                        return ApiAuthorizationRepository.getApiAuthorization($stateParams.apiName, $stateParams.version);
                    }],
                    authentication: ['AuthenticationRepository', function (AuthenticationRepository) {
                        return AuthenticationRepository.hasAuthentication();
                    }]
                }
            });
            $stateProvider.state('api.rest', {
                url: '/rest-services',
                templateUrl: 'html/api/rest-services/index.html',
                controller: 'ApiRestServicesController',
                resolve: {
                    dbAdapters: ['DbAdapterResource', function (DbAdapterResource) {
                        return DbAdapterResource.getList();
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
            $stateProvider.state('api.rpc', {
                url: '/rpc-services',
                templateUrl: 'html/api/rpc-services/index.html',
                controller: 'ApiRpcServicesController',
                resolve: {
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
            $urlRouterProvider.otherwise('/dashboard');
        }
    );

})();
