(function(_) {
    'use strict';

angular.module('ag-admin').controller(
  'ApiRestServicesController', 
  function ($scope, $state, $stateParams, $sce, $modal, $timeout, flash, filters, hydrators, validators, selectors, ApiRepository, api, dbAdapters, toggleSelection, agFormHandler) {

    $scope.activeService     = $stateParams.service ? $stateParams.service : '';
    $scope.inEdit            = !!$stateParams.edit;
    $scope.view              = $stateParams.view ? $stateParams.view : 'settings';
    $scope.ApiRepository     = ApiRepository; // used in child controller (input filters)
    $scope.flash             = flash;
    $scope.api               = api;
    $scope.version           = $stateParams.version;
    $scope.dbAdapters        = dbAdapters;
    $scope.filterOptions     = filters;
    $scope.hydrators         = hydrators;
    $scope.validatorOptions  = validators;
    $scope.selectors         = selectors;
    $scope.sourceCode        = [];
    $scope.deleteRestService = false;
    $scope.toggleSelection   = toggleSelection;
    $scope.newService        = {
        restServiceName: '',
        dbAdapterName:   '',
        dbTableName:     ''
    };

    $scope.resetForm = function () {
        agFormHandler.resetForm($scope);
        $scope.showNewRestServiceForm     = false;
        $scope.newService.restServiceName = '';
        $scope.newService.dbAdapterName   = '';
        $scope.newService.dbTableName     = '';
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

    $scope.isDbConnected = function (restService) {
        if (typeof restService !== 'object' || typeof restService === 'undefined') {
            return false;
        }
        if (restService.hasOwnProperty('adapter_name') || restService.hasOwnProperty('table_name') || restService.hasOwnProperty('table_service')) {
            return true;
        }
        return false;
    };

    $scope.newService.createNewRestService = function () {
        var newServiceName = $scope.newService.restServiceName;
        ApiRepository.createNewRestService($scope.api.name, newServiceName).then(
            function (restResource) {
                flash.success = 'New Code-Connected REST service created; please wait for the list to refresh';
                $scope.resetForm();
                ApiRepository.refreshApi($scope, true, 'Finished reloading REST service list').then(
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

    $scope.newService.createNewDbConnectedService = function () {
        var newServiceName = $scope.newService.dbTableName;
        ApiRepository.createNewDbConnectedService($scope.api.name, $scope.newService.dbAdapterName, newServiceName).then(
            function (restResource) {
                flash.success = 'New DB-Connected REST service created; please wait for the list to refresh';
                $scope.resetForm();
                ApiRepository.refreshApi($scope, true, 'Finished reloading REST service list').then(
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

    $scope.saveRestService = function (index) {
        var restServiceData = _.clone($scope.api.restServices[index]);
        ApiRepository.saveRestService($scope.api.name, restServiceData).then(
            function (data) {
                agFormHandler.resetForm($scope);
                flash.success = 'REST Service updated';
                $state.go($state.$current.name, { edit: null }, {reload: true});
            }
        ).catch(
            function (error) {
                agFormHandler.reportError(error, $scope);
            }
        );
    };

    $scope.removeRestService = function (restServiceName, recursive) {
        ApiRepository.removeRestService($scope.api.name, restServiceName, !!recursive).then(
            function (data) {
                ApiRepository.refreshApi($scope, true, 'REST Service deleted');
                $scope.deleteRestService = false;
            }
        );
    };

    $scope.getSourceCode = function (className, classType) {
        ApiRepository.getSourceCode ($scope.api.name, className).then(
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
