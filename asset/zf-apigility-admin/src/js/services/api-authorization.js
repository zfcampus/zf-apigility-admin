(function(_, Hyperagent) {'use strict';

angular.module('ag-admin').factory('ApiAuthorizationRepository', ['$rootScope', '$q', '$http', 'apiBasePath', function ($rootScope, $q, $http, apiBasePath) {

    return {
        getApiAuthorization: function (name, version, force) {
            var apiAuthorizationsModel = [];
            var deferred = $q.defer();

            if (typeof version == 'string') {
                version = parseInt(version.match(/\d/g)[0], 10);
            }

            var hyperagentResource = new Hyperagent.Resource(apiBasePath + '/module/' + name + '/authorization?version=' + version);

            hyperagentResource.fetch({force: !!force}).then(function (authorizationData) {
                apiAuthorizationsModel = authorizationData.props;
                deferred.resolve(apiAuthorizationsModel);
            });

            return deferred.promise;
        },

        getServiceAuthorizations: function (service, moduleName, version) {
            return this.getApiAuthorization(moduleName, version).then(function (apiAuthorizations) {
                var authorizations = {};
                var complete = false;
                var matches;
                var controllerServiceName = service.controller_service_name;
                controllerServiceName = controllerServiceName.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
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
            });
        },

        saveApiAuthorizations: function (apiName, apiAuthorizationsModel) {
            var url = apiBasePath + '/module/' + apiName + '/authorization';
            return $http.put(url, apiAuthorizationsModel);
        }
    };
}]);

})(_, Hyperagent);
