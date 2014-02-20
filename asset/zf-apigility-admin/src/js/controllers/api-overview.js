(function() {
    'use strict';

angular.module('ag-admin').controller('ApiOverviewController', function ($rootScope, $scope, flash, api, ApiRepository) {
    $scope.api = api;
    $scope.defaultApiVersion = api.default_version;

    $scope.setDefaultApiVersion = function () {
        flash.info = 'Setting the default API version to ' + $scope.defaultApiVersion;
        ApiRepository.setDefaultApiVersion($scope.api.name, $scope.defaultApiVersion).then(function (data) {
            flash.success = 'Default API version updated';
            $scope.defaultApiVersion = data.version;
        });
    };
});

})();
