(function() {'use strict';

angular.module('ag-admin').controller(
    'ApiVersionController',
    function($scope, $state, $stateParams, flash, ApiRepository) {

        ApiRepository.getApi($stateParams.apiName, $stateParams.version).then(
            function (api) {
                $scope.api = api;
                $scope.currentVersion = api.version;
                $scope.defaultApiVersion = api.default_version;
            }
        );

        $scope.createNewApiVersion = function () {
            ApiRepository.createNewVersion($scope.api.name).then(
                function (data) {
                    flash.success = 'A new version of this API was created';
                    $state.go('ag.api.version', {apiName: $scope.api.name, version: data.version});
                }
            );
        };

        $scope.setDefaultApiVersion = function () {
            flash.info = 'Setting the default API version to ' + $scope.defaultApiVersion;
            ApiRepository.setDefaultApiVersion($scope.api.name, $scope.defaultApiVersion).then(
                function (data) {
                    flash.success = 'Default API version updated';
                    $scope.defaultApiVersion = data.version;
                }
            );
        };

        $scope.changeVersion = function () {
            $state.go($state.$current, {version: $scope.currentVersion});
        };
    }
);

})();
