(function() {
    'use strict';

angular.module('ag-admin').controller(
    'SettingsDashboardController',
    function ($scope, $state, flash, SettingsDashboardRepository) {

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

        var fetch = function () {
            SettingsDashboardRepository.fetch().then(
                function (dashboard) {
                    $scope.dashboard = dashboard;

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
                },
                function (err) {
                    flash.error = 'Unable to fetch settings dashboard';
                }
            );
        };

        fetch();
    }
);

})();
