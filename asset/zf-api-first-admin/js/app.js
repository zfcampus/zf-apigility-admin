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

        // initial load
        $scope.modules = ModuleService.getAll();

        // and any time its changed
        $scope.$on('ModuleService.listUpdate', function () {
            $scope.modules = ModuleService.getAll();
        });

//
//
//            ModuleService.getAll().then(function (modules) {
//                $scope.modules = modules;
//            });
//        };
//        $scope.$on('UpdateModuleList', function () {
//            updatePrimaryNavigation();
//        });
//        updatePrimaryNavigation();
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

        $scope.$on('$routeChangeSuccess', function () {
            updateSecondaryNavigation();
        });

        // do first load (probably dashboard)
        updateSecondaryNavigation();
    }]
);

module.controller(
    'CreateModuleController',
    ['$rootScope', '$scope', '$location', 'ModuleService', function($rootScope, $scope, $location, ModuleService) {
        $scope.createNewModule = function () {
            ModuleService.createNewModule($scope.moduleName).then(function (module) {
                $('#create-module-button').popover('hide');
                $location.path('/module/' + module.name + '/info');
                $scope.$broadcast('UpdatePrimaryNavigation');
            });
        };
    }]
);

module.controller(
    'CreateRestResourceController',
    ['$rootScope', '$scope', '$location', 'ModuleService', function($rootScope, $scope, $location, ModuleService) {
        $scope.createNewRestResource = function () {
            ModuleService.createNewRestResource($scope.restResourceName).then(function (module) {
                $('#create-rest-resource-form').popover('hide');
                $location.path('/module/' + module.name + '/rest-resources');
            });
        };
    }]
);

module.controller(
    'ModuleController',
    ['$rootScope', '$scope', '$routeParams', 'ModuleService', function($rootScope, $scope, $routeParams, ModuleService) {
        $rootScope.pageTitle = '';
        $rootScope.pageDescription = '';

        ModuleService.getByName($routeParams.moduleName).then(function (module) {
            console.log(module);
            $scope.module = module;
            $rootScope.pageTitle = module.name;
            $rootScope.pageDescription = 'Module description tbd';
            ModuleService.currentModule = module;
        });

        $scope.show = {
            restResources: false,
            rpcEndpoints: false
        };

        switch ($routeParams.section) {
            case 'rest-resources': $scope.show.restResources = true; break;
            case 'rpc-endpoints': $scope.show.rpcEndpoints = true; break;
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
                {name: "REST Resources",        link: '/module/' + moduleName + '/rest-resources'},
                {name: "RPC Endpoints",        link: '/module/' + moduleName + '/rpc-endpoints'},
                {name: "Authentication",       link: '/module/' + moduleName + '/authentication'},
                {name: "Filters / Validators", link: '/module/' + moduleName + '/filters-validators'}
            ];
        }
    };
});

module.factory('ModuleService', ['$rootScope', '$http', 'halClient', function ($rootScope, $http, halClient) {
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
                .then(function (moduleResource) {
                    if (moduleResource.$has('rest')) {
                        moduleResource.$get('rest').then(function (s) {
                            moduleResource.rest = s;
                        });
                    } else {
                        moduleResource.rest = [];
                    }
                    return moduleResource;
                });
        },
        createNewModule: function (moduleName) {
            var postPromise = halClient.$post('/admin/api/module', {}, {name: moduleName})
                .then(function (halResource) {
                    return halResource;
                });
            $rootScope.$broadcast('ModuleService.listUpdate');
            return postPromise;
        },
        createNewRestResource: function (restResourceName, moduleName) {
            if (moduleName == undefined) {
                console.log(service.currentModule);
                moduleName = service.currentModule.name;
            }
            console.log(moduleName);
            return halClient.$post('/admin/api/module/' + moduleName + '/rest', {}, {resource_name: restResourceName});
        }
    };
    return service;
}]);

module.run(['$rootScope', function ($rootScope) {

}]);
