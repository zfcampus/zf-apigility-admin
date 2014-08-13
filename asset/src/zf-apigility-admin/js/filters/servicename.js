(function() {
    'use strict';

    // @todo refactor the naming of this at some point
    angular.module('ag-admin').filter('servicename', function() {
        return function(input) {
            /* For controller service name like "Status\V3\Rest\Message\Controller" or
             * "Status-V3-Rest-Message-Controller", return "Message"
             */
            var r = /^[^\\-]+[\\-]{1,2}V[^\\-]+[\\-]{1,2}(Rest|Rpc)[\\-]{1,2}([^\\-]+)[\\-]{1,2}.*?Controller.*?$/;
            if (!input.match(r)) {
                return input;
            }
            return input.replace(r, '$2');
        };
    });

})();
