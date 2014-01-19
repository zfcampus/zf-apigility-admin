(function() {'use strict';

angular.module('ag-admin').run([
    '$rootScope', '$routeParams', '$location', '$route', 
    function ($rootScope, $routeParams, $location, $route) {
        $rootScope.routeParams = $routeParams;

        $rootScope.$on('$routeChangeSuccess', function(scope, next, current){
            scope.targetScope.$root.navSection = $route.current.controller;
            if (next.locals.api && scope.targetScope.$root.pageTitle != next.locals.api.name) {
                scope.targetScope.$root.pageTitle = next.locals.api.name;
            }
        });
    }
]);

})();
