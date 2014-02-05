(function(_) {'use strict';

angular.module('ag-admin').factory('ApiRepository', ['$q', '$http', 'apiBasePath', 'Hal', function ($q, $http, apiBasePath, Hal) {
    var moduleApiPath = apiBasePath + '/module';

    return {
        currentApiModel: null,

        getList: function (force) {
            force = !!force;
            var apisModel = [];
            var config = {
                method: 'GET',
                url: moduleApiPath,
                cache: !force
            };

            return $http(config).then(
                function success(response) {
                    var apis = Hal.pluckCollection('module', response.data);
                    apis = Hal.stripLinks(apis);
                    return Hal.stripEmbedded(apis);
                }
            );
        },

        getApi: function (name, version, force) {
            force = !!force;
            var apiModel = {};
            var deferred = $q.defer();

            // localize this for future use
            var self = this;

            if (typeof version == 'string') {
                version = parseInt(version.match(/\d/g)[0], 10);
            }

            if (!force && self.currentApiModel && version && self.currentApiModel.name == name && self.currentApiModel.version == version) {
                deferred.resolve(self.currentApiModel);
                return deferred.promise;
            }

            var config = {
                method: 'GET',
                url: moduleApiPath,
                cache: !force
            };
            $http(config).then(function (response) {
                var apis = Hal.pluckCollection('module', response.data);
                var api = _.find(apis, function (m) {
                    return m.name === name;
                });

                _.forEach(Hal.stripLinks(api), function (value, key) {
                    apiModel[key] = value;
                });

                apiModel.restServices = [];
                apiModel.rpcServices  = [];

                if (!version) {
                    version = api.versions[api.versions.length - 1];
                }

                return api;
            }).then(function (api) {
                // Now load REST endpoints
                var config = self.getHttpConfigFromLink('rest', api);
                config.method = 'GET';
                config.params.version = version;
                return $http(config).then(function (response) {
                    apiModel.restServices = Hal.pluckCollection('rest', response.data);
                    _.forEach(apiModel.restServices, function (restService, index) {
                        restService._self = Hal.getLink('self', restService);
                        restService.input_filter = {};
                        restService.documentation = {};
                        if (! restService._embedded) {
                            return;
                        }

                        if (restService._embedded && restService._embedded.input_filters && restService._embedded.input_filters[0]) {
                            restService.input_filter = Hal.props(restService._embedded.input_filters[0]);
                            _.forEach(restService.input_filter, function (value, key) {
                                self.marshalInputFilter(restService, value, key);
                            });
                            restService.input_filter = _.toArray(restService.input_filter);
                        }

                        if (restService._embedded.documentation) {
                            var documentation = Hal.pluckCollection('documentation', restService);
                            restService.documentation = Hal.props(documentation);
                        }
                    });
                    return api;
                });
            }).then(function (api) {
                var config = self.getHttpConfigFromLink('rpc', api);
                config.method = 'GET';
                config.params.version = version;
                return $http(config).then(function (response) {
                    apiModel.rpcServices = Hal.pluckCollection('rpc', response.data);
                    _.forEach(apiModel.rpcServices, function (rpcService, index) {
                        rpcService._self = Hal.getLink('self', rpcService);
                        rpcService.input_filter = {};
                        rpcService.documentation = {};
                        if (! rpcService._embedded) {
                            return;
                        }

                        if (rpcService._embedded.input_filters && rpcService._embedded.input_filters[0]) {
                            rpcService.input_filter = Hal.props(rpcService._embedded.input_filters[0]);
                            _.forEach(rpcService.input_filter, function (value, key) {
                                self.marshalInputFilter(rpcService, value, key);
                            });
                            rpcService.input_filter = _.toArray(rpcService.input_filter);
                        }

                        if (rpcService._embedded.documentation) {
                            var documentation = Hal.pluckCollection('documentation', rpcService);
                            rpcService.documentation = Hal.props(documentation);
                        }
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
            var url = moduleApiPath + '/' + apiName + '/rest/' + encodeURIComponent(restService.controller_service_name);
            return $http({method: 'patch', url: url, data: restService})
                .then(function (response) {
                    return response.data;
                });
        },

        saveInputFilter: function (api, inputFilter) {
            var url = api._self + '/input-filter';
            return $http.put(url, inputFilter);
        },

        saveDocumentation: function (api) {
            var url = api._self + '/doc';
            return $http.put(url, api.documentation);
        },

        removeRpcService: function (apiName, rpcServiceName) {
            var url = moduleApiPath + '/' + apiName + '/rpc/' + encodeURIComponent(rpcServiceName);
            return $http.delete(url)
                .then(function (response) {
                    return response.data;
                });
        },

        saveRpcService: function (apiName, rpcService) {
            var url = moduleApiPath + '/' + apiName + '/rpc/' + encodeURIComponent(rpcService.controller_service_name);
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
        },

        getLatestVersion: function (api) {
            var versions = api.versions;
            var latest = versions.pop();
            versions.push(latest);
            return latest;
        },

        isLatestVersion: function (api) {
            var latest = this.getLatestVersion(api);
            return (api.version === latest);
        },

        marshalInputFilter: function (service, data, key) {
            if (typeof data == 'string') {
                delete service.input_filter[key];
                return;
            }

            if (typeof data.validators == 'undefined') {
                data.validators = [];
            } else {
                _.forEach(data.validators, function (validator, index) {
                    if (typeof validator.options == 'undefined' || validator.options.length === 0) {
                        validator.options = {};
                    }
                });
            }

            if (typeof data.filters == 'undefined') {
                data.filters = [];
            } else {
                _.forEach(data.filters, function (filter, index) {
                    if (typeof filter.options == 'undefined' || filter.options.length === 0) {
                        filter.options = {};
                    }
                });
            }

            if (typeof data.required == 'undefined') {
                data.required = true;
            } else {
                data.required = !!data.required;
            }

            if (typeof data.allow_empty == 'undefined') {
                data.allow_empty = false;
            } else {
                data.allow_empty = !!data.allow_empty;
            }

            if (typeof data.continue_if_empty == 'undefined') {
                data.continue_if_empty = false;
            } else {
                data.continue_if_empty = !!data.continue_if_empty;
            }
        },

        getHttpConfigFromLink: function (rel, resource) {
            var config  = {
                uri: null,
                params: {}
            };

            var uri = Hal.getLink(rel, resource);

            // Remove templates
            uri = uri.replace(/\{[^}]+\}/, '', 'g');

            // Check for query string
            var matches = uri.match(/^([^?]+)\?(.*?)$/);
            if (!Array.isArray(matches)) {
                config.url = uri;
                return config;
            }

            // Split query string into key/value pairs
            config.url = matches[1];
            config.params = {};
            var paramPairs = matches[2].split('&');
            _.forEach(paramPairs, function (pair, index) {
                if (!pair.match(/\=/)) {
                    config.params[pair] = true;
                    return;
                }
                pair = pair.split('=', 2);
                config.params[pair[0]] = pair[1];
            });
            return config;
        }
    };
}]);

})(_);
