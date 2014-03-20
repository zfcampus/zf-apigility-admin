(function(_) {
    'use strict';

angular.module('ag-admin').controller(
  'ApiRpcServicesController', 
  function ($scope, $state, $stateParams, $sce, flash, filters, validators, selectors, ApiRepository, api, toggleSelection) {

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
        $scope.$broadcast('ag-form-submit-complete');
        $scope.$broadcast('ag-form-validation-errors-clear');
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
                $scope.$broadcast('ag-form-submit-complete');

                if (error.status !== 400 && error.status !== 422) {
                    /* generic, non-validation related error! */
                    flash.error = 'Error submitting new API';
                    return;
                }

                var validationErrors;

                if (error.status === 400) {
                    validationErrors = [ 'Unexpected or missing data processing form' ];
                } else {
                    validationErrors = error.data.validation_messages;
                }

                $scope.$broadcast('ag-form-validation-errors', validationErrors);
                flash.error = 'We were unable to validate your form; please check for errors.';
            }
        );
    };

    $scope.cancelEdit = function () {
        $state.go($state.$current.name, {edit: ''}, {reload: true, inherit: true});
    };

    $scope.startEdit = function () {
        $state.go($state.$current.name, {edit: true}, {notify: true, inherit: true});
    };

    $scope.saveRpcService = function (index) {
        var rpcServiceData = _.clone($scope.api.rpcServices[index]);
        ApiRepository.saveRpcService($scope.api.name, rpcServiceData)
            .then(function (data) {
                ApiRepository.refreshApi($scope, $state, true, 'RPC Service updated', function () {
                    $scope.cancelEdit();
                });
            });
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
