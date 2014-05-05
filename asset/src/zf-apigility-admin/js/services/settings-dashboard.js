(function() {
    'use strict';

angular.module('ag-admin').factory('SettingsDashboardRepository', function ($http, apiBasePath, Hal) {
    return {
        fetch: function () {
            var config = {
                method: 'GET',
                url: apiBasePath + '/settings-dashboard'
            };

            return $http(config).then(
                function success(response) {
                    var data = response.data;

                    var authentication;
                    if (data.hasOwnProperty('authentication')) {
                        authentication = false;
                    } else {
                        authentication = Hal.pluckCollection('authentication', data);
                    }

                    return {
                        authentication: authentication,
                        contentNegotiation: Hal.pluckCollection('content_negotiation', data),
                        dbAdapters: Hal.pluckCollection('db_adapter', data)
                    };
                }
            );
        }
    };
});

})();
