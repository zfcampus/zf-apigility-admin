'use strict';

var module = angular.module('ag-admin', ['ngRoute', 'ngSanitize', 'tags-input', 'angular-flash.service', 'angular-flash.flash-alert-directive']);

module.config(['$routeProvider', '$provide', function($routeProvider, $provide) {

    // setup the API Base Path (this should come from initial ui load/php)
    $provide.value('apiBasePath', angular.element('body').data('api-base-path') || '/admin/api');

    $routeProvider.when('/dashboard', {
        templateUrl: 'zf-apigility-admin/partials/index.html',
        controller: 'DashboardController'
    });
    $routeProvider.when('/global/db-adapters', {
        templateUrl: 'zf-apigility-admin/partials/global/db-adapters.html',
        controller: 'DbAdapterController'
    });
    $routeProvider.when('/global/authentication', {
        templateUrl: 'zf-apigility-admin/partials/global/authentication.html',
        controller: 'AuthenticationController'
    });
    $routeProvider.when('/api/:apiName/:version/overview', {
        templateUrl: 'zf-apigility-admin/partials/api/overview.html',
        controller: 'ApiOverviewController',
        resolve: {
            api: ['$route', 'ApiRepository', function ($route, ApiRepository) {
                return ApiRepository.getApi($route.current.params.apiName, $route.current.params.version);
            }]
        }
    });
    $routeProvider.when('/api/:apiName/:version/authorization', {
        templateUrl: 'zf-apigility-admin/partials/api/authorization.html',
        controller: 'ApiAuthorizationController',
        resolve: {
            api: ['$route', 'ApiRepository', function ($route, ApiRepository) {
                return ApiRepository.getApi($route.current.params.apiName, $route.current.params.version);
            }],
            apiAuthorizations: ['$route', 'ApiAuthorizationRepository', function ($route, ApiAuthorizationRepository) {
                return ApiAuthorizationRepository.getApiAuthorization($route.current.params.apiName, $route.current.params.version);
            }]
        }
    });
    $routeProvider.when('/api/:apiName/:version/rest-services', {
        templateUrl: 'zf-apigility-admin/partials/api/rest-services.html',
        controller: 'ApiRestServicesController',
        resolve: {
            dbAdapters: ['DbAdapterResource', function (DbAdapterResource) {
                return DbAdapterResource.getList();
            }],
            api: ['$route', 'ApiRepository', function ($route, ApiRepository) {
                return ApiRepository.getApi($route.current.params.apiName, $route.current.params.version);
            }]
        }
    });
    $routeProvider.when('/api/:apiName/:version/rpc-services', {
        templateUrl: 'zf-apigility-admin/partials/api/rpc-services.html',
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
    ['$rootScope', 'flash', function($rootScope, flash) {
        $rootScope.pageTitle = 'Dashboard';
        $rootScope.pageDescription = 'Global system configuration and configuration to be applied to all APIs.';
    }]
);

module.controller(
    'ApiListController',
    ['$rootScope', '$scope', '$location', '$timeout', 'flash', 'ApiRepository', function($rootScope, $scope, $location, $timeout, flash, ApiRepository) {

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

                flash.success = 'New API Created';
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
    ['$scope', '$location', 'flash', 'DbAdapterResource', function ($scope, $location, flash, DbAdapterResource) {
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
                flash.success = 'Database adapter created';
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
                flash.success = 'Database adapter ' + dbAdapter.adapter_name + ' updated';
                updateDbAdapters(true);
            });
        };

        $scope.removeDbAdapter = function (adapter_name) {
            DbAdapterResource.removeAdapter(adapter_name).then(function () {
                flash.success = 'Database adapter ' + adapter_name + ' removed';
                updateDbAdapters(true);
                $scope.deleteDbAdapter = false;
            });
        };

    }]
);

module.controller(
    'AuthenticationController',
    ['$scope', 'flash', 'AuthenticationRepository', function ($scope, flash, AuthenticationRepository) {

    $scope.showSetupButtons                 = false;
    $scope.showHttpBasicAuthenticationForm  = false;
    $scope.showHttpBasicAuthentication      = false;
    $scope.showHttpDigestAuthenticationForm = false;
    $scope.showHttpDigestAuthentication     = false;
    $scope.showOAuth2AuthenticationForm     = false;
    $scope.showOAuth2Authentication         = false;
    $scope.removeAuthenticationForm         = false;
    $scope.httpBasic                        = null;
    $scope.httpDigest                       = null;
    $scope.oauth2                           = null;

    var enableSetupButtons = function () {
        $scope.$apply(function () {
            $scope.showSetupButtons             = true;
            $scope.showHttpBasicAuthentication  = false;
            $scope.showHttpDigestAuthentication = false;
            $scope.showOAuth2Authentication     = false;
            $scope.removeAuthenticationForm     = false;
            $scope.httpBasic                    = null;
            $scope.httpDigest                   = null;
            $scope.oauth2                       = null;
        });
    };

    var fetchAuthenticationDetails = function (force) {
        AuthenticationRepository.fetch({force: true})
            .then(function (authentication) {
                var data = authentication.props;
                if (data.type == "http_basic") {
                    $scope.$apply(function () {
                        $scope.showSetupButtons             = false;
                        $scope.showHttpBasicAuthentication  = true;
                        $scope.showHttpDigestAuthentication = false;
                        $scope.showOAuth2Authentication     = false;
                        $scope.httpBasic                    = data;
                        $scope.httpDigest                   = null;
                        $scope.oauth2                       = null;
                    });
                } else if (data.type == "http_digest") {
                    $scope.$apply(function () {
                        $scope.showSetupButtons             = false;
                        $scope.showHttpDigestAuthentication = true;
                        $scope.showHttpBasicAuthentication  = false;
                        $scope.showOAuth2Authentication     = false;
                        data.digest_domains                 = data.digest_domains.split(" ");
                        $scope.httpDigest                   = data;
                        $scope.httpBasic                    = null;
                        $scope.oauth2                       = null;
                    });
                } else if (data.type == "oauth2") {
                    $scope.$apply(function () {
                        $scope.showSetupButtons             = false;
                        $scope.showOAuth2Authentication     = true;
                        $scope.showHttpDigestAuthentication = false;
                        $scope.showHttpBasicAuthentication  = false;
                        $scope.oauth2                       = data;
                        $scope.httpDigest                   = null;
                        $scope.httpBasic                    = null;
                    });
                } else {
                    enableSetupButtons();
                }
            }, function (err) {
                enableSetupButtons();
                return false;
            }
        );
    };

    var createAuthentication = function (options) {
        AuthenticationRepository.createAuthentication(options).then(function (authentication) {
            flash.success = 'Authentication created';
            fetchAuthenticationDetails(true);
            $scope.removeAuthenticationForm = false;
            $scope.resetForm();
        });
    };

    var updateAuthentication = function (options) {
        AuthenticationRepository.updateAuthentication(options).then(function (authentication) {
            flash.success = 'Authentication updated';
            fetchAuthenticationDetails(true);
        });
    };

    $scope.resetForm = function () {
        $scope.showHttpBasicAuthenticationForm  = false;
        $scope.showHttpDigestAuthenticationForm = false;
        $scope.showOAuth2AuthenticationForm     = false;
        $scope.digest_domains                   = '';
        $scope.dsn                              = '';
        $scope.htdigest                         = '';
        $scope.htpasswd                         = '';
        $scope.nonce_timeout                    = '';
        $scope.password                         = '';
        $scope.realm                            = '';
        $scope.route_match                      = '';
        $scope.username                         = '';
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
            realm          : $scope.realm,
            htpasswd       : $scope.htpasswd
        };
        createAuthentication(options);
    };

    $scope.createHttpDigestAuthentication = function () {
        var options = {
            accept_schemes : [ "digest" ],
            realm          : $scope.realm,
            htdigest       : $scope.htdigest,
            digest_domains : $scope.digest_domains.join(" "),
            nonce_timeout  : $scope.nonce_timeout
        };
        createAuthentication(options);
    };

    $scope.createOAuth2Authentication = function () {
        var options = {
            dsn         : $scope.dsn,
            username    : $scope.username,
            password    : $scope.password,
            route_match : $scope.route_match
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

    $scope.updateHttpDigestAuthentication = function () {
        var options = {
            realm          : $scope.httpDigest.realm,
            htdigest       : $scope.httpDigest.htdigest,
            digest_domains : $scope.httpDigest.digest_domains.join(" "),
            nonce_timeout  : $scope.httpDigest.nonce_timeout
        };
        updateAuthentication(options);
    };

    $scope.updateOAuth2Authentication = function () {
        var options = {
            dsn         : $scope.oauth2.dsn,
            username    : $scope.oauth2.username,
            password    : $scope.oauth2.password,
            route_match : $scope.oauth2.route_match
        };
        updateAuthentication(options);
    };

    $scope.removeAuthentication = function () {
        AuthenticationRepository.removeAuthentication()
            .then(function (response) {
                flash.success = 'Authentication removed';
                fetchAuthenticationDetails(true);
            });
    };

    fetchAuthenticationDetails(true);
}]);

module.controller('ApiOverviewController', ['$http', '$rootScope', '$scope', 'flash', 'api', 'ApiRepository', function ($http, $rootScope, $scope, flash, api, ApiRepository) {
    $scope.api = api;
    $scope.defaultApiVersion = api.default_version;
    $scope.setDefaultApiVersion = function () {
        flash.info = 'Setting the default API version to ' + $scope.defaultApiVersion;
        ApiRepository.setDefaultApiVersion($scope.api.name, $scope.defaultApiVersion).then(function (data) {
            flash.success = 'Default API version updated';
            $scope.defaultApiVersion = data.version;
        });
    };
}]);

module.controller(
    'ApiAuthorizationController',
    ['$http', '$rootScope', '$scope', '$routeParams', 'flash', 'api', 'apiAuthorizations', 'ApiAuthorizationRepository', function ($http, $rootScope, $scope, $routeParams, flash, api, apiAuthorizations, ApiAuthorizationRepository) {
        $scope.apiAuthorizations = apiAuthorizations;

        var version = $routeParams.version.match(/\d/g)[0] || 1;
        $scope.editable = (version == api.versions[api.versions.length - 1]);

        $scope.saveAuthorization = function () {
            flash.success = 'Authorization settings saved';
            ApiAuthorizationRepository.saveApiAuthorizations($routeParams.apiName, $scope.apiAuthorizations);
        };

        $scope.updateColumn = function ($event, column) {
            _.forEach($scope.apiAuthorizations, function (item, name) {
                $scope.apiAuthorizations[name][column] = $event.target.checked;
            });
        };

        $scope.updateRow = function ($event, name) {
            _.forEach(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], function (method) {
                $scope.apiAuthorizations[name][method] = $event.target.checked;
            });
        };

        $scope.showTopSaveButton = function () {
            return (Object.keys(apiAuthorizations).length > 10);
        };
    }]
);

module.controller('ApiRestServicesController', ['$http', '$rootScope', '$scope', '$timeout', '$sce', 'flash', 'HydratorServicesRepository', 'ValidatorsServicesRepository', 'ApiRepository', 'api', 'dbAdapters', function ($http, $rootScope, $scope, $timeout, $sce, flash, HydratorServicesRepository, ValidatorsServicesRepository, ApiRepository, api, dbAdapters) {

    $scope.api = api;

    $scope.dbAdapters = dbAdapters;

    $scope.contentNegotiation = ['HalJson', 'Json']; // @todo refactor to provider/factory

    $scope.hydrators = [];

    $scope.validators = [];

    $scope.sourceCode = [];

    (function () {
        HydratorServicesRepository.getList().then(function(response) {
            $scope.hydrators = response.data.hydrators;
        });
    })();

    (function () {
        ValidatorsServicesRepository.getList().then(function(response) {
            $scope.validators = response.data.validators;
        });
    })();

    $scope.toggleSelection = function (model, $event) {
        var element = $event.target;
        (element.checked) ? model.push(element.value) : model.splice(model.indexOf(element.value), 1);
    };

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
            flash.success = 'New REST Service created';
            $timeout(function () {
                ApiRepository.getApi($scope.api.name, $scope.api.version, true).then(function (api) {
                    $scope.api = api;
                    $scope.currentVersion = api.currentVersion;
                });
            }, 500);
            $scope.showNewRestServiceForm = false;
            $scope.restServiceName = '';
        });
    };

    $scope.createNewDbConnectedService = function () {
        ApiRepository.createNewDbConnectedService($scope.api.name, $scope.dbAdapterName, $scope.dbTableName).then(function (restResource) {
            flash.success = 'New DB Connected Service created';
            $timeout(function () {
                ApiRepository.getApi($scope.api.name, $scope.api.version, true).then(function (api) {
                    $scope.api = api;
                });
            }, 500);
            $scope.showNewRestServiceForm = false;
            $scope.dbAdapterName = '';
            $scope.dbTableName = '';
        });
    };

    $scope.saveRestService = function (index) {
        var restServiceData = _.clone($scope.api.restServices[index]);
        ApiRepository.saveRestService($scope.api.name, restServiceData).then(function (data) {
            flash.success = 'REST Service updated';
        });
    };

    $scope.removeRestService = function (restServiceName) {
        ApiRepository.removeRestService($scope.api.name, restServiceName).then(function (data) {
            $scope.deleteRestService = false;
        });
    };

    $scope.getSourceCode = function (className, classType) {
        ApiRepository.getSourceCode ($scope.api.name, className)
            .then(function (data) {
                $scope.filename = className + '.php';
                $scope.classType = classType + ' Class';
                $scope.sourceCode = $sce.trustAsHtml(data.source);
            });
    };
}]);

module.controller('ApiRpcServicesController', ['$http', '$rootScope', '$scope', '$timeout', 'flash', 'ApiRepository', 'api', function ($http, $rootScope, $scope, $timeout, flash, ApiRepository, api) {

    $scope.api = api;

    $scope.contentNegotiation = ['HalJson', 'Json']; // @todo refactor to provider/factory

    $scope.resetForm = function () {
        $scope.showNewRpcServiceForm = false;
        $scope.rpcServiceName = '';
        $scope.rpcServiceRoute = '';
    };

    $scope.createNewRpcService = function () {

        ApiRepository.createNewRpcService($scope.api.name, $scope.rpcServiceName, $scope.rpcServiceRoute).then(function (rpcResource) {
            flash.success = 'New RPC Service created';
            $timeout(function () {
                ApiRepository.getApi($scope.api.name, $scope.api.version, true).then(function (api) {
                    $scope.api = api;
                });
            }, 500);
            $scope.addRpcService = false;
            $scope.resetForm();
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
    ['$rootScope', '$scope', '$location', '$timeout', '$routeParams', 'flash', 'ApiRepository', function($rootScope, $scope, $location, $timeout, $routeParams, flash, ApiRepository) {

        ApiRepository.getApi($routeParams.apiName, $routeParams.version).then(function (api) {
            $scope.api = api;
            $scope.currentVersion = api.version;
            $scope.defaultApiVersion = api.default_version;
        });

        $scope.createNewApiVersion = function () {
            ApiRepository.createNewVersion($scope.api.name).then(function (data) {
                flash.success = 'A new version of this API was created';
                $rootScope.$broadcast('refreshApiList');
                $timeout(function () {
                    $location.path('/api/' + $scope.api.name + '/v' + data.version + '/overview');
                }, 500);
            });
        };

        $scope.setDefaultApiVersion = function () {
            flash.info = 'Setting the default API version to ' + $scope.defaultApiVersion;
            ApiRepository.setDefaultApiVersion($scope.api.name, $scope.defaultApiVersion).then(function (data) {
                flash.success = 'Default API version updated';
                $scope.defaultApiVersion = data.version;
            });
        };

        $scope.changeVersion = function () {
            var curPath = $location.path();
            var lastSegment = curPath.substr(curPath.lastIndexOf('/') + 1);
            $timeout(function () {
                $location.path('/api/' + $scope.api.name + '/v' + $scope.currentVersion + '/' + lastSegment);
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
            return $http.post(moduleApiPath + '/' + apiName + '/rest', {adapter_name: dbAdapterName, table_name: dbTableName})
                .then(function (response) {
                    return response.data;
                });
        },

        createNewRpcService: function (apiName, rpcServiceName, rpcServiceRoute) {
            return $http.post(moduleApiPath + '/' + apiName + '/rpc', {service_name: rpcServiceName, route: rpcServiceRoute})
                .then(function (response) {
                    return response.data;
                });
        },

        removeRestService: function (apiName, restServiceName) {
            var url = moduleApiPath + '/' + apiName + '/rest/' + encodeURIComponent(restServiceName);
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
            var url = moduleApiPath + '/' + apiName + '/rpc/' + encodeURIComponent(rpcServiceName);
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
        },

        setDefaultApiVersion: function (apiName, defaultApiVersion) {
            return $http({method: 'patch', url: '/admin/api/default-version', data: {module: apiName, version: defaultApiVersion}})
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

module.factory('DbAdapterResource', ['$http', '$q', '$location', 'apiBasePath', function ($http, $q, $location, apiBasePath) {

    var dbAdapterApiPath = apiBasePath + '/db-adapter';

    var resource =  new Hyperagent.Resource(dbAdapterApiPath);

    resource.getList = function () {
        var deferred = $q.defer();

        this.fetch().then(function (adapters) {
            var dbAdapters = _.pluck(adapters.embedded.db_adapter, 'props');
            deferred.resolve(dbAdapters);
        });

        return deferred.promise;
    };

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
    'HydratorServicesRepository',
    ['$http', 'flash', 'apiBasePath', function ($http, flash, apiBasePath) {
        var servicePath = apiBasePath + '/hydrators';

        return {
            getList: function () {
                return $http({method: 'GET', url: servicePath}).
                    error(function(data, status, headers, config) {
                        flash.error = 'Unable to fetch hydrators for hydrator dropdown; you may need to reload the page';
                    });
            }
        };
    }]
);

module.factory(
    'ValidatorsServicesRepository',
    ['$http', 'flash', 'apiBasePath', function ($http, flash, apiBasePath) {
        var servicePath = apiBasePath + '/validators';

        return {
            getList: function () {
                return $http({method: 'GET', url: servicePath}).
                    error(function(data, status, headers, config) {
                        flash.error = 'Unable to fetch validators for hydrator dropdown; you may need to reload the page';
                    });
            }
        };
    }]
);

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
                return false;
            });
        };

        return resource;
    }]
);

// @todo refactor the naming of this at some point
module.filter('servicename', function () {
    return function (input) {
        var parts = input.split('::');
        var newServiceName = parts[0] + ' (';
        switch (parts[1]) {
            case '__collection__': newServiceName += 'Collection)'; break;
            case '__resource__': newServiceName += 'Entity)'; break;
            default: newServiceName += parts[1] + ")"; break;
        }
        return newServiceName;
    }
});

module.run(['$rootScope', '$routeParams', '$location', function ($rootScope, $routeParams, $location) {
    $rootScope.routeParams = $routeParams;

    $rootScope.$on('$routeChangeSuccess', function(scope, next, current){
        if (next.locals.api && scope.targetScope.$root.pageTitle != next.locals.api.name) {
            scope.targetScope.$root.pageTitle = next.locals.api.name;
        }
    });
}]);
