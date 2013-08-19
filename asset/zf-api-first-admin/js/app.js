'use strict';

var module = angular.module('zfa1-admin', ['HALParser']);

module.controller(
    'IndexController',
    ['$rootScope', function($rootScope) {
	$rootScope.pageTitle = 'Dashboard';
	$rootScope.pageDescription = 'Global system configuration and configuration to be applied to all modules.';
	$rootScope.subNavItems = ['General Information', 'Media Types', 'Authenticaiton', 'phpinfo()', 'ZF2 Info'];
    }]
);

module.controller(
    'ModuleController',
    ['$rootScope', '$scope', '$routeParams', '$http', 'HALParser', function($rootScope, $scope, $routeParams, $http, HALParser) {

	var halParser = new HALParser;

	$rootScope.pageTitle = $routeParams.name;
	$rootScope.pageDescription = '';

	$scope.showRestResources = ($routeParams.section == 'rest-resources');

	function load() {
	    $http.get('/admin/api/module/' + $routeParams.name)
		.success(function (data) {
//                    console.log(data);
		    $scope.moduleResource = halParser.parse(data);
		    $rootScope.pageTitle = $scope.moduleResource.namespace;
		    $rootScope.pageDescription = '';

		    $rootScope.subNavItems = {};
		    $rootScope.subNavItems['module/' + $scope.moduleResource.module + '/info'] = 'General Information';
		    $rootScope.subNavItems['module/' + $scope.moduleResource.module + '/rest-resources'] = 'API Resources';
		    $rootScope.subNavItems['module/' + $scope.moduleResource.module + '/api-endpoints'] = 'API RPC Endpoints';
		    $rootScope.subNavItems['module/' + $scope.moduleResource.module + '/authentication'] = 'Authentication';
		    $rootScope.subNavItems['module/' + $scope.moduleResource.module + '/filters-validators'] = 'Filters / Validatators';

		});
	}

	load();
    }]
);

module.config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
    $routeProvider.when('/', {templateUrl: '/zf-api-first-admin/partials/index.html', controller: 'IndexController'});
    $routeProvider.when('/module/:name/:section', {templateUrl: '/zf-api-first-admin/partials/module.html', controller: 'ModuleController'});
    // Configure the app to use push state routing.
//    $locationProvider.html5Mode(true).hashPrefix('#');
}]);

module.run(['$rootScope', '$http', 'HALParser', function ($rootScope, $http, HALParser) {

    $rootScope.moduleResources = [];

    var halParser = new HALParser;

    function initialize() {
	$http.get('/admin/api/module')
	    .success(function (data) {
//                console.log(data);
		$rootScope.moduleResources = halParser.parse(data);
	    });
    }

    initialize();


}]);
