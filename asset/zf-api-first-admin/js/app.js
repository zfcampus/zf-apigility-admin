
'use strict';

angular.module('zf-api-first-admin')
    .controller('IndexController', ['$http', '$scope', '$location', function($http, $scope, $location) {

    }])
    .config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
	$routeProvider.when('/', {templateUrl: '/zf-api-first-admin/partials/index.html', controller: 'IndexController'});
    }])
;

