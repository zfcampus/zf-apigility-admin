
'use strict';

angular.module('zf-api-first-admin', [])
    .controller('IndexController', [function() {

    }])
    .config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
        $routeProvider.when('/', {templateUrl: '/zf-api-first-admin/partials/index.html', controller: 'IndexController'});
        $routeProvider.when('/configuration', {templateUrl: '/zf-api-first-admin/partials/configuration.html', controller: 'ConfigurationController'});
    }])
    .controller('ConfigurationController', ['$http', '$scope', '$compile', function($http, $scope, $compile) {



//        $http.get('/admin/api/config', {headers: {'Content-Type': 'application/vnd.zfcampus.v1.config'}})
//            .success(function (data) {
//                $scope.configurations = data;
//            });

	$scope.addDatabase = function() {
	    $http.get('/zf-api-first-admin/partials/configuration-db.html')
		.success(function (data) {
		    $scope.configForm = data;
		});
	};

	$scope.doSubmit = function () {
	    $http({
		    method: 'PATCH',
		    url: '/admin/api/config',
		    headers: {'Content-Type': 'application/vnd.zfcampus.v1.config+json'},
		    data: {db: $scope.db}
	    })
		.success(function (data) {
		    console.log(data);
		});

	}
    }])
    .directive('compile', function($compile) {
	// directive factory creates a link function
	return function(scope, element, attrs) {
	    scope.$watch(
		function(scope) {
		    // watch the 'compile' expression for changes
		    return scope.$eval(attrs.compile);
		},
		function(value) {
		    // when the 'compile' expression changes
		    // assign it into the current DOM
		    element.html(value);

		    // compile the new DOM and link it to the current
		    // scope.
		    // NOTE: we only compile .childNodes so that
		    // we don't get into infinite loop compiling ourselves
		    $compile(element.contents())(scope);
		}
	    );
	};
    })
;

