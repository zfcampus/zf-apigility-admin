(function(_) {'use strict';

angular.module('ag-admin').controller(
    'ApiAuthorizationController',
    ['$http', '$rootScope', '$scope', '$routeParams', 'flash', 'api', 'apiAuthorizations', 'authentication', 'ApiAuthorizationRepository', function ($http, $rootScope, $scope, $routeParams, flash, api, apiAuthorizations, authentication, ApiAuthorizationRepository) {
        $scope.api = api;
        $scope.apiAuthorizations = apiAuthorizations;
        $scope.authentication = authentication;

        var version = $routeParams.version.match(/\d/g)[0] || 1;
        $scope.editable = (version == api.versions[api.versions.length - 1]);

        var serviceMethodMap = (function() {
            var services = {};
            angular.forEach(api.restServices, function(service) {
                var entityName = service.controller_service_name + '::__resource__';
                var collectionName = service.controller_service_name + '::__collection__';
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
                angular.forEach(service.resource_http_methods, function(method) {
                    entityMethods[method] = true;
                });
                angular.forEach(service.collection_http_methods, function(method) {
                    collectionMethods[method] = true;
                });
                services[entityName] = entityMethods;
                services[collectionName] = collectionMethods;
            });
            return services;
        })();

        $scope.isEditable = function(serviceName, method) {
            if (!$scope.editable) {
                return false;
            }

            if (!serviceMethodMap.hasOwnProperty(serviceName)) {
                return false;
            }

            return serviceMethodMap[serviceName][method];
        };

        $scope.saveAuthorization = function () {
            flash.success = 'Authorization settings saved';
            ApiAuthorizationRepository.saveApiAuthorizations($routeParams.apiName, $scope.apiAuthorizations);
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
    }]
);

})(_);
