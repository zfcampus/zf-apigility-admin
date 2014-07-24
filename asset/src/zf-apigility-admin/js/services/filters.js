(function() {
    'use strict';

angular.module('ag-admin').factory(
    'FiltersServicesRepository',
    function ($http, flash, apiBasePath) {
        var servicePath = apiBasePath + '/filters';

        return {
            getList: function () {
                var promise = $http({method: 'GET', url: servicePath}).then(
                    function (response) {
                        return response.data.filters;
                    }
                ).catch(
                    function () {
                        flash.error = 'Unable to fetch filters for filter dropdown; you may need to reload the page';
                        return false;
                    }
                );
                return promise;
            }
        };
    }
);

})();
