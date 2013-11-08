'use strict';

var module = angular.module('ag-admin', ['tags-input']);

module.config(['$routeProvider', '$provide', function($routeProvider, $provide) {

    // setup the API Base Path
    $provide.value('apiBasePath', '/admin/api');

    $routeProvider.when('/dashboard', {
        templateUrl: '/zf-apigility-admin/partials/index.html',
        controller: 'DashboardController'
    });
    $routeProvider.when('/global/db-adapters', {
        templateUrl: '/zf-apigility-admin/partials/global/db-adapters.html',
        controller: 'DbAdapterController'
    });
    $routeProvider.when('/api/:apiName/info', {
        templateUrl: '/zf-apigility-admin/partials/api.html',
        controller: 'ApiInfoController',
        resolve: {
            api: ['$route', '$q', 'ApisResource', function ($route, $q, ApisResource) {
                return ApisResource.getApi($route.current.params.apiName);
            }]
        }
    });
    $routeProvider.when('/api/:apiName/rest-services', {
        templateUrl: '/zf-apigility-admin/partials/api/rest-services.html',
        controller: 'ApiRestServicesController'
    });
    $routeProvider.when('/api/:apiName/rpc-services', {
        templateUrl: '/zf-apigility-admin/partials/api/rpc-services.html',
        controller: 'ApiRpcServicesController'
    });
    $routeProvider.otherwise({redirectTo: '/dashboard'});
}]);

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
//                    updateApiList();
                    $location.path('/api/' + newApi.name + '/info');
                });
            });
        };

        $scope.resetForm = function () {
            $scope.showNewApiForm = false;
            $scope.apiName = '';
        };

        ApisResource.getApis().then(function (apis) {
            $scope.apis = apis;
        });


//        ApisResource.getApis().then(function (apis) {
//            return apis;
//        });

//        var updateApiList = function () {
//            ApisResource.fetch().then(function (apis) {
//                $scope.$apply(function () {
//                    $scope.apis = _.pluck(apis.embedded.module, 'props');
//                });
//            });
//        };
//
//        updateApiList();
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

module.controller('ApiInfoController', ['$http', '$rootScope', '$scope', 'ApisResource', function ($http, $rootScope, $scope, ApisResource) {

//    ApisResource.getCurrentApi.then(function (apiModel) {
//        $scope.$apply(function () {
//            $scope.api = apiModel;
//        });
//    });
//
//    $rootScope.$on('api.updated', function (event, data) {
//        $scope.$apply(function () {
//            $scope.api = data.apiModel;
//        });
//    });

}]);

module.controller('ApiRestController', ['$http', '$rootScope', '$scope', 'ApisResource', '$log', function ($http, $rootScope, $scope, ApisResource, $log) {

    $scope.$log = $log;

    $rootScope.$on('api.updated', function (event, data) {
        $scope.$apply(function () {
            $scope.api = data.apiModel;
            _($scope.api.restServices).forEach(function (restService) {
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

    $scope.createNewRestService = function () {
        ApisResource.createNewRestService($scope.api.name, $scope.restServiceName).then(function (restResource) {
            ApisResource.setApiModel($scope.api.name, null, true).then(function (apiModel) {});
            $scope.showNewRestServiceForm = false;
            $scope.restServiceName = '';
        });
    };

    $scope.createNewDbConnectedService = function () {
        ApisResource.createNewDbConnectedService($scope.api.name, $scope.dbAdapterName, $scope.dbTableName).then(function (restResource) {
            ApisResource.setApiModel($scope.api.name, null, true).then(function (apiModel) {});
            $scope.showNewRestServiceForm = false;
            $scope.dbAdapterName = '';
            $scope.dbTableName = '';
        });
    };

    $scope.saveRestService = function (index) {
        var restServiceData = _.clone($scope.api.restServices[index]);

        _(['collection_http_methods', 'resource_http_methods']).forEach(function (httpItem) {
            restServiceData[httpItem] = _.chain(restServiceData[httpItem])
                .where({checked: true})
                .pluck('name')
                .valueOf();
        });

        ApisResource.saveRestService($scope.api.name, restServiceData).then(function (data) {
            ApisResource.setApiModel($scope.api.name, null, true).then(function (apiModel) {});
        });
    };

    $scope.removeRestService = function (restServiceName) {
        ApisResource.removeRestService($scope.api.name, restServiceName).then(function (data) {
            ApisResource.setApiModel($scope.api.name, null, true).then(function (apiModel) {});
            $scope.deleteRestService = false;
        });
    };

    $scope.getSourceCode = function (className, classType) {
        ApisResource.getSourceCode ($scope.api.name, className)
            .then(function (data) {
                $scope.filename = className + '.php';
                $scope.class_type = classType + ' Class';
                $scope.source_code = data.source;
            });
    };
}]);

module.controller('ApiRpcController', ['$http', '$rootScope', '$scope', 'ApisResource', function ($http, $rootScope, $scope, ApisResource) {

    $rootScope.$on('api.updated', function (event, data) {
        $scope.$apply(function () {
            $scope.api = data.apiModel;

            _($scope.api.rpcServices).forEach(function (rpcService) {
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
                rpcService.controller_class = rpcService.controller_service_name.replace(myReg, "$2\\$2Controller");
            });

        });
    });

    $scope.resetForm = function () {
        $scope.showNewRpcServiceForm = false;
        $scope.rpcServiceName = '';
        $scope.rpcServiceRoute = '';
    };

    $scope.createNewRpcService = function () {
        ApisResource.createNewRpcService($scope.api.name, $scope.rpcServiceName, $scope.rpcServiceRoute).then(function (rpcResource) {
            ApisResource.setApiModel($scope.api.name, null, true).then(function (apiModel) {});
            $scope.addRpcService = false;
            $scope.rpcServiceName = '';
            $scope.rpcServiceRoute = '';
        });
    };

    $scope.saveRpcService = function (index) {
        var rpcServiceData = _.clone($scope.api.rpcServices[index]);

        rpcServiceData.http_methods = _.chain(rpcServiceData.http_methods)
            .where({checked: true})
            .pluck('name')
            .valueOf();

        ApisResource.saveRpcService($scope.api.name, rpcServiceData).then(function (data) {
            ApisResource.setApiModel($scope.api.name, null, true).then(function (apiModel) {});
        });
    };

    $scope.removeRpcService = function (rpcServiceName) {
        ApisResource.removeRpcService($scope.api.name, rpcServiceName).then(function () {
            ApisResource.setApiModel($scope.api.name, null, true).then(function (apiModel) {});
            $scope.deleteRestService = false;
        });
    };

    $scope.getSourceCode = function (className, classType) {
        ApisResource.getSourceCode($scope.api.name, className).then(function (data) {
            $scope.filename = className + '.php';
            $scope.class_type = classType + ' Class';
            $scope.source_code = data.source;
        });
    };

}]);










//module.controller(
//    'ApiController',
//    ['$rootScope', '$scope', '$routeParams', 'ApisResource', 'DbAdapterResource', function($rootScope, $scope, $routeParams, ApisResource, DbAdapterResource) {
//
//        $scope.api = null;
//        $scope.section = null;
//        $scope.content_negotiation = [
//            "HalJson",
//            "Json"
//        ];
//        $scope.source_code = [];
//
//        DbAdapterResource.fetch().then(function (adapters) {
//            $scope.$apply(function () {
//                $scope.dbAdapters = _.pluck(adapters.embedded.db_adapter, 'props');
//            });
//        });
//
//        // first run
//        ApisResource.setApiModel($routeParams.apiName, null, true).then(function (api) {
//            $scope.$apply(function () {
//                // controller scope
//                $scope.api = api;
//                $scope.currentApiVersion = ApisResource.currentApiVersion;
//                $scope.section = $routeParams.section;
//
//                // root scope page elements
//                $rootScope.pageTitle = api.namespace;
//                $rootScope.pageDescription = 'tbd';
//            });
//
//        });
//
//        $scope.$watch('currentApiVersion', function () {
//            if ($scope.currentApiVersion != null) {
//                ApisResource.setApiModel($scope.api.name, $scope.currentApiVersion, true).then(function (apiModel) {
//                    $scope.$apply(function () {
//                        $scope.api = apiModel;
//                    })
//                });
//            }
//        });
//
//        $scope.createNewApiVersion = function () {
//            ApisResource.createNewVersion($scope.api.name).then(function (data) {
//                ApisResource.setApiModel($scope.api.name, data.version, true).then(function (apiModel) {
//                    $scope.$apply(function () {
//                        $scope.api = apiModel;
//                        $scope.currentApiVersion = data.version;
//                    })
//                });
//            });
//        };
//
////        $scope.currentApiVersion = null;
//
//    }]
//);

module.directive('viewNavigation', ['$routeParams', '$location', function ($routeParams, $location) {
    return {
        restrict: 'E',
        scope: true,
        templateUrl: '/zf-apigility-admin/partials/view-navigation.html',
        controller: ['$rootScope', '$scope', function ($rootScope, $scope) {
            $scope.routeParams = $routeParams;
        }]
    }
}]);

module.factory('ApisResource', ['$rootScope', '$q', '$http', '$location', 'apiBasePath', '$timeout', function ($rootScope, $q, $http, $location, apiBasePath, $timeout) {
    var moduleApiPath = apiBasePath + '/module';

    return {

        hyperagentResource: new Hyperagent.Resource(moduleApiPath),

        currentApiModel: null,

        getApis: function (force) {
            var apisModel = [];
            var deferred = $q.defer();
            this.hyperagentResource.fetch({force: !!force}).then(function (apis) {
                apisModel = _.pluck(apis.embedded.module, 'props');
                // make $q and Q play nice together
                $rootScope.$apply(function () {
                    deferred.resolve(apisModel);
                });
            });
            return deferred.promise;
        },

        getApi: function (name, version, force) {
            var apiModel = {};
            var deferred = $q.defer();

            this.hyperagentResource.fetch({force: !!force}).then(function (apis) {
                var api = _.find(apis.embedded.module, function (m) {
                    return m.props.name === name;
                });

                _.forEach(api.props, function (value, key) {
                    apiModel[key] = value;
                });

                apiModel.restServices = [];
                apiModel.rpcServices = [];

                var latestVersion = api.props.versions[api.props.versions.length - 1];
//
//                if (resource.lastApi && resource.lastApi.name != name) {
//                    resource.lastApi = null;
//                    resource.currentApiVersion = 0;
//                }
//
//                if (version === null && resource.currentApiVersion > 0) {
//                    version = resource.currentApiVersion;
//                } else if (typeof version != 'number') {
//                    version = latestVersion;
//                }

                return api;
            }).then(function (api) {
                // now load REST + RPC endpoints
                return api.link('rest', {version: version}).fetch().then(function (restServices) {
                    _.chain(restServices.embedded.rest)
                        .pluck('props')
                        .forEach(function (item) {
                            apiModel.restServices.push(item);
                        });
                    return api;
                });
            }).then(function (api) {
                // now load REST + RPC endpoints
                return api.link('rpc', {version: version}).fetch().then(function (rpcServices) {
                    _.chain(rpcServices.embedded.rpc)
                        .pluck('props')
                        .forEach(function (item) {
                            apiModel.rpcServices.push(item);
                        });
                    return api;
                });

            }).then(function (api) {
                this.currentApiModel = apiModel;
                // make $q and Q play nice together
                $rootScope.$apply(function () {
                    deferred.resolve(apiModel);
                });
            });

            return deferred.promise;
        },

        createNewApi: function (name) {
            return $http.post(moduleApiPath, {name: name})
                .then(function (response) {
                    return response.data;
                });
        },

        createNewRestService: function (apiName, restServiceName) {
            return $http.post(moduleApiPath + '/' + apiName + '/rest', {resource_name: restServiceName})
                .then(function (response) {
                    return response.data;
                });
        },

        createNewDbConnectedService: function(apiName, dbAdapterName, dbTableName) {
            return $http.post(moduleApiPath + '/' +  + apiName + '/rest', {adapter_name: dbAdapterName, table_name: dbTableName})
                .then(function (response) {
                    return response.data;
                });
        },

        createNewRpcService: function (apiName, rpcServiceName, rpcServiceRoute) {
            return $http.post(moduleApiPath + '/' +  + apiName + '/rpc', {service_name: rpcServiceName, route: rpcServiceRoute})
                .then(function (response) {
                    return response.data;
                });
        },

        removeRestService: function (apiName, restServiceName) {
            var url = moduleApiPath + '/' +  + apiName + '/rest/' + encodeURIComponent(restServiceName);
            return $http.delete(url)
                .then(function (response) {
                    return response.data;
                });
        },

        saveRestService: function (apiName, restService) {
            var url = '/admin/api/module/' + apiName + '/rest/' + encodeURIComponent(restService.controller_service_name);
            return $http({method: 'patch', url: url, data: restService})
                .then(function (response) {
                    return response.data;
                });
        },

        removeRpcService: function (apiName, rpcServiceName) {
            var url = moduleApiPath + '/' +  + apiName + '/rpc/' + encodeURIComponent(rpcServiceName);
            return $http.delete(url)
                .then(function (response) {
                    return response.data;
                });
        },

        saveRpcService: function (apiName, rpcService) {
            var url = moduleApiPath + '/' +  + apiName + '/rpc/' + encodeURIComponent(rpcService.controller_service_name);
            return $http({method: 'patch', url: url, data: rpcService})
                .then(function (response) {
                    return response.data;
                });
        },

        getSourceCode: function (apiName, className) {
            return $http.get(apiBasePath + '/source?module=' + apiName + '&class=' + className)
                .then(function(response) {
                    return response.data;
                });
        },

        createNewVersion: function (apiName) {
            return $http({method: 'patch', url: apiBasePath + '/versioning', data: {module: apiName}})
                .then(function (response) {
                    return response.data;
                });
        }
    };

}]);

module.factory('DbAdapterResource', ['$http', '$location', 'apiBasePath', function ($http, $location, apiBasePath) {

    var dbAdapterApiPath = apiBasePath + '/db-adapter';

    var resource =  new Hyperagent.Resource(dbAdapterApiPath);

    resource.createNewAdapter = function (options) {
        return $http.post(dbAdapterApiPath, options)
            .then(function (response) {
                return response.data;
            });
    };

    resource.saveAdapter = function (name, data) {
        return $http({method: 'patch', url: dbAdapterApiPath + '/' + encodeURIComponent(name), data: data})
            .then(function (response) {
                return response.data;
            });
    };

    resource.removeAdapter = function (name) {
        return $http.delete(dbAdapterApiPath + '/' + encodeURIComponent(name))
            .then(function (response) {
                return true;
            });
    };

    return resource;
}]);

module.run(['$rootScope', '$routeParams', '$q', function ($rootScope, $routeParams, $q) {
    $rootScope.routeParams = $routeParams;
    $rootScope.currentApi = null;

    $rootScope.$on('$routeChangeSuccess', function(scope, next, current){
        console.log('Changing from '+angular.toJson(current)+' to '+angular.toJson(next));
    });
    $rootScope.$on("$routeChangeError", function (event, current, previous, rejection) {
        console.log('error be found');
    });

}]);
