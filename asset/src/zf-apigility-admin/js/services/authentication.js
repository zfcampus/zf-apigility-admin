(function() {
    'use strict';

angular.module('ag-admin').factory(
    'AuthenticationRepository',
    function ($http, $q, apiBasePath) {

        var authenticationPath = apiBasePath + '/authentication';

        return {
            hasAuthentication: function() {
                return this.fetch({cache: false}).then(
                    function (response) {
                        var configured = true;
                        if (response === '') {
                            configured = false;
                        }
                        return { configured: configured };
                    }
                ).catch(
                    function (response) {
                        return { configured: false };
                    }
                );
            },
            fetch: function(options) {
                return $http.get(authenticationPath, options).then(
                    function (response) {
                        return response.data;
                    }
                );
            },
            createAuthentication: function (type, options) {
                return $http.post(authenticationPath + '/' + type, options).then(
                    function (response) {
                        return response.data;
                    }
                );
            },
            updateAuthentication: function (type, data) {
                return $http({method: 'patch', url: authenticationPath + '/' + type, data: data}).then(
                    function (response) {
                        return response.data;
                    }
                );
            },
            removeAuthentication: function (type) {
                return $http.delete(authenticationPath + '/' + type).then(
                    function (response) {
                        return true;
                    }
                ).catch(
                    function (error) {
                        return false;
                    }
                );
            }
        };
    }
);

})();
