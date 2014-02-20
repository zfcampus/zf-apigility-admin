(function() {'use strict';

angular.module('ag-admin').controller(
    'ApiVersionController',
    function($rootScope, $scope, $location, $timeout, $stateParams, flash, ApiRepository) {

        ApiRepository.getApi($stateParams.apiName, $stateParams.version).then(function (api) {
            $scope.api = api;
            $scope.currentVersion = api.version;
            $scope.defaultApiVersion = api.default_version;
        });

        $scope.createNewApiVersion = function () {
            ApiRepository.createNewVersion($scope.api.name).then(function (data) {
                flash.success = 'A new version of this API was created';
                $rootScope.$broadcast('refreshApiList');
                $timeout(function () {
                    $location.path('/api/' + $scope.api.name + '/v' + data.version + '/overview');
                }, 500);
            });
        };

        $scope.setDefaultApiVersion = function () {
            flash.info = 'Setting the default API version to ' + $scope.defaultApiVersion;
            ApiRepository.setDefaultApiVersion($scope.api.name, $scope.defaultApiVersion).then(function (data) {
                flash.success = 'Default API version updated';
                $scope.defaultApiVersion = data.version;
            });
        };

        $scope.changeVersion = function () {
            var curPath = $location.path();
            var lastSegment = curPath.substr(curPath.lastIndexOf('/') + 1);
            $timeout(function () {
                $location.path('/api/' + $scope.api.name + '/v' + $scope.currentVersion + '/' + lastSegment);
            }, 500);
        };
    }
);

})();
