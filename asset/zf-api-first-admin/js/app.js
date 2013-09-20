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
        $scope.source_code = [];

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

module.directive('viewNavigation', ['$routeParams', function ($routeParams) {
    return {
        restrict: 'E',
        scope: true,
        templateUrl: '/zf-api-first-admin/partials/view-navigation.html',
        controller: ['$scope', function ($scope) {
            $scope.routeParams = $routeParams;
        }]
    }
}]);

module.directive('apiInfo', function () {
    return {
        restrict : 'E',
        templateUrl: '/zf-api-first-admin/partials/api/info.html',
        controller:  ['$http', '$rootScope', '$scope', 'ApisResource', function ($http, $rootScope, $scope, ApisResource) {
            $scope.api = $scope.$parent.api;
            
            console.log($scope.api);

            $scope.restEndpoints = [];
            $scope.api.links['rest'].fetch({force: true}).then(function (restEndpoints) {
                // update view
                $scope.$apply(function() {
                    $scope.restEndpoints = _.pluck(restEndpoints.embedded.rest, 'props');
                });
            });

            $scope.rpcEndpoints = [];
            $scope.api.links['rpc'].fetch({force: true}).then(function (rpcEndpoints) {
                // update view
                $scope.$apply(function() {
                    $scope.rpcEndpoints = _.pluck(rpcEndpoints.embedded.rpc, 'props');
                });
            });
        }]
    };
});


module.directive('apiRestServices', function () {
    return {
        restrict: 'E',
        templateUrl: '/zf-api-first-admin/partials/api/rest-services.html',
        controller: ['$http', '$rootScope', '$scope', 'ApisResource', function ($http, $rootScope, $scope, ApisResource) {
            $scope.api = $scope.$parent.api;

            $scope.resetForm = function () {
                $scope.showNewRestServiceForm = false;
                $scope.restServiceName = '';
                $scope.dbAdapterName = '';
                $scope.dbTableName = '';
            };

            $scope.isDbConnected = function (restService) {
              if (typeof restService !== 'object' || restService === null) {
                return false;
              }
              if ("adapter_name" in restService || "table_name" in restService || "table_service" in restService || "hydrator_name" in restService) {
                return true;
              }
              return false;
            };

            function updateApiRestServices(force) {
                $scope.restServices = [];
                $scope.restServicesEditable = [];
                $scope.api.links['rest'].fetch({force: force}).then(function (restServices) {
                    // update view
                    $scope.$apply(function() {
                        $scope.restServices = _.pluck(restServices.embedded.rest, 'props');

                        _($scope.restServices).forEach(function (restService) {
                            _(['collection_http_methods', 'resource_http_methods']).forEach(function (httpItem) {
                                var checkify = [];
                                _.forEach(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], function (httpMethod) {
                                    checkify.push({name: httpMethod, checked: _.contains(restService[httpItem], httpMethod)});
                                });
                                restService[httpItem] = checkify;

                                restService[httpItem + '_view'] = _.chain(restService[httpItem])
                                    .where({checked: true})
                                    .pluck('name')
                                    .valueOf()
                                    .join(', ');
                            });
                        });

                    });
                });
            }
            updateApiRestServices(false);

            $scope.createNewRestService = function () {
                ApisResource.createNewRestService($scope.api.props.name, $scope.restServiceName).then(function (restResource) {
                    updateApiRestServices(true);
                    $scope.showNewRestServiceForm = false;
                    $scope.restServiceName = '';
                });
            };

            $scope.createNewDbConnectedService = function () {
                ApisResource.createNewDbConnectedService($scope.api.props.name, $scope.dbAdapterName, $scope.dbTableName)
                    .then(function (restResource) {
                        updateApiRestServices(true);
                        $scope.showNewRestServiceForm = false;
                        $scope.dbAdapterName = '';
                        $scope.dbTableName = '';
                    });
            };

            $scope.saveRestService = function (index) {
                var restServiceData = _.clone($scope.restServices[index]);

                _(['collection_http_methods', 'resource_http_methods']).forEach(function (httpItem) {
                    restServiceData[httpItem] = _.chain(restServiceData[httpItem])
                    .where({checked: true})
                    .pluck('name')
                    .valueOf();
                });

                ApisResource.saveRestService($scope.api.props.name, restServiceData)
                    .then(function (data) {
                        updateApiRestServices(true);
                    });
            };

            $scope.removeRestService = function (restServiceName) {
                ApisResource.removeRestService($scope.api.props.name, restServiceName)
                    .then(function (data) {
                        updateApiRestServices(true);
                        $scope.deleteRestService = false;
                    });
            };

            $scope.getSourceCode = function (className, classType) {
                ApisResource.getSourceCode ($scope.api.props.name, className)
                    .then(function (data) {
                        $scope.filename = className + '.php';
                        $scope.class_type = classType + ' Class';
                        $scope.source_code = data.source;
                    });
            };
        }]
    };
});

module.directive('apiRpcServices', function () {
    return {
        restrict: 'E',
        templateUrl: '/zf-api-first-admin/partials/api/rpc-services.html',
        controller: ['$http', '$rootScope', '$scope', 'ApisResource', function ($http, $rootScope, $scope, ApisResource) {
            $scope.api = $scope.$parent.api;

            $scope.resetForm = function () {
                $scope.showNewRpcServiceForm = false;
                $scope.rpcServiceName = '';
                $scope.rpcServiceRoute = '';
            };

            function updateApiRpcServices(force) {
                $scope.rpcServices = [];
                $scope.api.links['rpc'].fetch({force: force}).then(function (rpcServices) {
                    // update view
                    $scope.$apply(function() {
                        $scope.rpcServices = _.pluck(rpcServices.embedded.rpc, 'props');

                        _($scope.rpcServices).forEach(function (rpcService) {
                            var checkify = [];
                            _.forEach(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], function (httpMethod) {
                                checkify.push({name: httpMethod, checked: _.contains(rpcService.http_methods, httpMethod)});
                            });
                            rpcService.http_methods = checkify;

                            rpcService.http_methods_view = _.chain(rpcService.http_methods)
                                .where({checked: true})
                                .pluck('name')
                                .valueOf()
                                .join(', ');

                            var myReg = /(([^\\]+)\\Controller)$/g;
                            rpcEndpoint.controller_class = rpcEndpoint.controller_service_name.replace(myReg, "$2\\$2Controller");
                        });
                    });
                });
            }
            updateApiRpcServices(false);

            $scope.createNewRpcService = function () {
                ApisResource.createNewRpcService($scope.api.props.name, $scope.rpcServiceName, $scope.rpcServiceRoute).then(function (rpcResource) {
                    updateApiRpcServices(true);
                    $scope.addRpcService = false;
                    $scope.rpcServiceName = '';
                    $scope.rpcServiceRoute = '';
                });
            };

            $scope.saveRpcService = function (index) {
                var rpcServiceData = _.clone($scope.rpcServices[index]);

                rpcServiceData.http_methods = _.chain(rpcServiceData.http_methods)
                    .where({checked: true})
                    .pluck('name')
                    .valueOf();

                ApisResource.saveRpcService($scope.api.props.name, rpcServiceData)
                    .then(function (data) {
                        updateApiRpcServices(true);
                    });
            };

            $scope.removeRpcService = function (rpcServiceName) {
                ApisResource.removeRpcService($scope.api.props.name, rpcServiceName)
                    .then(function () {
                        updateApiRpcServices(true);
                        $scope.deleteRestService = false;
                    });
            };
            
            $scope.getSourceCode = function (className, classType) {
                ApisResource.getSourceCode ($scope.api.props.name, className)
                    .then(function (data) {
                        $scope.filename = className + '.php';
                        $scope.class_type = classType + ' Class';
                        $scope.source_code = data.source;
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

    resource.createNewRestService = function (apiName, restServiceName) {
        return $http.post('/admin/api/module/' + apiName + '/rest', {resource_name: restServiceName})
            .then(function (response) {
                return response.data;
            });
    };

    resource.createNewDbConnectedService = function(apiName, dbAdapterName, dbTableName) {
        return $http.post('/admin/api/module/' + apiName + '/rest', {adapter_name: dbAdapterName, table_name: dbTableName})
            .then(function (response) {
                return response.data;
            });
    };

    resource.createNewRpcService = function (apiName, rpcServiceName, rpcServiceRoute) {
        return $http.post('/admin/api/module/' + apiName + '/rpc', {service_name: rpcServiceName, route: rpcServiceRoute})
            .then(function (response) {
                return response.data;
            });
    };


    resource.removeRestService = function (apiName, restServiceName) {
        var url = '/admin/api/module/' + apiName + '/rest/' + encodeURIComponent(restServiceName);
        return $http.delete(url)
            .then(function (response) {
                return response.data;
            });
    };

    resource.saveRestService = function (apiName, restService) {
        var url = '/admin/api/module/' + apiName + '/rest/' + encodeURIComponent(restService.controller_service_name);
        return $http({method: 'patch', url: url, data: restService})
            .then(function (response) {
                return response.data;
            });
    };

    resource.removeRpcService = function (apiName, rpcServiceName) {
        var url = '/admin/api/module/' + apiName + '/rpc/' + encodeURIComponent(rpcServiceName);
        return $http.delete(url)
            .then(function (response) {
                return response.data;
            });
    };

    resource.saveRpcService = function (apiName, rpcService) {
        var url = '/admin/api/module/' + apiName + '/rpc/' + encodeURIComponent(rpcService.controller_service_name);
        return $http({method: 'patch', url: url, data: rpcService})
            .then(function (response) {
                return response.data;
            });
    };

    resource.getSourceCode = function (apiName, className) {
        return $http.get('/admin/api/source?module=' + apiName + '&class=' + className)
            .then(function(response) {
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
