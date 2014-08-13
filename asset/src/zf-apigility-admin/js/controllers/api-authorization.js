(function(_) {
    'use strict';

angular.module('ag-admin').controller(
    'ApiAuthorizationController',
    function ($scope, $stateParams, flash, api, apiAuthorizations, authentication, ApiAuthorizationRepository, agFormHandler) {
        $scope.api = api;
        $scope.apiAuthorizations = apiAuthorizations;
        $scope.authentication = authentication;

        var version = $stateParams.version.match(/\d/g)[0] || 1;
        $scope.editable = (version == api.versions[api.versions.length - 1]);

        var serviceMethodMap = (function() {
            var serviceNameNormalization = new RegExp('-', 'g');
            var services = {};
            angular.forEach(api.restServices, function(service) {
                var serviceName = service.controller_service_name.replace(serviceNameNormalization, '\\');
                var entityName = serviceName + '::__entity__';
                var collectionName = serviceName + '::__collection__';
                var entityMethods = {
                    GET: false,
                    POST: false,
                    PUT: false,
                    PATCH: false,
                    DELETE: false,
                };
                var collectionMethods = {
                    GET: false,
                    POST: false,
                    PUT: false,
                    PATCH: false,
                    DELETE: false,
                };
                angular.forEach(service.entity_http_methods, function(method) {
                    entityMethods[method] = true;
                });
                angular.forEach(service.collection_http_methods, function(method) {
                    collectionMethods[method] = true;
                });
                services[entityName] = entityMethods;
                services[collectionName] = collectionMethods;
            });

            angular.forEach(api.rpcServices, function(service) {
                var serviceName = service.controller_service_name.replace(serviceNameNormalization, '\\');
                var serviceMethods = {
                    GET: false,
                    POST: false,
                    PUT: false,
                    PATCH: false,
                    DELETE: false,
                };
                angular.forEach(service.http_methods, function(method) {
                    serviceMethods[method] = true;
                });
                services[serviceName] = serviceMethods;
            });

            return services;
        })();

        $scope.isEditable = function(serviceName, method) {
            if (!$scope.editable) {
                return false;
            }

            if (!serviceMethodMap.hasOwnProperty(serviceName)) {
                var parts = serviceName.split('::');
                var test  = parts[0];
                if (!serviceMethodMap.hasOwnProperty(test)) {
                    return false;
                }
                serviceName = test;
            }

            return serviceMethodMap[serviceName][method];
        };

        $scope.saveAuthorization = function () {
            ApiAuthorizationRepository.saveApiAuthorizations($stateParams.apiName, $scope.apiAuthorizations).then(
                function (savedAuthorizations) {
                    agFormHandler.resetForm($scope);
                    flash.success = 'Authorization settings saved';
                }
            ).catch(
                function (error) {
                    agFormHandler.reportError(error, $scope);
                }
            );
        };

        $scope.updateColumn = function ($event, column) {
            angular.forEach($scope.apiAuthorizations, function (item, name) {
                if ($scope.isEditable(name, column)) {
                    $scope.apiAuthorizations[name][column] = $event.target.checked;
                }
            });
        };

        $scope.updateRow = function ($event, name) {
            _.forEach(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], function (method) {
                if ($scope.isEditable(name, method)) {
                    $scope.apiAuthorizations[name][method] = $event.target.checked;
                }
            });
        };

        $scope.showTopSaveButton = function () {
            return (Object.keys(apiAuthorizations).length > 10);
        };
    }
);

})(_);
