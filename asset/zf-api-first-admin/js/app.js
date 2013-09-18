'use strict';

var module = angular.module('ag-admin', ['tags-input']);

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
    'DbAdapterController',
    ['$rootScope', '$scope', '$location', 'DbAdapterResource', function ($rootScope, $scope, $location, DbAdapterResource) {
        $scope.dbAdapters = [];
        $scope.showNewDbAdapterForm = false;

        $scope.resetForm = function () {
            $scope.showNewDbAdapterForm = false;
            $scope.adapterName = '';
            $scope.driver      = '';
            $scope.database    = '';
            $scope.username    = '';
            $scope.password    = '';
            $scope.hostname    = 'localhost';
            $scope.port        = '';
            $scope.charset     = 'UTF-8';
        };

        function updateDbAdapters(force) {
            $scope.dbAdapters = [];
            DbAdapterResource.fetch({force: force}).then(function (dbAdapters) {
                $scope.$apply(function () {
                    $scope.dbAdapters = _.pluck(dbAdapters.embedded.db_adapter, 'props');
                });
            });
        }
        updateDbAdapters(false);

        $scope.createNewDbAdapter = function () {
            var options = {
                adapter_name :  $scope.adapter_name,
                driver       :  $scope.driver,
                database     :  $scope.database,
                username     :  $scope.username,
                password     :  $scope.password,
                hostname     :  $scope.hostname,
                port         :  $scope.port,
                charset      :  $scope.charset
            };
            DbAdapterResource.createNewAdapter(options).then(function (dbAdapter) {
                updateDbAdapters(true);
                $scope.resetForm();
            });
        };

        $scope.saveDbAdapter = function (index) {
            var dbAdapter = $scope.dbAdapters[index];
            var options = {
                driver   :  dbAdapter.driver,
                database :  dbAdapter.database,
                username :  dbAdapter.username,
                password :  dbAdapter.password,
                hostname :  dbAdapter.hostname,
                port     :  dbAdapter.port,
                charset  :  dbAdapter.charset
            };
            DbAdapterResource.saveAdapter(dbAdapter.adapter_name, options).then(function (dbAdapter) {
                updateDbAdapters(true);
            });
        };

        $scope.removeDbAdapter = function (adapter_name) {
            DbAdapterResource.removeAdapter(adapter_name).then(function () {
                updateDbAdapters(true);
                $scope.deleteDbAdapter = false;
            });
        };


    }]
);

module.controller(
    'ApiController',
    ['$rootScope', '$scope', '$routeParams', 'ApisResource', 'DbAdapterResource', function($rootScope, $scope, $routeParams, ApisResource, DbAdapterResource) {

        $scope.api = null;
        $scope.section = null;
        $scope.content_negotiation = [
            "HalJson", 
            "Json"
        ];

        DbAdapterResource.fetch().then(function (adapters) {
            $scope.$apply(function () {
                $scope.dbAdapters = _.pluck(adapters.embedded.db_adapter, 'props');
            });
        });

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
        controller: ['$http', '$rootScope', '$scope', 'ApisResource', function ($http, $rootScope, $scope, ApisResource) {
            $scope.api = $scope.$parent.api;

            $scope.resetForm = function () {
                $scope.showNewRestEndpointForm = false;
                $scope.restEndpointName = '';
                $scope.dbAdapterName = '';
                $scope.dbTableName = '';
            };

            function updateApiRestEndpoints(force) {
                $scope.restEndpoints = [];
                $scope.restEndpointsEditable = [];
                $scope.api.links['rest'].fetch({force: force}).then(function (restEndpoints) {
                    // update view
                    $scope.$apply(function() {
                        $scope.restEndpoints = _.pluck(restEndpoints.embedded.rest, 'props');

                        _($scope.restEndpoints).forEach(function (restEndpoint) {
                            _(['collection_http_methods', 'resource_http_methods']).forEach(function (httpItem) {
                                var checkify = [];
                                _.forEach(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], function (httpMethod) {
                                    checkify.push({name: httpMethod, checked: _.contains(restEndpoint[httpItem], httpMethod)});
                                });
                                restEndpoint[httpItem] = checkify;

                                restEndpoint[httpItem + '_view'] = _.chain(restEndpoint[httpItem])
                                    .where({checked: true})
                                    .pluck('name')
                                    .valueOf()
                                    .join(', ');
                            });
                        });

                    });
                });
            }
            updateApiRestEndpoints(false);

            $scope.createNewRestEndpoint = function () {
                ApisResource.createNewRestEndpoint($scope.api.props.name, $scope.restEndpointName).then(function (restResource) {
                    updateApiRestEndpoints(true);
                    $scope.showNewRestEndpointForm = false;
                    $scope.restEndpointName = '';
                });
            };

            $scope.createNewDbConnectedEndpoint = function () {
                ApisResource.createNewDbConnectedEndpoint($scope.api.props.name, $scope.dbAdapterName, $scope.dbTableName)
                    .then(function (restResource) {
                        updateApiRestEndpoints(true);
                        $scope.showNewRestEndpointForm = false;
                        $scope.dbAdapterName = '';
                        $scope.dbTableName = '';
                    });
            };

            $scope.saveRestEndpoint = function (index) {
                var restEndpointData = _.clone($scope.restEndpoints[index]);

                _(['collection_http_methods', 'resource_http_methods']).forEach(function (httpItem) {
                    restEndpointData[httpItem] = _.chain(restEndpointData[httpItem])
                    .where({checked: true})
                    .pluck('name')
                    .valueOf();
                });

                ApisResource.saveRestEndpoint($scope.api.props.name, restEndpointData)
                    .then(function (data) {
                        updateApiRestEndpoints(true);
                    });
            };

            $scope.removeRestEndpoint = function (restEndpointName) {
                ApisResource.removeRestEndpoint($scope.api.props.name, restEndpointName)
                    .then(function (data) {
                        updateApiRestEndpoints(true);
                        $scope.deleteRestEndpoint = false;
                    });
            };

            $scope.getSourceCode = function (index) {
                $http.get('/admin/api/source?module=' + $scope.api.props.name + '&class=' + $scope.restEndpoints[index].entity_class)
                    .then(function(response) {
                        $scope.source_code = response.data.source;
                        return true;
                    });
            };
        }]
    };
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

                        _($scope.rpcEndpoints).forEach(function (rpcEndpoint) {
                            var checkify = [];
                            _.forEach(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], function (httpMethod) {
                                checkify.push({name: httpMethod, checked: _.contains(rpcEndpoint.http_methods, httpMethod)});
                            });
                            rpcEndpoint.http_methods = checkify;

                            rpcEndpoint.http_methods_view = _.chain(rpcEndpoint.http_methods)
                                .where({checked: true})
                                .pluck('name')
                                .valueOf()
                                .join(', ');
                        });
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

            $scope.saveRpcEndpoint = function (index) {
                var rpcEndpointData = _.clone($scope.rpcEndpoints[index]);

                rpcEndpointData.http_methods = _.chain(rpcEndpointData.http_methods)
                    .where({checked: true})
                    .pluck('name')
                    .valueOf();

                ApisResource.saveRpcEndpoint($scope.api.props.name, rpcEndpointData)
                    .then(function (data) {
                        updateApiRpcEndpoints(true);
                    });
            };

            $scope.removeRpcEndpoint = function (rpcEndpointName) {
                ApisResource.removeRpcEndpoint($scope.api.props.name, rpcEndpointName)
                    .then(function () {
                        updateApiRpcEndpoints(true);
                        $scope.deleteRestEndpoint = false;
                    });
            };
        }]
    }
});

module.config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
    $routeProvider.when('/dashboard', {templateUrl: '/zf-api-first-admin/partials/index.html', controller: 'DashboardController'});
    $routeProvider.when('/global/db-adapters', {templateUrl: '/zf-api-first-admin/partials/global/db-adapters.html', controller: 'DbAdapterController'});
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

    resource.createNewDbConnectedEndpoint = function(apiName, dbAdapterName, dbTableName) {
        return $http.post('/admin/api/module/' + apiName + '/rest', {adapter_name: dbAdapterName, table_name: dbTableName})
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


    resource.removeRestEndpoint = function (apiName, restEndpointName) {
        var url = '/admin/api/module/' + apiName + '/rest/' + encodeURIComponent(restEndpointName);
        return $http.delete(url)
            .then(function (response) {
                return response.data;
            });
    };

    resource.saveRestEndpoint = function (apiName, restEndpoint) {
        var url = '/admin/api/module/' + apiName + '/rest/' + encodeURIComponent(restEndpoint.controller_service_name);
        return $http({method: 'patch', url: url, data: restEndpoint})
            .then(function (response) {
                return response.data;
            });
    };

    resource.removeRpcEndpoint = function (apiName, rpcEndpointName) {
        var url = '/admin/api/module/' + apiName + '/rpc/' + encodeURIComponent(rpcEndpointName);
        return $http.delete(url)
            .then(function (response) {
                return response.data;
            });
    };

    resource.saveRpcEndpoint = function (apiName, rpcEndpoint) {
        var url = '/admin/api/module/' + apiName + '/rpc/' + encodeURIComponent(rpcEndpoint.controller_service_name);
        return $http({method: 'patch', url: url, data: rpcEndpoint})
            .then(function (response) {
                return response.data;
            });
    };

    return resource;
}]);

module.factory('DbAdapterResource', ['$http', function ($http) {
    var resource =  new Hyperagent.Resource('/admin/api/db-adapter');

    resource.createNewAdapter = function (options) {
        return $http.post('/admin/api/db-adapter', options)
            .then(function (response) {
                return response.data;
            });
    };

    resource.saveAdapter = function (name, data) {
        return $http({method: 'patch', url: '/admin/api/db-adapter/' + encodeURIComponent(name), data: data})
            .then(function (response) {
                return response.data;
            });
    };

    resource.removeAdapter = function (name) {
        return $http.delete('/admin/api/db-adapter/' + encodeURIComponent(name))
            .then(function (response) {
                return true;
            });
    };

    return resource;
}]);

module.run(['$rootScope', '$routeParams', function ($rootScope, $routeParams) {
    $rootScope.routeParams = $routeParams;
}]);
