(function() {'use strict';

angular.module('ag-admin').factory(
    'AuthenticationRepository',
    ['$http', '$q', 'apiBasePath', function ($http, $q, apiBasePath) {

        var authenticationPath = apiBasePath + '/authentication';

        return {
            hasAuthentication: function() {
                return this.fetch({cache: false}).then(
                    function success(response) {
                        var configured = true;
                        if (response === '') {
                            configured = false;
                        }
                        return { configured: configured };
                    },
                    function error(response) {
                        return { configured: false };
                    }
                );
            },
            fetch: function(options) {
                return $http.get(authenticationPath, options)
                    .then(function (response) {
                        var data = response.data;
                        if (data.hasOwnProperty('digest_domains') && typeof data.digest_domains === 'string') {
                            data.digest_domains = data.digest_domains.split(' ');
                        }
                        return data;
                    });
            },
            createAuthentication: function (options) {
                return $http.post(authenticationPath, options)
                    .then(function (response) {
                        return response.data;
                    });
            },
            updateAuthentication: function (data) {
                return $http({method: 'patch', url: authenticationPath, data: data})
                    .then(function (response) {
                        return response.data;
                    });
            },
            removeAuthentication: function () {
                return $http.delete(authenticationPath)
                    .then(function (response) {
                    return true;
                }, function (error) {
                    return false;
                });
            }
        };
    }]
);

})();
