'use strict';

var module = angular.module('ag-admin', ['ngRoute', 'tags-input']);

module.config(['$routeProvider', '$provide', function($routeProvider, $provide) {

    // setup the API Base Path (this should come from initial ui load/php)
    $provide.value('apiBasePath', '/admin/api');

    $routeProvider.when('/dashboard', {
        templateUrl: '/zf-apigility-admin/partials/index.html',
        controller: 'DashboardController'
    });
    $routeProvider.when('/global/db-adapters', {
        templateUrl: '/zf-apigility-admin/partials/global/db-adapters.html',
        controller: 'DbAdapterController'
    });
    $routeProvider.when('/global/authentication', {
        templateUrl: '/zf-apigility-admin/partials/global/authentication.html',
        controller: 'AuthenticationController'
    });
    $routeProvider.when('/api/:apiName/:version/overview', {
        templateUrl: '/zf-apigility-admin/partials/api/overview.html',
        controller: 'ApiOverviewController',
        resolve: {
            api: ['$route', 'ApiRepository', function ($route, ApiRepository) {
                return ApiRepository.getApi($route.current.params.apiName, $route.current.params.version);
            }]
        }
    });
    $routeProvider.when('/api/:apiName/:version/authorization', {
        templateUrl: '/zf-apigility-admin/partials/api/authorization.html',
        controller: 'ApiAuthorizationController',
        resolve: {
            apiAuthorizations: ['$route', 'ApiAuthorizationRepository', function ($route, ApiAuthorizationRepository) {
                return ApiAuthorizationRepository.getApiAuthorization($route.current.params.apiName, $route.current.params.version);
            }]
        }
    });
    $routeProvider.when('/api/:apiName/:version/rest-services', {
        templateUrl: '/zf-apigility-admin/partials/api/rest-services.html',
        controller: 'ApiRestServicesController',
        resolve: {
            api: ['$route', 'ApiRepository', function ($route, ApiRepository) {
                return ApiRepository.getApi($route.current.params.apiName, $route.current.params.version);
            }]
        }
    });
    $routeProvider.when('/api/:apiName/:version/rpc-services', {
        templateUrl: '/zf-apigility-admin/partials/api/rpc-services.html',
        controller: 'ApiRpcServicesController',
        resolve: {
            api: ['$route', 'ApiRepository', function ($route, ApiRepository) {
                return ApiRepository.getApi($route.current.params.apiName, $route.current.params.version);
            }]
        }
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
    ['$rootScope', '$scope', '$location', 'ApiRepository', '$timeout', function($rootScope, $scope, $location, ApiRepository, $timeout) {

        $scope.apis = [];
        $scope.showNewApiForm = false;

        $scope.refreshApiList = function () {
            ApiRepository.getList(true).then(function (apis) { $scope.apis = apis; });
        };

        $scope.createNewApi = function () {
            ApiRepository.createNewApi($scope.apiName).then(function (newApi) {
                // reset form, repopulate, redirect to new
                $scope.resetForm();
                $scope.refreshApiList();
                $timeout(function () {
                    $location.path('/api/' + newApi.name + '/v1/overview');
                }, 500);
            });
        };

        $scope.resetForm = function () {
            $scope.showNewApiForm = false;
            $scope.apiName = '';
        };

        $rootScope.$on('refreshApiList', function () { $scope.refreshApiList() });
    }]
);

module.controller(
    'DbAdapterController',
    ['$scope', '$location', 'DbAdapterResource', function ($scope, $location, DbAdapterResource) {
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
  'AuthenticationController',
  ['$scope', 'AuthenticationRepository', function ($scope, AuthenticationRepository) {

    $scope.showSetupButtons                 = false;
    $scope.showHttpBasicAuthenticationForm  = false;
    $scope.showHttpBasicAuthentication      = false;
    $scope.showHttpDigestAuthenticationForm = false;
    $scope.showHttpDigestAuthentication     = false;
    $scope.showOAuth2AuthenticationForm     = false;
    $scope.showOAuth2Authentication         = false;
    $scope.httpBasic                        = null;

    var fetchAuthenticationDetails = function (force) {
      AuthenticationRepository.fetch({force: force})
        .then(function (authentication) {
          var data = authentication.props;
          if (data.htpasswd) {
            $scope.$apply(function () {
              $scope.showSetupButtons = false;
              $scope.showHttpBasicAuthentication = true;
              $scope.httpBasic = data;
            });
          } else if (data.htdigest) {
            $scope.$apply(function () {
              $scope.showSetupButtons = false;
              $scope.showHttpDigestAuthentication = true;
              $scope.httpDigest = data;
            });
          } else if (data.oauth2) {
            $scope.$apply(function () {
              $scope.showSetupButtons = false;
              $scope.showOAuth2Authentication = true;
              $scope.oauth2 = data;
            });
          }
        }, function (err) {
          console.log("No authentication found!");
          $scope.$apply(function () {
            $scope.showSetupButtons             = true;
            $scope.showHttpBasicAuthentication  = false;
            $scope.showHttpDigestAuthentication = false;
            $scope.showOAuth2Authentication     = false;
            $scope.httpBasic                    = null;
          });
          return false;
        });
    };

    var createAuthentication = function (options) {
      AuthenticationRepository.createAuthentication(options).then(function (authentication) {
        fetchAuthenticationDetails(true);
        $scope.resetForm();
      });
    };

    var updateAuthentication = function (options) {
      AuthenticationRepository.updateAuthentication(options).then(function (authentication) {
        fetchAuthenticationDetails(true);
      });
    };

    $scope.resetForm = function () {
      $scope.showHttpBasicAuthenticationForm  = false;
      $scope.showHttpDigestAuthenticationForm = false;
      $scope.showOAuth2AuthenticationForm     = false;
      $scope.realm                            = '';
      $scope.htpasswd                         = '';
    };

    $scope.showAuthenticationSetup = function () {
      if ($scope.showHttpBasicAuthenticationForm || $scope.showHttpDigestAuthenticationForm || $scope.showOAuth2AuthenticationForm) {
        return false;
      }
      return $scope.showSetupButtons;
    };

    $scope.createHttpBasicAuthentication = function () {
      var options = {
        accept_schemes : [ "basic" ],
        realm          :  $scope.realm,
        htpasswd       :  $scope.htpasswd
      };
      createAuthentication(options);
    };

    $scope.updateHttpBasicAuthentication = function () {
      var options = {
        realm          :  $scope.httpBasic.realm,
        htpasswd       :  $scope.httpBasic.htpasswd
      };
      updateAuthentication(options);
    };

    $scope.removeAuthentication = function () {
      AuthenticationRepository.removeAuthentication()
        .then(function (response) {
          fetchAuthenticationDetails(true);
        });
    };

    fetchAuthenticationDetails(true);
}]);

module.controller('ApiOverviewController', ['$http', '$rootScope', '$scope', 'api', function ($http, $rootScope, $scope, api) {
    $scope.api = api;
}]);

module.controller(
    'ApiAuthorizationController',
    ['$http', '$rootScope', '$scope', '$routeParams', 'apiAuthorizations', 'ApiAuthorizationRepository', function ($http, $rootScope, $scope, $routeParams, apiAuthorizations, ApiAuthorizationRepository) {
        $scope.apiAuthorizations = apiAuthorizations;
        $scope.showModel = function () {
            console.log($scope.apiAuthorizations);
        };
        $scope.saveAuthorization = function () {
            ApiAuthorizationRepository.saveApiAuthorizations($routeParams.apiName, $scope.apiAuthorizations);
        };
    }]
);

module.controller('ApiRestServicesController', ['$http', '$rootScope', '$scope', '$timeout', 'ApiRepository', 'api', function ($http, $rootScope, $scope, $timeout, ApiRepository, api) {

    $scope.api = api;

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
        ApiRepository.createNewRestService($scope.api.name, $scope.restServiceName).then(function (restResource) {
            $timeout(function () {
                ApiRepository.getApi(restResource.module, 1, true).then(function (api) {
                    console.log(api);
                    $scope.api = api;
                });
            }, 500);
            $scope.showNewRestServiceForm = false;
            $scope.restServiceName = '';
        });
    };

    $scope.createNewDbConnectedService = function () {
        ApiRepository.createNewDbConnectedService($scope.api.name, $scope.dbAdapterName, $scope.dbTableName).then(function (restResource) {
            ApiRepository.setApiModel($scope.api.name, null, true).then(function (apiModel) {});
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

        ApiRepository.saveRestService($scope.api.name, restServiceData).then(function (data) {
            ApiRepository.setApiModel($scope.api.name, null, true).then(function (apiModel) {});
        });
    };

    $scope.removeRestService = function (restServiceName) {
        ApiRepository.removeRestService($scope.api.name, restServiceName).then(function (data) {
            ApiRepository.setApiModel($scope.api.name, null, true).then(function (apiModel) {});
            $scope.deleteRestService = false;
        });
    };

    $scope.getSourceCode = function (className, classType) {
        ApiRepository.getSourceCode ($scope.api.name, className)
            .then(function (data) {
                $scope.filename = className + '.php';
                $scope.class_type = classType + ' Class';
                $scope.source_code = data.source;
            });
    };
}]);

module.controller('ApiRpcServicesController', ['$http', '$rootScope', '$scope', 'ApiRepository', function ($http, $rootScope, $scope, ApiRepository) {

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
        ApiRepository.createNewRpcService($scope.api.name, $scope.rpcServiceName, $scope.rpcServiceRoute).then(function (rpcResource) {
            ApiRepository.setApiModel($scope.api.name, null, true).then(function (apiModel) {});
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

        ApiRepository.saveRpcService($scope.api.name, rpcServiceData).then(function (data) {
            ApiRepository.setApiModel($scope.api.name, null, true).then(function (apiModel) {});
        });
    };

    $scope.removeRpcService = function (rpcServiceName) {
        ApiRepository.removeRpcService($scope.api.name, rpcServiceName).then(function () {
            ApiRepository.setApiModel($scope.api.name, null, true).then(function (apiModel) {});
            $scope.deleteRestService = false;
        });
    };

    $scope.getSourceCode = function (className, classType) {
        ApiRepository.getSourceCode($scope.api.name, className).then(function (data) {
            $scope.filename = className + '.php';
            $scope.class_type = classType + ' Class';
            $scope.source_code = data.source;
        });
    };

}]);

module.controller(
    'ApiVersionController',
    ['$rootScope', '$scope', '$location', '$timeout', '$routeParams', 'ApiRepository', function($rootScope, $scope, $location, $timeout, $routeParams, ApiRepository) {

        ApiRepository.getApi($routeParams.apiName, $routeParams.version).then(function (api) {
            $scope.api = api;
            $scope.currentVersion = api.version;
        });

        $scope.createNewApiVersion = function () {
            ApiRepository.createNewVersion($scope.api.name).then(function (data) {

                $rootScope.$broadcast('refreshApiList');
                $timeout(function () {
                    $location.path('/api/' + $scope.api.name + '/v' + data.version + '/overview');
                }, 500);
            });
        };

        $scope.updateVersion = function () {
            $timeout(function () {
                $location.path('/api/' + $scope.api.name + '/v' + $scope.currentVersion + '/overview');
            }, 500);
        };

    }]
);

module.factory('ApiRepository', ['$rootScope', '$q', '$http', 'apiBasePath', function ($rootScope, $q, $http, apiBasePath) {
    var moduleApiPath = apiBasePath + '/module';

    return {

        hyperagentResource: new Hyperagent.Resource(moduleApiPath),

        currentApiModel: null,

        getList: function (force) {
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

            // localize this for future use
            var self = this;

            if (typeof version == 'string') {
                version = parseInt(version.match(/\d/g)[0]);
            }

            if (!force && self.currentApiModel && version && self.currentApiModel.name == name && self.currentApiModel.version == version) {
                deferred.resolve(self.currentApiModel);
                return deferred.promise;
            }

            this.hyperagentResource.fetch({force: !!force}).then(function (apis) {
                var api = _.find(apis.embedded.module, function (m) {
                    return m.props.name === name;
                });

                _.forEach(api.props, function (value, key) {
                    apiModel[key] = value;
                });

                apiModel.restServices = [];
                apiModel.rpcServices = [];

                if (!version) {
                    var version = api.props.versions[api.props.versions.length - 1];
                }

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
                deferred.resolve(apiModel);
                self.currentApiModel = apiModel;
                self.currentApiModel.version = version;
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

module.factory('ApiAuthorizationRepository', ['$rootScope', '$q', '$http', 'apiBasePath', function ($rootScope, $q, $http, apiBasePath) {

    return {
        getApiAuthorization: function (name, version, force) {

            var apiAuthorizationsModel = [];
            var deferred = $q.defer();

            if (typeof version == 'string') {
                version = parseInt(version.match(/\d/g)[0]);
            }

            var hyperagentResource = new Hyperagent.Resource(apiBasePath + '/module/' + name + '/authorization?version=' + version);

            hyperagentResource.fetch({force: !!force}).then(function (authorizationData) {
                apiAuthorizationsModel = authorizationData.props;
                deferred.resolve(apiAuthorizationsModel);
            });

            return deferred.promise;

        },

        saveApiAuthorizations: function (apiName, apiAuthorizationsModel) {
            var url = apiBasePath + '/module/' + apiName + '/authorization';
            return $http.put(url, apiAuthorizationsModel);
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

module.factory(
  'AuthenticationRepository',
  ['$http', '$location', 'apiBasePath', function ($http, $location, apiBasePath) {

    var authenticationPath = apiBasePath + '/authentication';

    var resource = new Hyperagent.Resource(authenticationPath);

    resource.createAuthentication = function (options) {
      return $http.post(authenticationPath, options)
        .then(function (response) {
          return response.data;
        });
    };

    resource.updateAuthentication = function (data) {
      return $http({method: 'patch', url: authenticationPath, data: data})
        .then(function (response) {
          return response.data;
        });
    };

    resource.removeAuthentication = function () {
      return $http.delete(authenticationPath)
        .then(function (response) {
          return true;
        }, function (error) {
          console.log(error);
          return false;
        });
    };

    return resource;
  }]
);

module.run(['$rootScope', '$routeParams', '$q', function ($rootScope, $routeParams) {
    $rootScope.routeParams = $routeParams;

    $rootScope.$on('$routeChangeSuccess', function(scope, next, current){
        if (next.locals.api && scope.targetScope.$root.pageTitle != next.locals.api.name) {
            scope.targetScope.$root.pageTitle = next.locals.api.name;
        }
    });
}]);
