(function() {'use strict';

// @todo refactor the naming of this at some point
angular.module('ag-admin').filter('servicetype', function () {
    return function (input) {
        var parts = input.split('::');
        switch (parts[1]) {
            case '__collection__': return '(Collection)';
            case '__entity__':     return '(Entity)';
            default: return '';
        }
    };
});

})();

