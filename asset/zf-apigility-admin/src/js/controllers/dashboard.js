(function() {
    'use strict';

angular.module('ag-admin').controller(
    'DashboardController',
    function($rootScope, flash) {
        $rootScope.pageTitle = 'Dashboard';
        $rootScope.pageDescription = 'Global system configuration and configuration to be applied to all APIs.';
    }
);

})();
