(function(_) {'use strict';

angular.module('ag-admin').factory('ApiRepository', function ($q, $http, apiBasePath, Hal, flash) {
    var moduleApiPath = apiBasePath + '/module';
    var apis;
    var apiModels = {};

    return {
        currentApiModel: null,

        getList: function (force) {
            force = !!force;

            if (! force &&
                ((Array.isArray(apis) && apis.length > 0)  ||
                 typeof(apis) === 'object')) {
                return $q.when(apis);
            }

            var apisModel = [];
            var config = {
                method: 'GET',
                url: moduleApiPath
            };

            return $http(config).then(
                function success(response) {
                    apis = Hal.pluckCollection('module', response.data);
                    apis = Hal.stripLinks(apis);
                    apis = Hal.stripEmbedded(apis);
                    return apis;
                }
            );
        },

        getApi: function (name, version, force) {
            force = !!force;

            if (typeof version == 'string') {
                version = parseInt(version.match(/\d/g)[0], 10);
            }

            if (!force && apiModels.hasOwnProperty(name) && apiModels[name].hasOwnProperty(version)) {
                return $q.when(apiModels[name][version]);
            }

            // localize this for future use
            var self = this;

            var deferred = $q.defer();
            var apiModel = {};
            var config = {
                method: 'GET',
                url: moduleApiPath
            };
            $http(config).then(
                function (response) {
                    var apis = Hal.pluckCollection('module', response.data);
                    var api = _.find(apis, function (m) {
                        return m.name === name;
                    });
                    
                    if (api === undefined) {
                        flash.error = 'API "' + name + '" not found';
                        return $q.reject(404);
                    }

                    _.forEach(Hal.stripLinks(api), function (value, key) {
                        apiModel[key] = value;
                    });

                    apiModel.restServices = [];
                    apiModel.rpcServices  = [];

                    if (!version) {
                        version = api.versions[api.versions.length - 1];
                    }

                    if (-1 === api.versions.indexOf(version)) {
                        flash.error = 'Version "' + version + '" of API "' + name + '" not found';
                        return $q.reject(404);
                    }

                    return api;
                }
            ).then(
                function (api) {
                    // Now load REST endpoints
                    var config = self.getHttpConfigFromLink('rest', api);
                    config.method = 'GET';
                    config.params.version = version;
                    return $http(config).then(
                        function (response) {
                            apiModel.restServices = Hal.pluckCollection('rest', response.data);
                            _.forEach(apiModel.restServices, function (restService, index) {
                                restService._self = Hal.getLink('self', restService);
                                restService.input_filter = [];
                                restService.documentation = [];
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
                        }
                    );
                }
            ).then(
                function (api) {
                    var config = self.getHttpConfigFromLink('rpc', api);
                    config.method = 'GET';
                    config.params.version = version;
                    return $http(config).then(
                        function (response) {
                            apiModel.rpcServices = Hal.pluckCollection('rpc', response.data);
                            _.forEach(apiModel.rpcServices, function (rpcService, index) {
                                rpcService._self = Hal.getLink('self', rpcService);
                                rpcService.input_filter = [];
                                rpcService.documentation = [];
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
                        }
                    );
                }
            ).then(
                function (api) {
                    deferred.resolve(apiModel);
                    if (!apiModels.hasOwnProperty(name)) {
                        apiModels[name] = {};
                    }
                    apiModels[name][version] = apiModel;
                    apiModels[name][version].version = version;
                }
            );

            return deferred.promise;
        },

        refreshApi: function (scope, force, message) {
            if (!scope.hasOwnProperty('api')) {
                console.error('Provided scope does not have an API property; cannot refresh API');
                return;
            }

            return this.getApi(scope.api.name, scope.api.version, true).then(
                function (api) {
                    if (message) {
                        flash.success = message;
                    }

                    scope.api = api;
                    scope.currentVersion = api.currentVersion;
                    return api;
                }
            );
        },

        createNewApi: function (name) {
            return $http.post(moduleApiPath, {name: name}).then(
                function (response) {
                    return response.data;
                }
            );
        },

        removeApi: function (name, recursive) {
            var deferred = $q.defer();
            var self = this;
            var config = {
                method: 'GET',
                url: moduleApiPath
            };
            return $http(config).then(
                function (response) {
                    var apis = Hal.pluckCollection('module', response.data);
                    var api = _.find(apis, function (m) {
                        return m.name === name;
                    });
                    
                    if (api === undefined) {
                        flash.error = 'API "' + name + '" not found';
                        return $q.reject(404);
                    }

                    return api;
                }
            ).then(
                function (api) {
                    var uri = Hal.getLink('self', api);
                    var config = {};
                    if ( !!recursive ) {
                        config.params = { recursive: 1 };
                    }

                    return $http.delete(uri, config).then(
                        function () {
                            return self.getList(true);
                        }
                    );
                }
            );
        },

        createNewRestService: function (apiName, restServiceName) {
            return $http.post(moduleApiPath + '/' + apiName + '/rest', {service_name: restServiceName}).then(
                function (response) {
                    return response.data;
                }
            );
        },

        createNewDbConnectedService: function(apiName, dbAdapterName, dbTableName) {
            return $http.post(moduleApiPath + '/' + apiName + '/rest', {adapter_name: dbAdapterName, table_name: dbTableName})
                .then(
                    function (response) {
                        return response.data;
                    }
                );
        },

        createNewRpcService: function (apiName, rpcServiceName, rpcServiceRoute) {
            return $http.post(moduleApiPath + '/' + apiName + '/rpc', {service_name: rpcServiceName, route_match: rpcServiceRoute})
                .then(
                    function (response) {
                        return response.data;
                    }
                );
        },

        removeRestService: function (apiName, restServiceName, recursive) {
            var url    = moduleApiPath + '/' + apiName + '/rest/' + encodeURIComponent(restServiceName);
            var config = {};
            if ( !!recursive ) {
                config.params = { recursive: 1 };
            }
            return $http.delete(url, config).then(
                function (response) {
                    return response.data;
                }
            );
        },

        saveRestService: function (apiName, restService) {
            var url = moduleApiPath + '/' + apiName + '/rest/' + encodeURIComponent(restService.controller_service_name);
            var testForEmpty = this.testForEmpty;
            var data = {
                accept_whitelist: restService.accept_whitelist,
                collection_class: testForEmpty(restService.collection_class),
                collection_http_methods: restService.collection_http_methods,
                collection_name: testForEmpty(restService.collection_name),
                collection_query_whitelist: restService.collection_query_whitelist,
                content_type_whitelist: restService.content_type_whitelist,
                entity_class: testForEmpty(restService.entity_class),
                entity_http_methods: restService.entity_http_methods,
                entity_identifier_name: testForEmpty(restService.entity_identifier_name),
                hydrator_name: testForEmpty(restService.hydrator_name),
                page_size: testForEmpty(restService.page_size),
                page_size_param: testForEmpty(restService.page_size_param),
                resource_class: testForEmpty(restService.resource_class),
                route_identifier_name: testForEmpty(restService.route_identifier_name),
                route_match: testForEmpty(restService.route_match),
                selector: restService.selector,
                service_name: restService.service_name
            };
            if (restService.hasOwnProperty('adapter_name') && restService.adapter_name) {
                data.adapter_name = restService.adapter_name;
            }
            if (restService.hasOwnProperty('table_name') && restService.table_name) {
                data.table_name = restService.table_name;
            }
            return $http({method: 'patch', url: url, data: data}).then(
                function (response) {
                    return response.data;
                }
            );
        },

        saveInputFilter: function (api, inputFilter) {
            var url = api._self + '/input-filter';
            return $http.put(url, inputFilter).then(
                function (response) {
                    return response.data;
                }
            );
        },

        saveDocumentation: function (api) {
            var url = api._self + '/doc';
            return $http.put(url, api.documentation).then(
                function (response) {
                    return response.data;
                }
            );
        },

        removeRpcService: function (apiName, rpcServiceName, recursive) {
            var url    = moduleApiPath + '/' + apiName + '/rpc/' + encodeURIComponent(rpcServiceName);
            var config = {};
            if ( !!recursive ) {
                config.params = { recursive: 1 };
            }
            return $http.delete(url, config).then(
                function (response) {
                    return response.data;
                }
            );
        },

        saveRpcService: function (apiName, rpcService) {
            var url = moduleApiPath + '/' + apiName + '/rpc/' + encodeURIComponent(rpcService.controller_service_name);
            var testForEmpty = this.testForEmpty;
            var data = {
                accept_whitelist: rpcService.accept_whitelist,
                content_type_whitelist: rpcService.content_type_whitelist,
                controller_class: testForEmpty(rpcService.controller_class),
                http_methods: rpcService.http_methods,
                route_match: testForEmpty(rpcService.route_match),
                selector: testForEmpty(rpcService.selector),
                service_name: rpcService.service_name
            };
            return $http({method: 'patch', url: url, data: data}).then(
                function (response) {
                    return response.data;
                }
            );
        },

        getSourceCode: function (apiName, className) {
            return $http.get(apiBasePath + '/source?module=' + apiName + '&class=' + className).then(
                function(response) {
                    return response.data;
                }
            );
        },

        createNewVersion: function (apiName) {
            return $http({method: 'patch', url: apiBasePath + '/versioning', data: {module: apiName}}).then(
                function (response) {
                    return response.data;
                }
            );
        },

        setDefaultApiVersion: function (apiName, defaultApiVersion) {
            return $http({method: 'patch', url: apiBasePath + '/default-version', data: {module: apiName, version: defaultApiVersion}})
                .then(
                    function (response) {
                        return response.data;
                    }
                );
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

            if (typeof data.type === 'string' &&
                data.type == 'Zend\\InputFilter\\FileInput') {
                data.file_upload = true;
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
        },

        testForEmpty: function (value) {
            if (value === '') {
                return null;
            }
            if (value === undefined) {
                return null;
            }
            return value;
        }
    };
});

})(_);
