(function() {
    'use strict';

angular.module('ag-admin').controller(
    'ApiOverviewController',
    function ($scope, $state, $timeout, flash, ApiRepository) {
    $scope.api = {};
    $scope.defaultApiVersion = 1;
    $scope.deleteApiPanelIsCollapsed = true;

    var updateApi = function (api) {
        $scope.api = api;
        $scope.defaultApiVersion = api.default_version;
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

    $scope.$on('api.version.update', function () {
        ApiRepository.getApi($state.params.apiName, $state.params.version, true).then(
            function (api) {
                updateApi(api);
            }
        );
    });

    $scope.removeApi = function (recursive) {
        var name = $state.params.apiName;
        ApiRepository.removeApi($state.params.apiName, !!recursive).then(
            function () {
                flash.success = 'API "' + name + '" is being deleted... Please wait';
                $timeout(function () {
                    $state.go('^');
                }, 2000);
            }
        );
    };

    ApiRepository.getApi($state.params.apiName, $state.params.version).then(
        function (api) {
            updateApi(api);
        }
    );
});

})();
