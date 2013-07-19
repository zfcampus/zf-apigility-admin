
'use strict';

angular.module('zf-api-first-admin', [])
    .controller('IndexController', [function() {

    }])
    .controller('DiagnosticsController', ['$http', '$scope', function($http, $scope) {
        $http({method: 'GET', url: '/zftool/diagnostics'}).
            success(function(data, status, headers, config) {
                $scope.diagnostics = data;
            }).
            error(function(data, status, headers, config) {
                console.log(data);
            });
    }])
    .config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
        $routeProvider.when('/', {templateUrl: '/zf-api-first-admin/partials/index.html', controller: 'IndexController'});
        $routeProvider.when('/diagnostics', {templateUrl: '/zf-api-first-admin/partials/diagnostics.html', controller: 'DiagnosticsController'});
    }])
;

