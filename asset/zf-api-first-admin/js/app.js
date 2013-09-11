'use strict';

var module = angular.module('zfa1-admin', ['HALParser', 'angular-hal']);

module.controller(
    'DashboardController',
    ['$rootScope', function($rootScope) {
        $rootScope.pageTitle = 'Dashboard';
        $rootScope.pageDescription = 'Global system configuration and configuration to be applied to all modules.';
    }]
);

module.controller(
    'ModuleListController',
    ['$scope', 'ModuleService', function($scope, ModuleService) {

        var updateModuleList = function () {
            $scope.modules = ModuleService.getAll();
        };

        // on refresh, and initial load
        $scope.$on('ModuleList.refresh', function () {
            updateModuleList();
        });
        updateModuleList();
    }]
);

module.controller(
    'ViewNavigationController',
    ['$rootScope', '$scope', '$routeParams', 'SecondaryNavigationService', function ($rootScope, $scope, $routeParams, SecondaryNavigationService) {

        var updateSecondaryNavigation = function () {
            if ($routeParams.moduleName == undefined) {
                $scope.items = SecondaryNavigationService.getGlobalNavigation();
            } else {
                $scope.items = SecondaryNavigationService.getModuleNavigation($routeParams.moduleName);
            }
        };

        // on refresh, and initial load
        $scope.$on('$routeChangeSuccess', function () {
            updateSecondaryNavigation();
        });
        updateSecondaryNavigation();
    }]
);

module.controller(
    'CreateModuleController',
    ['$rootScope', '$scope', '$location', 'ModuleService', function($rootScope, $scope, $location, ModuleService) {
        $scope.createNewModule = function () {
            ModuleService.createNewModule($scope.moduleName).then(function (module) {
                $rootScope.$broadcast('ModuleList.refresh');
                $('#create-module-button').popover('hide');
                $location.path('/module/' + module.name + '/info');
            });
        };
    }]
);

module.controller(
    'CreateRestResourceController',
    ['$rootScope', '$scope', '$location', 'ModuleService', function($rootScope, $scope, $location, ModuleService) {
        $scope.createNewRestResource = function () {
            ModuleService.createNewRestResource($scope.restResourceName).then(function (restResource) {
                $rootScope.$broadcast('Module.refresh');
                $('#create-rest-resource-button').popover('hide');
                $location.path('/module/' + restResource.module + '/rest-endpoints');
            });
        };
    }]
);

module.controller(
    'ModuleController',
    ['$rootScope', '$scope', '$routeParams', 'ModuleService', function($rootScope, $scope, $routeParams, ModuleService) {
        $rootScope.pageTitle = '';
        $rootScope.pageDescription = '';

        var updateModule = function () {
            ModuleService.getByName($routeParams.moduleName).then(function (module) {
                $scope.module = module;
                $rootScope.pageTitle = module.name;
                $rootScope.pageDescription = 'Module description TBD';
                ModuleService.currentModule = module;
            });
        };

        // on refresh, and initial load
        $scope.$on('Module.refresh', function () {
            updateModule();
        });
        updateModule();

        $scope.show = {
            restEndpoints: false,
            rpcEndpoints: false
        };

        switch ($routeParams.section) {
            case 'rest-endpoints': 
                ModuleService.getEndpointsByType("rest", $routeParams.moduleName).then(function (rest) {
                    $scope.module.rest = rest;
                    $scope.show.restEndpoints = true;
                });
                $scope.show.restEndpoints = true; 
                break;
            case 'rpc-endpoints': 
                ModuleService.getEndpointsByType("rpc", $routeParams.moduleName).then(function (rpc) {
                    console.log("Retrieved RPC endpoints");
                    console.log(rpc);
                    $scope.module.rpc = rpc;
                    $scope.show.rpcEndpoints = true;
                });
                break;
        }
    }]
);

module.config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
    $routeProvider.when('/dashboard', {templateUrl: '/zf-api-first-admin/partials/index.html', controller: 'DashboardController'});
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

module.factory('SecondaryNavigationService', function () {
    return {
        getGlobalNavigation: function () {
            return [
                {name: "General Information", link: '/general-information'},
                {name: "Media Types",         link: '/media-types'},
                {name: "Authentication",      link: '/authentication'},
                {name: "phpinfo()",           link: '/phpinfo'},
                {name: "zf2info()",           link: '/zf2info'}
            ];
        },
        getModuleNavigation: function (moduleName) {
            return [
                {name: "General Information",  link: '/module/' + moduleName + '/info'},
                {name: "REST Endpoints",       link: '/module/' + moduleName + '/rest-endpoints'},
                {name: "RPC Endpoints",        link: '/module/' + moduleName + '/rpc-endpoints'},
                {name: "Authentication",       link: '/module/' + moduleName + '/authentication'},
                {name: "Filters / Validators", link: '/module/' + moduleName + '/filters-validators'}
            ];
        }
    };
});

module.factory('ModuleService', ['$rootScope', '$http', 'halClient', 'HALParser', function ($rootScope, $http, halClient, halParser) {
    var service = {
        currentModule: null,
        getAll: function () {
            return halClient.$get('/admin/api/module')
                .then(function (halResource) {
                    var modules = [];
                    halResource.$get('module').then(function (halResourceModule) {
                        halResourceModule.forEach(function (mod) {
                            modules.push(mod);
                        });
                    });
                    return modules;
                });
        },
        getByName: function (moduleName) {
            return halClient.$get('/admin/api/module/' + moduleName)
                .then(function (halResource) {
                    return halResource;
                });
        },
        getEndpointsByType: function (type, module) {
            var uri = '/admin/api/module/' + module + '/' + type;
            console.log('Fetching URI ' + uri);
            return $http.get(uri)
                .then(function (data) {
                    var parser   = new halParser();
                    var resource = parser.parse(data.data);
                    return resource[type];
                });
        },
        createNewModule: function (moduleName) {
            return halClient.$post('/admin/api/module', {}, {name: moduleName})
                .then(function (halResource) {
                    return halResource;
                });
        },
        createNewRestResource: function (restResourceName, moduleName) {
            if (moduleName == undefined) {
                moduleName = service.currentModule.name;
            }
            return halClient.$post('/admin/api/module/' + moduleName + '/rest', {}, {resource_name: restResourceName});
        }
    };
    return service;
}]);

module.run(['$rootScope', function ($rootScope) {

}]);
