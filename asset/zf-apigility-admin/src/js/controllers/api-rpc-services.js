(function(_) {'use strict';

angular.module('ag-admin').controller(
  'ApiRpcServicesController', 
  ['$http', '$rootScope', '$scope', '$timeout', '$sce', 'flash', 'filters', 'validators', 'selectors', 'ApiRepository', 'api', 'toggleSelection', 
  function ($http, $rootScope, $scope, $timeout, $sce, flash, filters, validators, selectors, ApiRepository, api, toggleSelection) {

    $scope.ApiRepository = ApiRepository; // used in child controller (input filters)
    $scope.flash = flash;

    $scope.api = api;

    $scope.toggleSelection = toggleSelection;

    $scope.filterOptions = filters;

    $scope.validatorOptions = validators;

    $scope.selectors = selectors;

    $scope.sourceCode = [];

    $scope.deleteRpcService = false;

    $scope.resetForm = function () {
        $scope.showNewRpcServiceForm = false;
        $scope.rpcServiceName = '';
        $scope.rpcServiceRoute = '';
    };

    $scope.isLatestVersion = function () {
        return $scope.ApiRepository.isLatestVersion($scope.api);
    };

    $scope.createNewRpcService = function () {
        ApiRepository.createNewRpcService($scope.api.name, $scope.rpcServiceName, $scope.rpcServiceRoute).then(function (rpcResource) {
            flash.success = 'New RPC Service created';
            $timeout(function () {
                ApiRepository.getApi($scope.api.name, $scope.api.version, true).then(function (api) {
                    $scope.api = api;
                    $scope.currentVersion = api.currentVersion;
                });
            }, 500);
            $scope.addRpcService = false;
            $scope.resetForm();
        });
    };

    $scope.saveRpcService = function (index) {
        var rpcServiceData = _.clone($scope.api.rpcServices[index]);
        ApiRepository.saveRpcService($scope.api.name, rpcServiceData).then(function (data) {
            flash.success = 'RPC Service updated';
        });
    };

    $scope.removeRpcService = function (rpcServiceName) {
        ApiRepository.removeRpcService($scope.api.name, rpcServiceName)
            .then(function (data) {
                flash.success = 'RPC Service deleted';
                $scope.deleteRpcService = false;
                $timeout(function () {
                    ApiRepository.getApi($scope.api.name, $scope.api.version, true).then(function (api) {
                        $scope.api = api;
                        $scope.currentVersion = api.currentVersion;
                    });
                }, 500);
            });
    };

    $scope.getSourceCode = function (className, classType) {
        ApiRepository.getSourceCode($scope.api.name, className)
            .then(function (data) {
                $scope.filename = className + '.php';
                $scope.classType = classType + ' Class';
                if (typeof data.source === 'string') {
                    $scope.sourceCode = $sce.trustAsHtml(data.source);
                } else {
                    $scope.sourceCode = '';
                }
            });
    };
}]);

})(_);
