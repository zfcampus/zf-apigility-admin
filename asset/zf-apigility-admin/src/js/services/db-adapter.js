(function(_) {'use strict';

angular.module('ag-admin').factory('DbAdapterResource', ['$http', 'apiBasePath', 'Hal', function ($http, apiBasePath, Hal) {

    var dbAdapterApiPath = apiBasePath + '/db-adapter';

    return {
        getList: function (force) {
            force = !!force;
            var config = {
                method: 'GET',
                url: dbAdapterApiPath,
                cache: !force
            };
            return $http(config).then(
                function success(response) {
                    var dbAdapters = Hal.pluckCollection('db_adapter', response.data);
                    return Hal.props(dbAdapters);
                }
            );
        },

        createNewAdapter: function (options) {
            return $http.post(dbAdapterApiPath, options)
                .then(function (response) {
                    return Hal.props(response.data);
                });
        },

        saveAdapter: function (name, data) {
            return $http({method: 'patch', url: dbAdapterApiPath + '/' + encodeURIComponent(name), data: data})
                .then(function (response) {
                    return Hal.props(response.data);
                });
        },

        removeAdapter: function (name) {
            return $http.delete(dbAdapterApiPath + '/' + encodeURIComponent(name))
                .then(function (response) {
                    return true;
                });
        }
    };
}]);

})(_);
