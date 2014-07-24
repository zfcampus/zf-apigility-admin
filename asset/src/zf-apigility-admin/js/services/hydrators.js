(function() {
    'use strict';

angular.module('ag-admin').factory(
    'HydratorServicesRepository',
    function ($http, flash, apiBasePath) {
        var servicePath = apiBasePath + '/hydrators';

        return {
            getList: function () {
                var promise = $http({method: 'GET', url: servicePath}).then(
                    function (response) {
                        return response.data.hydrators;
                    }
                ).catch(
                    function () {
                        flash.error = 'Unable to fetch hydrators for hydrator dropdown; you may need to reload the page';
                    }
                );
                return promise;
            }
        };
    }
);

})();
