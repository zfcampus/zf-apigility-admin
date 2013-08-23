'use strict';

var module = angular.module('zfa1-admin', ['HALParser']);

module.controller(
    'IndexController',
    ['$rootScope', function($rootScope) {
        $rootScope.pageTitle = 'Dashboard';
        $rootScope.pageDescription = 'Global system configuration and configuration to be applied to all modules.';
    }]
);

module.controller(
    'ModuleMenuController',
    ['$scope', 'moduleFactory', function($scope, moduleFactory) {
        moduleFactory.getAll().then(function (modules) {
            $scope.modules = modules;
        });
    }]
);

module.controller(
    'SubnavController',
    ['$scope', '$routeParams', 'subnavFactory', function ($scope, $routeParams, subnavFactory) {
        console.log($routeParams.moduleName);
        if ($routeParams.moduleName == undefined) {
            $scope.items = subnavFactory.getModuleNavigation($routeParams.moduleName);
        } else {
            $scope.items = subnavFactory.getGlobalNavigation();
        }

    }]
);

module.controller(
    'CreateModuleController',
    ['$rootScope', '$scope', '$http', '$location', 'HALParser', function($rootScope, $scope, $http, $location, HALParser) {
        var halParser = new HALParser;
        $scope.createNewModule = function () {
            $http.post('/admin/api/module', {name: $scope.moduleName})
                .success(function (data) {
                    var newModule = halParser.parse(data);
                    $('#create-module-button').popover('hide');
                    $rootScope.syncModuleResources();
                    $location.path('/module/' + newModule.name + '/info');
                });
        }
    }]
);

module.controller(
    'ModuleController',
    ['$rootScope', '$scope', '$routeParams', 'moduleFactory', function($rootScope, $scope, $routeParams, moduleFactory) {
        $rootScope.pageTitle = $routeParams.moduleName;
        $rootScope.pageDescription = '';
    }]
);

module.config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
    $routeProvider.when('/dashboard', {templateUrl: '/zf-api-first-admin/partials/index.html', controller: 'IndexController'});
    $routeProvider.when('/module/:moduleName/:section', {templateUrl: '/zf-api-first-admin/partials/module.html', controller: 'ModuleController'});
    $routeProvider.otherwise({redirectTo: '/dashboard'})
}]);


module.directive('popover', function($compile) {
    return {
        restrict: "A",
        link: function (scope, element, attrs) {
            var popOverContent;
            var html = $(attrs.content).html();
            popOverContent = $compile(html)(scope);
            var options = {
                content: popOverContent,
                placement: "bottom",
                html: true,
                title: scope.title
            };
            $(element).popover(options);
        },
        scope: {
            items: '=',
            title: '@'
        }
    };
});

module.factory('subnavFactory', ['moduleFactory', function (moduleFactory) {
    return {
        getGlobalNavigation: function () {
            return [
                {name: "General Information", link: "/general-information"},
                {name: "Media Types",         link: "/media-types"},
                {name: "Authentication",      link: "/authentication"},
                {name: "phpinfo()",           link: "/phpinfo"},
                {name: "zf2info()",           link: "/zf2info"}
            ];
        },
        getModuleNavigation: function (moduleName) {
            return [
                {name: "General Information",  link: 'module/' + moduleName + '/info'},
                {name: "API Resources",        link: 'module/' + moduleName + '/api-resources'},
                {name: "RPC Endpoints",        link: 'module/' + moduleName + '/rpc-endpoints'},
                {name: "Authentication",       link: 'module/' + moduleName + '/authentication'},
                {name: "Filters / Validators", link: 'module/' + moduleName + '/filters-validators'}
            ];
        }
    };
}]);

module.factory('moduleFactory', ['$http', 'HALParser', function ($http, HALParser) {
    var halParser = new HALParser;
    return {
        getAll: function () {
            return $http.get('/admin/api/module')
                .then(function (result) {
                    return halParser.parse(result.data).module;
                });
        },
        getByName: function (name) {
            return $http.get('/admin/api/module/' + name)
                .then(function (result) {
                    return halParser.parse(result.data);
                });
        }
    }
}]);

module.run(['$rootScope', function ($rootScope) {

}]);
