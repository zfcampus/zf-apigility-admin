(function() {'use strict';

// @todo refactor the naming of this at some point
angular.module('ag-admin').filter('servicename', function () {
    return function (input) {
        var parts = input.split('::');
        var newServiceName = parts[0] + ' (';
        switch (parts[1]) {
            case '__collection__': newServiceName += 'Collection)'; break;
            case '__resource__': newServiceName += 'Entity)'; break;
            default: newServiceName += parts[1] + ")"; break;
        }
        return newServiceName;
    };
});

})();
