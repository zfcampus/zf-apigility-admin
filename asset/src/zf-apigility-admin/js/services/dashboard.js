(function() {
    'use strict';

angular.module('ag-admin').factory('DashboardRepository', function ($http, apiBasePath, Hal) {
    return {
        fetch: function () {
            var config = {
                method: 'GET',
                url: apiBasePath + '/dashboard'
            };

            return $http(config).then(
                function (response) {
                    var data = response.data;

                    var authentication;
                    if (data.hasOwnProperty('authentication')) {
                        authentication = false;
                    } else {
                        authentication = Hal.pluckCollection('authentication', data);
                    }

                    return {
                        authentication: authentication,
                        dbAdapters: Hal.pluckCollection('db_adapter', data),
                        modules: Hal.pluckCollection('module', data)
                    };
                }
            );
        }
    };
});

})();
