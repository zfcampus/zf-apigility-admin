(function(_) {
    'use strict';

angular.module('ag-admin').controller(
  'ApiRpcServicesController', 
  function ($scope, $state, $stateParams, $sce, $modal, $timeout, flash, filters, validators, selectors, ApiRepository, api, toggleSelection, agFormHandler) {

    $scope.activeService    = $stateParams.service ? $stateParams.service : '';
    $scope.inEdit           = !!$stateParams.edit;
    $scope.view             = $stateParams.view ? $stateParams.view : 'settings';
    $scope.ApiRepository    = ApiRepository; // used in child controller (input filters)
    $scope.flash            = flash;
    $scope.api              = api;
    $scope.version          = $stateParams.version;
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

    $scope.toggleEditState = function (service, flag) {
        flag = !!flag;
        $state.go($state.$current.name, {service: service, edit: (flag ? true : null)}, {notify: false});
        $scope.inEdit = flag;
    };

    $scope.createNewRpcService = function () {
        var newServiceName = $scope.rpcServiceName;
        ApiRepository.createNewRpcService($scope.api.name, newServiceName, $scope.rpcServiceRoute).then(
            function (rpcResource) {
                flash.success = 'New RPC service created; please wait for the list to refresh';
                $scope.addRpcService = false;
                $scope.resetForm();
                ApiRepository.refreshApi($scope, true, 'Finished reloading RPC service list').then(
                    function () {
                        return $timeout(function () {
                            $state.go('.', { service: newServiceName, view: 'settings' }, { reload: true });
                        }, 100);
                    }
                );
            }
        ).catch(
            function (error) {
                agFormHandler.reportError(error, $scope);
            }
        );
    };

    $scope.saveRpcService = function (index) {
        var rpcServiceData = _.clone($scope.api.rpcServices[index]);
        ApiRepository.saveRpcService($scope.api.name, rpcServiceData).then(
            function (data) {
                agFormHandler.resetForm($scope);
                ApiRepository.refreshApi($scope, true, 'RPC Service updated', function () {
                    $state.go($state.$current.name, { edit: null });
                });
            }
        ).catch(
            function (error) {
                agFormHandler.reportError(error, $scope);
            }
        );
    };

    $scope.removeRpcService = function (rpcServiceName, recursive) {
        ApiRepository.removeRpcService($scope.api.name, rpcServiceName, !!recursive).then(
            function (data) {
                $scope.deleteRpcService = false;
                ApiRepository.refreshApi($scope, true, 'RPC Service deleted');
            }
        );
    };

    $scope.getSourceCode = function (className, classType) {
        ApiRepository.getSourceCode($scope.api.name, className).then(
            function (data) {
                $scope.filename = className + '.php';
                $scope.classType = classType + ' Class';
                if (typeof data.source === 'string') {
                    $scope.sourceCode = $sce.trustAsHtml(data.source);
                } else {
                    $scope.sourceCode = '';
                }
                $modal.open({
                    scope: $scope,
                    templateUrl: 'html/modals/source-code.html'
                });
            }
        );
    };
});

})(_);
