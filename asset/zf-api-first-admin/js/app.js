'use strict';

var module = angular.module('ag-admin', []);

module.controller(
    'DashboardController',
    ['$rootScope', function($rootScope) {
        $rootScope.pageTitle = 'Dashboard';
        $rootScope.pageDescription = 'Global system configuration and configuration to be applied to all APIs.';
    }]
);

module.controller(
    'APIListController',
    ['$rootScope', '$scope', '$location', 'APIsResource', function($rootScope, $scope, $location, APIsResource) {

        $scope.apis = [];

        $scope.createNewAPI = function () {
            APIsResource.createNewAPI($scope.apiName).then(function (newAPI) {
                APIsResource.fetch({force: true}).then(function (apis) {
                    $scope.resetForm();
                    updateAPIList();
                    $location.path('/api/' + newAPI.name + '/info');
                });
            });
        };

        $scope.resetForm = function () {
            $scope.addAPI = false;
            $scope.apiName = '';
        };

        var updateAPIList = function () {
            APIsResource.fetch().then(function (apis) {
                $scope.$apply(function () {
                    $scope.apis = apis.embedded.module;
                });
            });
        };

        updateAPIList();
    }]
);

// this should probably be a directive
module.controller(
    'ViewNavigationController',
    ['$rootScope', '$scope', '$routeParams', 'SecondaryNavigationService', function ($rootScope, $scope, $routeParams, SecondaryNavigationService) {

        function updateSecondaryNavigation() {
            if ($routeParams.apiName == undefined) {
                $scope.items = SecondaryNavigationService.getGlobalNavigation();
            } else {
                $scope.items = SecondaryNavigationService.getAPINavigation($routeParams.apiName);
                $scope.section = $routeParams.section;
            }
        }
        updateSecondaryNavigation();

        // on refresh, and initial load
        $scope.$on('$routeChangeSuccess', function () {
            updateSecondaryNavigation();
        });
    }]
);

module.controller(
    'APIController',
    ['$rootScope', '$scope', '$routeParams', 'APIsResource', function($rootScope, $scope, $routeParams, APIsResource) {

        $scope.api = null;
        $scope.section = null;

        APIsResource.fetch().then(function (apis) {

            // @todo matthew claims this is the full-enough resource now, perhaps refactor
            var briefModule = _.find(apis.embedded.module, function (m) {
                return m.props.name === $routeParams.apiName;
            });

            briefModule.links['self'].fetch().then(function (api) {
                // update UI immediately:
                $scope.$apply(function () {
                    $scope.api = api;
                    $rootScope.pageTitle = api.props.namespace;
                    $rootScope.pageDescription = 'tbd';
                    $scope.section = $routeParams.section;
                });
            });

        });

    }]
);

module.directive('apiRestEndpoints', function () {
    return {
        restrict: 'E',
        templateUrl: '/zf-api-first-admin/partials/api/rest-endpoints.html',
        controller: ['$rootScope', '$scope', 'APIsResource', function ($rootScope, $scope, APIsResource) {
            $scope.api = $scope.$parent.api;

            function updateAPIRestEndpoints(force) {
                $scope.restEndpoints = [];
                $scope.api.links['rest'].fetch({force: force}).then(function (restEndpoints) {
                    // update view
                    $scope.$apply(function() {
                        $scope.restEndpoints = restEndpoints.embedded.rest;
                    });
                });
            }
            updateAPIRestEndpoints(false);

            $scope.createNewRestEndpoint = function () {
                APIsResource.createNewRestEndpoint($scope.api.props.name, $scope.restEndpointName).then(function (restResource) {
                    updateAPIRestEndpoints(true);
                    $scope.addRestEndpoint = false;
                    $scope.restEndpointName = '';
                });
            };
        }]
    }
});

module.directive('apiRpcEndpoints', function () {
    return {
        restrict: 'E',
        templateUrl: '/zf-api-first-admin/partials/api/rpc-endpoints.html',
        controller: ['$rootScope', '$scope', 'APIsResource', function ($rootScope, $scope, APIsResource) {
            $scope.api = $scope.$parent.api;

            function updateAPIRpcEndpoints(force) {
                $scope.rpcEndpoints = [];
                $scope.api.links['rpc'].fetch({force: force}).then(function (rpcEndpoints) {
                    // update view
                    $scope.$apply(function() {
                        $scope.rpcEndpoints = rpcEndpoints.embedded.rpc;
                    });
                });
            }
            updateAPIRpcEndpoints(false);

            $scope.createNewRpcEndpoint = function () {
                APIsResource.createNewRpcEndpoint($scope.api.props.name, $scope.rpcEndpointName, $scope.rpcEndpointRoute).then(function (rpcResource) {
                    updateAPIRpcEndpoints(true);
                    $scope.addRpcEndpoint = false;
                    $scope.rpcEndpointName = '';
                    $scope.rpcEndpointRoute = '';
                });
            };
        }]
    }
});

module.config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
    $routeProvider.when('/dashboard', {templateUrl: '/zf-api-first-admin/partials/index.html', controller: 'DashboardController'});
    $routeProvider.when('/api/:apiName/:section', {templateUrl: '/zf-api-first-admin/partials/api.html', controller: 'APIController'});
    $routeProvider.otherwise({redirectTo: '/dashboard'})
}]);

module.factory('SecondaryNavigationService', function () {

    // @todo if this is not shared anymore, this should move to the appropriate controller
    return {
        getGlobalNavigation: function () {
            return [
                {id: 'general-information', name: "General Information", link: '/general-information'},
                {id: 'media-types', name: "Media Types", link: '/media-types'},
                {id: 'authentication', name: "Authentication", link: '/authentication'},
                {id: 'phpinfo', name: "phpinfo()", link: '/phpinfo'},
                {id: 'zf2info', name: "zf2info()", link: '/zf2info'}
            ];
        },
        getAPINavigation: function (apiName, section) {
            return [
                {id: 'info', name: "General Information", link: '/api/' + apiName + '/info'},
                {id: 'rest-endpoints', name: "REST Endpoints", link: '/api/' + apiName + '/rest-endpoints'},
                {id: 'rpc-endpoints', name: "RPC Endpoints", link: '/api/' + apiName + '/rpc-endpoints'},
                {id: 'authentication', name: "Authentication", link: '/api/' + apiName + '/authentication'},
                {id: 'filters-validators', name: "Filters / Validators", link: '/api/' + apiName + '/filters-validators'}
            ];
        }
    };
});

module.factory('APIsResource', ['$http', function ($http) {
    var resource = new Hyperagent.Resource('/admin/api/module');

    resource.createNewAPI = function (name) {
        return $http.post('/admin/api/module', {name: name})
            .then(function (response) {
                return response.data;
            });
    };

    resource.createNewRestEndpoint = function (apiName, restEndpointName) {
        return $http.post('/admin/api/module/' + apiName + '/rest', {resource_name: restEndpointName})
            .then(function (response) {
                return response.data;
            });
    };

    resource.createNewRpcEndpoint = function (apiName, rpcEndpointName, rpcEndpointRoute) {
        return $http.post('/admin/api/module/' + apiName + '/rpc', {service_name: rpcEndpointName, route: rpcEndpointRoute})
            .then(function (response) {
                return response.data;
            });
    };

    return resource;
}]);

module.run(['$rootScope', function ($rootScope) {

}]);
