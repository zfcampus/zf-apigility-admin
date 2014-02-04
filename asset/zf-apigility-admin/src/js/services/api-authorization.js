(function(Hyperagent) {'use strict';

angular.module('ag-admin').factory('ApiAuthorizationRepository', function ($rootScope, $q, $http, apiBasePath) {

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

        saveApiAuthorizations: function (apiName, apiAuthorizationsModel) {
            var url = apiBasePath + '/module/' + apiName + '/authorization';
            return $http.put(url, apiAuthorizationsModel);
        }
    };
});

})(Hyperagent);
