(function(_, Hyperagent) {'use strict';

angular.module('ag-admin').factory('DbAdapterResource', ['$http', '$q', '$location', 'apiBasePath', function ($http, $q, $location, apiBasePath) {

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

})(_, Hyperagent);
