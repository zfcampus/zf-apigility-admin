(function(_, Hyperagent) {'use strict';

angular.module('ag-admin').factory('ApiRepository', ['$rootScope', '$q', '$http', 'apiBasePath', function ($rootScope, $q, $http, apiBasePath) {
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
                version = parseInt(version.match(/\d/g)[0], 10);
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
                    version = api.props.versions[api.props.versions.length - 1];
                }

                return api;
            }).then(function (api) {
                // now load REST + RPC endpoints
                return api.link('rest', {version: version}).fetch().then(function (restServices) {

                    _.forEach(restServices.embedded.rest, function (restService, index) {
                        var length = 0;
                        length = apiModel.restServices.push(restService.props);
                        apiModel.restServices[length - 1]._self = restService.links.self.href;
                        if (restService.embedded.input_filters && restService.embedded.input_filters[0]) {
                            apiModel.restServices[length - 1].input_filter = restService.embedded.input_filters[0].props;
                            _.forEach(apiModel.restServices[length-1].input_filter, function (value, key) {
                                if (typeof value == 'string') {
                                    delete apiModel.restServices[length-1].input_filter[key];
                                } else {
                                    if (typeof value.validators == 'undefined') {
                                        value.validators = [];
                                    } else {
                                        _.forEach(value.validators, function (validator, index) {
                                            if (typeof validator.options == 'undefined' || validator.options.length === 0) {
                                                validator.options = {};
                                            }
                                        });
                                    }

                                    if (typeof value.filters == 'undefined') {
                                        value.filters = [];
                                    } else {
                                        _.forEach(value.filters, function (filter, index) {
                                            if (typeof filter.options == 'undefined' || filter.options.length === 0) {
                                                filter.options = {};
                                            }
                                        });
                                    }

                                    if (typeof value.required == 'undefined') {
                                        value.required = true;
                                    } else {
                                        value.required = !!value.required;
                                    }
                                }

                            });
                            // convert to array
                            apiModel.restServices[length - 1].input_filter = _.toArray(apiModel.restServices[length - 1].input_filter);
                        } else {
                            apiModel.restServices[length - 1].input_filter = [];
                        }

                    });

                    return api;
                });
            }).then(function (api) {
                // now load REST + RPC endpoints
                return api.link('rpc', {version: version}).fetch().then(function (rpcServices) {


                    _.forEach(rpcServices.embedded.rpc, function (rpcService, index) {
                        var length = 0;
                        length = apiModel.rpcServices.push(rpcService.props);
                        apiModel.rpcServices[length - 1]._self = rpcService.links.self.href;
                        if (rpcService.embedded.input_filters && rpcService.embedded.input_filters[0]) {
                            apiModel.rpcServices[length - 1].input_filter = rpcService.embedded.input_filters[0].props;
                            _.forEach(apiModel.rpcServices[length-1].input_filter, function (value, key) {
                                if (typeof value == 'string') {
                                    delete apiModel.rpcServices[length-1].input_filter[key];
                                } else {
                                    if (typeof value.validators == 'undefined') {
                                        value.validators = [];
                                    } else {
                                        _.forEach(value.validators, function (validator, index) {
                                            if (typeof validator.options == 'undefined' || validator.options.length === 0) {
                                                validator.options = {};
                                            }
                                        });
                                    }
                                }

                            });
                            // convert to array
                            apiModel.rpcServices[length - 1].input_filter = _.toArray(apiModel.rpcServices[length - 1].input_filter);
                        } else {
                            apiModel.rpcServices[length - 1].input_filter = [];
                        }

                    });

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
            var url = api._self + '/inputfilter';
            return $http.put(url, inputFilter);
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
        }
    };
}]);

})(_, Hyperagent);
