(function() {'use strict';

angular.module('ag-admin').run(
    function ($rootScope, $stateParams) {
        $rootScope.stateParams = $stateParams;

        $rootScope.$on('$stateChangeSuccess', function(event, toState, toParams, fromState, fromParams){
            $rootScope.navSection = toParams.controller;
            if (toParams.locals.api && $rootScope.pageTitle != toParams.locals.api.name) {
                $rootScope.pageTitle = toParams.locals.api.name;
            }
        });
    }
);

})();
