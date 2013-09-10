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
    'ModuleMenuController',
    ['$scope', 'ModuleService', function($scope, ModuleService) {
        ModuleService.getAll().then(function (modules) {
            $scope.modules = modules;
        });
    }]
);

module.controller(
    'SubnavController',
    ['$rootScope', '$scope', '$routeParams', 'SubnavService', function ($rootScope, $scope, $routeParams, SubnavService) {

        var updateSubnav = function () {
            if ($routeParams.moduleName == undefined) {
                $scope.items = SubnavService.getGlobalNavigation();
            } else {
                $scope.items = SubnavService.getModuleNavigation($routeParams.moduleName);
            }
        };

        $scope.$on('$routeChangeSuccess', function () {
            updateSubnav();
        });

        $scope.$on('SubnavUpdate', function () {
            updateSubnav();
        });

        // do first load (probably dashboard)
        updateSubnav();
    }]
);

module.controller(
    'CreateModuleController',
    ['$rootScope', '$scope', '$location', 'ModuleService', function($rootScope, $scope, $location, ModuleService) {
        $scope.createNewModule = function () {
            ModuleService.createNewModule($scope.moduleName).then(function (module) {
                $('#create-module-button').popover('hide');
                $location.path('/module/' + module.name + '/info');
                $scope.$broadcast('SubnavUpdate');
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

module.factory('SubnavService', function () {
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

module.factory('ModuleService', ['$http', 'HALParser', 'halClient', function ($http, HALParser, halClient) {
    var halParser = new HALParser;
    var currentModule = null;
    return {
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
//                    moduleResource.rest = [];
                    moduleResource.$get('rest').then(function (s) {
//                        angular.forEach(s, function (x) {
//
//                        });
//                        console.log(s);
                        moduleResource.rest = s;
                    });
                    return moduleResource;
                });
        },
        createNewModule: function (moduleName) {
            return $http.post('/admin/api/module', {name: moduleName})
                .then(function (result) {
                    return halParser.parse(result.data);
                });
        },
        createNewRestResource: function (restResourceName, moduleName) {
            if (moduleName == undefined) {
                moduleName = this.currentModule.name;
            }
            return halClient.$post('/admin/api/module/' + moduleName + '/rest', {}, {resource_name: restResourceName});
        }
    }
}]);

module.run(['$rootScope', function ($rootScope) {

}]);
