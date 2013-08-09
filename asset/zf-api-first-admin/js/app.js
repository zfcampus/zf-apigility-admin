
'use strict';

angular.module('zf-api-first-admin', ['angularTreeview'])
    .controller('IndexController', ['$http', '$scope', '$location', function($http, $scope, $location) {
		
        $scope.step1 = function() {
			$location.path('/step2');
		  };
        
        $scope.step2 = function() {
			$location.path('/step3');
		  };
		  
		  $scope.step3 = function() {
			$location.path('/step4');
		  };
		  
		  $scope.step4 = function() {
		  	$location.path('/step5');
		  };
		  
		  $scope.step5 = function() {
		  	$location.path('/step6');
		  };
		  
        $scope.loadConfiguration = function () {
			var req = {
				method: 'GET',
                url: '/admin/api/config',
                data: '',
                headers: {
                    'Content-Type': 'application/vnd.zfcampus.v1.config+json'
                }
			};
            $http(req)
                .success(function (data) {
                   
                   var result = buildTree(data,0);
                   console.log(result);
                   $scope.tree = result;
                });
        };


        $scope.addDatabase = function() {
            $http.get('/zf-api-first-admin/partials/configuration-db.html')
            .success(function (data) {
                $scope.configForm = data;
            });
        };

        $scope.doSubmit = function () {
            var req = {
                method: 'PATCH',
                url: '/admin/api/config',
                headers: {
                    'Accept': 'application/vnd.zfcampus.v1.config+json',
                    'Content-Type': 'application/vnd.zfcampus.v1.config+json'
                },
                data: {db: $scope.db}
            };
            $http(req)
                .success(function (data) {
                    $scope.configForm = 'Configuration saved.';
                    $scope.loadConfiguration();
                });

    	};

    }])
    .config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
      $routeProvider.when('/', {templateUrl: '/zf-api-first-admin/partials/wizard-step-1.html', controller: 'IndexController'});
      $routeProvider.when('/step2', {templateUrl: '/zf-api-first-admin/partials/wizard-step-2.html', controller: 'IndexController'});
	   $routeProvider.when('/step3', {templateUrl: '/zf-api-first-admin/partials/wizard-step-3.html', controller: 'IndexController'});
		$routeProvider.when('/step4', {templateUrl: '/zf-api-first-admin/partials/wizard-step-4.html', controller: 'IndexController'});
		$routeProvider.when('/step5', {templateUrl: '/zf-api-first-admin/partials/wizard-step-5.html', controller: 'IndexController'});
		$routeProvider.when('/step6', {templateUrl: '/zf-api-first-admin/partials/wizard-step-6.html', controller: 'IndexController'});
		$routeProvider.when('/step7', {templateUrl: '/zf-api-first-admin/partials/wizard-step-7.html', controller: 'IndexController'});
      $routeProvider.when('/configuration', {templateUrl: '/zf-api-first-admin/partials/configuration.html', controller: 'ConfigurationController'});
    }])
    .controller('ConfigurationController', ['$http', '$scope', '$compile', function($http, $scope, $compile) {

        $scope.loadConfiguration = function () {
			var req = {
				method: 'GET',
                url: '/admin/api/config',
                headers: {
                    'Content-Type': 'application/vnd.zfcampus.v1.config+json'
                }
			};
            $http(req)
                .success(function (data) {
                    $scope.tree = data;
                });
        };


        $scope.addDatabase = function() {
            $http.get('/zf-api-first-admin/partials/configuration-db.html')
            .success(function (data) {
                $scope.configForm = data;
            });
        };

        $scope.doSubmit = function () {
            var req = {
                method: 'PATCH',
                url: '/admin/api/config',
                headers: {
                    'Accept': 'application/vnd.zfcampus.v1.config+json',
                    'Content-Type': 'application/vnd.zfcampus.v1.config+json'
                },
                data: {db: $scope.db}
            };
            $http(req)
                .success(function (data) {
                    $scope.configForm = 'Configuration saved.';
                    $scope.loadConfiguration();
                });

    	};

        $scope.loadConfiguration();
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

function buildTree(obj, count) {
	var result = Array();
	for (var key in obj) {
		var value = obj[key];
		if (value instanceof Object) {
			result.push({ "name" : key, "id" : count++, "icon" : key, "child" : buildTree(value, count++) });
		} else {
			result.push({ "name" : key + " : " + value, "icon" : key, "id" : count++, "child" : [] })
		}
	}
	return result;
}