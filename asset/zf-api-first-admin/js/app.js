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
    'ApiListController',
    ['$rootScope', '$scope', '$location', 'ApisResource', function($rootScope, $scope, $location, ApisResource) {

        $scope.apis = [];
        $scope.showNewApiForm = false;

        $scope.createNewApi = function () {
            ApisResource.createNewApi($scope.apiName).then(function (newApi) {
                ApisResource.fetch({force: true}).then(function (apis) {
                    $scope.resetForm();
                    updateApiList();
                    $location.path('/api/' + newApi.name + '/info');
                });
            });
        };

        $scope.resetForm = function () {
            $scope.showNewApiForm = false;
            $scope.apiName = '';
        };

        var updateApiList = function () {
            ApisResource.fetch().then(function (apis) {
                $scope.$apply(function () {
                    $scope.apis = _.pluck(apis.embedded.module, 'props');
                });
            });
        };

        updateApiList();
    }]
);

module.controller(
    'ApiController',
    ['$rootScope', '$scope', '$routeParams', 'ApisResource', function($rootScope, $scope, $routeParams, ApisResource) {

        $scope.api = null;
        $scope.section = null;

        ApisResource.fetch().then(function (apis) {

            var api = _.find(apis.embedded.module, function (m) {
                return m.props.name === $routeParams.apiName;
            });

            $scope.$apply(function () {
                $scope.api = api;
                $scope.section = $routeParams.section;
                $rootScope.pageTitle = api.props.namespace;
                $rootScope.pageDescription = 'tbd';
            });

        });

    }]
);

// this should probably be a directive
module.directive('viewNavigation', ['$routeParams', function ($routeParams) {
    return {
        restrict: 'E',
        scope: true,
        templateUrl: '/zf-api-first-admin/partials/view-navigation.html'
        ,
        controller: ['$scope', function ($scope) {
            $scope.routeParams = $routeParams;
        }]
    }
}]);

module.directive('apiRestEndpoints', function () {
    return {
        restrict: 'E',
        templateUrl: '/zf-api-first-admin/partials/api/rest-endpoints.html',
        controller: ['$rootScope', '$scope', 'ApisResource', function ($rootScope, $scope, ApisResource) {
            $scope.api = $scope.$parent.api;

            $scope.resetForm = function () {
                $scope.showNewRestEndpointForm = false;
                $scope.restEndpointName = '';
            };

            function updateApiRestEndpoints(force) {
                $scope.restEndpoints = [];
                $scope.api.links['rest'].fetch({force: force}).then(function (restEndpoints) {
                    // update view
                    console.log(restEndpoints);
                    $scope.$apply(function() {
                        $scope.restEndpoints = _.pluck(restEndpoints.embedded.rest, 'props');
                    });
                });
            }
            updateApiRestEndpoints(false);

            $scope.createNewRestEndpoint = function () {
                ApisResource.createNewRestEndpoint($scope.api.props.name, $scope.restEndpointName).then(function (restResource) {
                    updateApiRestEndpoints(true);
                    $scope.addRestEndpoint = false;
                    $scope.restEndpointName = '';
                });
            };

            $scope.saveRestEndpoint = function (index) {
                console.log($scope.restEndpoints[index]);
            };

            $scope.removeRestEndpoint = function () {
                ModuleResource.removeRestEndpoint($scope.api.props.name, $scope.restEndpointName).then(function (restResource) {
                    updateApiRestEndpoints(true);
                    $scope.deleteRestEndpoint = false;
                });
            };

            $scope.foo = function () {
                var a = $('#collapseOne');
                console.log(a);
                a.collapse('toggle');
            };
        }]
    }
});

module.directive('apiRpcEndpoints', function () {
    return {
        restrict: 'E',
        templateUrl: '/zf-api-first-admin/partials/api/rpc-endpoints.html',
        controller: ['$rootScope', '$scope', 'ApisResource', function ($rootScope, $scope, ApisResource) {
            $scope.api = $scope.$parent.api;

            $scope.resetForm = function () {
                $scope.showNewRpcEndpointForm = false;
                $scope.rpcEndpointName = '';
                $scope.rpcEndpointRoute = '';
            };

            function updateApiRpcEndpoints(force) {
                $scope.rpcEndpoints = [];
                $scope.api.links['rpc'].fetch({force: force}).then(function (rpcEndpoints) {
                    // update view
                    $scope.$apply(function() {
                        $scope.rpcEndpoints = _.pluck(rpcEndpoints.embedded.rpc, 'props');
                    });
                });
            }
            updateApiRpcEndpoints(false);

            $scope.createNewRpcEndpoint = function () {
                ApisResource.createNewRpcEndpoint($scope.api.props.name, $scope.rpcEndpointName, $scope.rpcEndpointRoute).then(function (rpcResource) {
                    updateApiRpcEndpoints(true);
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
    $routeProvider.when('/api/:apiName/:section', {templateUrl: '/zf-api-first-admin/partials/api.html', controller: 'ApiController'});
    $routeProvider.otherwise({redirectTo: '/dashboard'})
}]);

module.factory('ApisResource', ['$http', function ($http) {
    var resource = new Hyperagent.Resource('/admin/api/module');

    resource.createNewApi = function (name) {
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

    resource.deleteRestEndpoint = function (moduleName, restEndpoint) {
        // @todo add the remove rest endpoint API call
        return;
    };

    resource.saveRestEndpoint = function (moduleName, restEndpoint) {
        // @todo add the save rest endpoint API call
        return;
    };

    return resource;
}]);

module.run(['$rootScope', '$routeParams', function ($rootScope, $routeParams) {
    $rootScope.routeParams = $routeParams;
}]);
