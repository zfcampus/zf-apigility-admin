(function(_) {
    'use strict';

angular.module('ag-admin').controller(
  'ApiRpcServicesController', 
  function ($scope, $state, $stateParams, $sce, flash, filters, validators, selectors, ApiRepository, api, toggleSelection, agFormHandler) {

    $scope.activeService    = $stateParams.service ? $stateParams.service : '';
    $scope.inEdit           = !!$stateParams.edit;
    $scope.view             = $stateParams.view ? $stateParams.view : 'settings';
    $scope.ApiRepository    = ApiRepository; // used in child controller (input filters)
    $scope.flash            = flash;
    $scope.api              = api;
    $scope.toggleSelection  = toggleSelection;
    $scope.filterOptions    = filters;
    $scope.validatorOptions = validators;
    $scope.selectors        = selectors;
    $scope.sourceCode       = [];
    $scope.deleteRpcService = false;

    $scope.resetForm = function () {
        agFormHandler.resetForm($scope);
        $scope.showNewRpcServiceForm = false;
        $scope.rpcServiceName = '';
        $scope.rpcServiceRoute = '';
    };

    $scope.isLatestVersion = function () {
        return $scope.ApiRepository.isLatestVersion($scope.api);
    };
    if (!$scope.isLatestVersion()) {
        $scope.inEdit = false;
        $state.go($state.$current.name, {edit: ''}, {reload: true});
    }


    $scope.createNewRpcService = function () {
        ApiRepository.createNewRpcService($scope.api.name, $scope.rpcServiceName, $scope.rpcServiceRoute).then(
            function (rpcResource) {
                $scope.addRpcService = false;
                $scope.resetForm();
                ApiRepository.refreshApi($scope, $state, true, 'New RPC Service created');
            },
            function (error) {
                agFormHandler.reportError(error, $scope);
            }
        );
    };

    $scope.cancelEdit = function () {
        $state.go($state.$current.name, {edit: null}, {reload: false, notify: false, inherit: true});
    };

    $scope.startEdit = function () {
        $state.go($state.$current.name, {edit: true}, {reload: false, notify: true, inherit: true});
    };

    $scope.saveRpcService = function (index) {
        var rpcServiceData = _.clone($scope.api.rpcServices[index]);
        ApiRepository.saveRpcService($scope.api.name, rpcServiceData).then(
            function (data) {
                agFormHandler.resetForm($scope);
                ApiRepository.refreshApi($scope, $state, true, 'RPC Service updated', function () {
                    $scope.cancelEdit();
                });
            },
            function (error) {
                agFormHandler.reportError(error, $scope);
            }
        );
    };

    $scope.removeRpcService = function (rpcServiceName) {
        ApiRepository.removeRpcService($scope.api.name, rpcServiceName)
            .then(function (data) {
                $scope.deleteRpcService = false;
                ApiRepository.refreshApi($scope, $state, true, 'RPC Service deleted');
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
});

})(_);
