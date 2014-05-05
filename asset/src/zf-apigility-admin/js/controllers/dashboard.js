(function() {
    'use strict';

angular.module('ag-admin').controller(
    'DashboardController',
    function ($scope, $state, dashboard) {
        if (dashboard.authentication) {
            switch (dashboard.authentication.type) {
                case 'http_basic':
                    $scope.httpBasic = dashboard.authentication;
                    break;
                case 'http_digest':
                    $scope.httpDigest = dashboard.authentication;
                    break;
                case 'oauth2':
                    $scope.oauth2 = dashboard.authentication;
                    break;
            }
        }

        angular.forEach(dashboard.modules, function (module) {
            module.latestVersion = module.versions.pop();
        });

        $scope.dashboard = dashboard;

        $scope.isHttpBasicAuthentication = function (authentication) {
            if (!authentication) {
                return false;
            }
            return (authentication.type == 'http_basic');
        };

        $scope.isHttpDigestAuthentication = function (authentication) {
            if (!authentication) {
                return false;
            }
            return (authentication.type == 'http_digest');
        };

        $scope.isOAuth2 = function (authentication) {
            if (!authentication) {
                return false;
            }
            return (authentication.type == 'oauth2');
        };
    }
);

})();

