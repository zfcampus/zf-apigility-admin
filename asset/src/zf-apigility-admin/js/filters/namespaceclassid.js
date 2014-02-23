(function() {
    'use strict';

// Used to strip out the backslash characters to use as a part of a class id
angular.module('ag-admin').filter('namespaceclassid', function () {
    return function (input) {
        return input.replace(/\\/g, '_');
    };
});

})();
