(function() {'use strict';

angular.module('ag-admin').factory(
    'ValidatorsServicesRepository',
    function ($http, flash, apiBasePath) {
        var servicePath = apiBasePath + '/validators';

        return {
            getList: function () {
                var promise = $http({method: 'GET', url: servicePath}).then(
                    function (response) {
                        return response.data.validators;
                    }
                ).catch(
                    function () {
                        flash.error = 'Unable to fetch validators for validator dropdown; you may need to reload the page';
                        return false;
                    }
                );
                return promise;
            }
        };
    }
);

})();
