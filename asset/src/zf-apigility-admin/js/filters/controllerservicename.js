(function() {
    'use strict';

    angular.module('ag-admin').filter('controllerservicename', function() {
        return function(input) {
            /* For controller service name like "Status-V3-Rest-Message-Controller",
             * return "Status\V3\Rest\Message\Controller".
             */
            return input.replace(/-/g, '\\');
        };
    });

})();

