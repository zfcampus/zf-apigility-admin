(function(_) {'use strict';

angular.module('ag-admin').controller('ApiRestServicesController', ['$http', '$rootScope', '$scope', '$timeout', '$sce', 'flash', 'filters', 'hydrators', 'validators', 'ApiRepository', 'api', 'dbAdapters', 'toggleSelection', function ($http, $rootScope, $scope, $timeout, $sce, flash, filters, hydrators, validators, ApiRepository, api, dbAdapters, toggleSelection) {

    $scope.ApiRepository = ApiRepository; // used in child controller (input filters)
    $scope.flash = flash;

    $scope.api = api;

    $scope.dbAdapters = dbAdapters;

    $scope.contentNegotiation = ['HalJson', 'Json']; // @todo refactor to provider/factory

    $scope.filterOptions = filters;

    $scope.hydrators = hydrators;

    $scope.validatorOptions = validators;

    $scope.sourceCode = [];

    $scope.deleteRestService = false;

    $scope.toggleSelection = toggleSelection;

    $scope.resetForm = function () {
        $scope.showNewRestServiceForm = false;
        $scope.newService.restServiceName = '';
        $scope.newService.dbAdapterName = '';
        $scope.newService.dbTableName = '';
    };

    $scope.isLatestVersion = function () {
        return $scope.ApiRepository.isLatestVersion($scope.api);
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

    $scope.newService = {
        restServiceName: '',
        dbAdapterName: '',
        dbTableName: ''
    };

    $scope.newService.createNewRestService = function () {
        ApiRepository.createNewRestService($scope.api.name, $scope.newService.restServiceName).then(function (restResource) {
            flash.success = 'New REST Service created';
            $timeout(function () {
                ApiRepository.getApi($scope.api.name, $scope.api.version, true).then(function (api) {
                    $scope.api = api;
                    $scope.currentVersion = api.currentVersion;
                });
            }, 500);
            $scope.showNewRestServiceForm = false;
            $scope.newService.restServiceName = '';
        }, function (response) {
        });
    };

    $scope.newService.createNewDbConnectedService = function () {
        ApiRepository.createNewDbConnectedService($scope.api.name, $scope.newService.dbAdapterName, $scope.newService.dbTableName).then(function (restResource) {
            flash.success = 'New DB Connected Service created';
            $timeout(function () {
                ApiRepository.getApi($scope.api.name, $scope.api.version, true).then(function (api) {
                    $scope.api = api;
                });
            }, 500);
            $scope.showNewRestServiceForm = false;
            $scope.newService.dbAdapterName = '';
            $scope.newService.dbTableName = '';
        }, function (response) {
        });
    };

    $scope.saveRestService = function (index) {
        var restServiceData = _.clone($scope.api.restServices[index]);
        ApiRepository.saveRestService($scope.api.name, restServiceData).then(function (data) {
            flash.success = 'REST Service updated';
        });
    };

    $scope.removeRestService = function (restServiceName) {
        ApiRepository.removeRestService($scope.api.name, restServiceName)
            .then(function (data) {
                flash.success = 'REST Service deleted';
                $scope.deleteRestService = false;
                $timeout(function () {
                    ApiRepository.getApi($scope.api.name, $scope.api.version, true).then(function (api) {
                        $scope.api = api;
                        $scope.currentVersion = api.currentVersion;
                    });
                }, 500);
            });
    };

    $scope.getSourceCode = function (className, classType) {
        ApiRepository.getSourceCode ($scope.api.name, className)
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
