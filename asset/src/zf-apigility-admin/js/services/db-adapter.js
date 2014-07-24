(function(_) {
    'use strict';

angular.module('ag-admin').factory('DbAdapterResource', function ($http, $q, apiBasePath, Hal) {

    var dbAdapterApiPath = apiBasePath + '/db-adapter';
    var adapters;

    return {
        getList: function (force) {
            force = !!force;

            if (! force &&
                ((Array.isArray(adapters) && adapters.length > 0)  ||
                 typeof(adapters) === 'object')) {
                return $q.when(adapters);
            }

            var config = {
                method: 'GET',
                url: dbAdapterApiPath
            };
            return $http(config).then(
                function (response) {
                    adapters = Hal.pluckCollection('db_adapter', response.data);
                    adapters = Hal.props(adapters);
                    return adapters;
                }
            );
        },

        createNewAdapter: function (options) {
            return $http.post(dbAdapterApiPath, options).then(
                function (response) {
                    return Hal.props(response.data);
                }
            );
        },

        saveAdapter: function (name, data) {
            return $http({method: 'patch', url: dbAdapterApiPath + '/' + encodeURIComponent(name), data: data}).then(
                function (response) {
                    return Hal.props(response.data);
                }
            );
        },

        removeAdapter: function (name) {
            return $http.delete(dbAdapterApiPath + '/' + encodeURIComponent(name)).then(
                function (response) {
                    return true;
                }
            );
        }
    };
});

})(_);
