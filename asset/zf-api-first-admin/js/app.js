'use strict';

var module = angular.module('zfa1-admin', []);

module.controller(
    'DashboardController',
    ['$rootScope', function($rootScope) {
        $rootScope.pageTitle = 'Dashboard';
        $rootScope.pageDescription = 'Global system configuration and configuration to be applied to all modules.';
    }]
);

module.controller(
    'ModuleListController',
    ['$scope', 'ModulesResource', function($scope, ModulesResource) {
        // $scope vars
        $scope.modules = [];

        var updateModuleList = function () {
            ModulesResource.fetch().then(function (modules) {
                $scope.$apply(function () {
                    $scope.modules = modules.embedded.module;
                });
            });
        };
        updateModuleList();

        // on refresh, and initial load
        $scope.$on('ModuleList.refresh', function () {
            updateModuleList();
        });
    }]
);

// this should probably be a directive
module.controller(
    'ViewNavigationController',
    ['$rootScope', '$scope', '$routeParams', 'SecondaryNavigationService', function ($rootScope, $scope, $routeParams, SecondaryNavigationService) {

        function updateSecondaryNavigation() {
            if ($routeParams.moduleName == undefined) {
                $scope.items = SecondaryNavigationService.getGlobalNavigation();
            } else {
                $scope.items = SecondaryNavigationService.getModuleNavigation($routeParams.moduleName);
                $scope.section = $routeParams.section;
            }
        }
        updateSecondaryNavigation();

        // on refresh, and initial load
        $scope.$on('$routeChangeSuccess', function () {
            updateSecondaryNavigation();
        });
    }]
);

module.controller(
    'CreateModuleController',
    ['$rootScope', '$scope', '$location', 'ModulesResource', function($rootScope, $scope, $location, ModulesResource) {
        $scope.createNewModule = function () {
            ModulesResource.createNewModule($scope.moduleName).then(function (newModule) {
                ModulesResource.fetch({force: true}).then(function (modules) {
                    $rootScope.$broadcast('ModuleList.refresh');
                    $('#create-module-button').popover('hide');
                    $location.path('/module/' + newModule.name + '/info');
                });
            });
        };
    }]
);

module.controller(
    'ModuleController',
    ['$rootScope', '$scope', '$routeParams', 'ModulesResource', function($rootScope, $scope, $routeParams, ModulesResource) {

        $scope.module = null;
        $scope.section = null;

        function updateModule() {

            ModulesResource.fetch().then(function (modules) {

                var briefModule = _.find(modules.embedded.module, function (m) {
                    return m.props.name === $routeParams.moduleName;
                });

                briefModule.links['self'].fetch().then(function (module) {
                    console.log('updating module');
                    // update UI immediately:
                    $scope.$apply(function () {
                        $scope.module = module;
                        $rootScope.pageTitle = module.props.namespace;
                        $rootScope.pageDescription = 'tbd';
                        $scope.section = $routeParams.section;
                    });
                });

            });
        }
        updateModule();

        $scope.$on('Module.refresh', function () {
            updateModule();
        });

    }]
);

module.directive('moduleRestEndpoints', function () {
    return {
        restrict: 'E',
        //scope: {
        //    current: '=current'
        //},
        templateUrl: '/zf-api-first-admin/partials/module/rest-endpoints.html',
        controller: ['$rootScope', '$scope', 'ModulesResource', function ($rootScope, $scope, ModulesResource) {
            $scope.module = $scope.$parent.module;

            function updateModuleRestEndpoints(force) {
                $scope.restEndpoints = [];
                $scope.module.links['rest'].fetch({force: force}).then(function (restEndpoints) {
                    // update view
                    $scope.$apply(function() {
                        $scope.restEndpoints = restEndpoints.embedded.rest;
                    });
                });
            }
            updateModuleRestEndpoints(false);

            $scope.createNewRestEndpoint = function () {
                ModulesResource.createNewRestEndpoint($scope.module.props.name, $scope.restEndpointName).then(function (restResource) {
                    updateModuleRestEndpoints(true);
                    $('#create-rest-endpoint-button').popover('hide');
                });
            };
        }]
    }
});

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
                {id: 'general-information', name: "General Information", link: '/general-information'},
                {id: 'media-types', name: "Media Types", link: '/media-types'},
                {id: 'authentication', name: "Authentication", link: '/authentication'},
                {id: 'phpinfo', name: "phpinfo()", link: '/phpinfo'},
                {id: 'zf2info', name: "zf2info()", link: '/zf2info'}
            ];
        },
        getModuleNavigation: function (moduleName, section) {
            return [
                {id: 'info', name: "General Information", link: '/module/' + moduleName + '/info'},
                {id: 'rest-endpoints', name: "REST Endpoints", link: '/module/' + moduleName + '/rest-endpoints'},
                {id: 'rpc-endpoints', name: "RPC Endpoints", link: '/module/' + moduleName + '/rpc-endpoints'},
                {id: 'authentication', name: "Authentication", link: '/module/' + moduleName + '/authentication'},
                {id: 'filters-validators', name: "Filters / Validators", link: '/module/' + moduleName + '/filters-validators'}
            ];
        }
    };
});

module.factory('ModulesResource', ['$http', function ($http) {
    var resource = new Hyperagent.Resource('/admin/api/module');

    resource.createNewModule = function (name) {
        return $http.post('/admin/api/module', {name: name})
            .then(function (response) {
                return response.data;
            });
    };

    resource.createNewRestEndpoint = function (moduleName, restEndpointName) {
        return $http.post('/admin/api/module/' + moduleName + '/rest', {resource_name: restEndpointName})
            .then(function (response) {
                return response.data;
            });
    };

    return resource;
}]);

module.run(['$rootScope', function ($rootScope) {

}]);
