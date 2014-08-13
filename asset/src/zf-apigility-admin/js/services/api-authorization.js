(function(_) {
    'use strict';

angular.module('ag-admin').factory('ApiAuthorizationRepository', function ($http, apiBasePath, Hal) {
    return {
        getApiAuthorization: function (name, version, force) {
            force = !!force;

            if (typeof version == 'string') {
                version = parseInt(version.match(/\d/g)[0], 10);
            }

            var config = {
                method: 'GET',
                url: apiBasePath + '/module/' + name + '/authorization',
                params: {
                    version: version
                },
                cache: !force
            };

            return $http(config).then(
                function (response) {
                    return Hal.props(response.data);
                }
            );
        },

        getServiceAuthorizations: function (service, moduleName, version) {
            return this.getApiAuthorization(moduleName, version).then(
                function (apiAuthorizations) {
                    var authorizations = {};
                    var complete = false;
                    var matches;
                    var controllerServiceName = service.controller_service_name.replace(/-/g, '\\');
                    controllerServiceName = controllerServiceName.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, '\\$&');
                    var serviceRegex = new RegExp('^' + controllerServiceName + '::(.*?)$');
                    var actionRegex  = new RegExp('^__([^_]+)__$');
                    _.forEach(apiAuthorizations, function (data, serviceName) {
                        if (complete) {
                            return;
                        }

                        matches = serviceRegex.exec(serviceName);
                        if (!Array.isArray(matches)) {
                            return;
                        }

                        var action = matches[1];
                        matches = actionRegex.exec(action);
                        if (Array.isArray(matches)) {
                            var type = matches[1];
                            if (type == 'resource') {
                                type = 'entity';
                            }
                            authorizations[type] = data;
                            return;
                        }

                        authorizations = data;
                        complete = true;
                    });

                    return authorizations;
                }
            );
        },

        saveApiAuthorizations: function (apiName, apiAuthorizationsModel) {
            var url = apiBasePath + '/module/' + apiName + '/authorization';
            return $http.put(url, apiAuthorizationsModel).then(
                function (response) {
                    return Hal.props(response.data);
                }
            );
        }
    };
});

})(_);
