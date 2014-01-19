(function() { 'use strict';

angular.module('ag-admin').directive('agModalDismiss', function() {
    return {
        restrict: 'A',
        link: function(scope, element, attr) {
            scope.dismissModal = function() {
                element.modal('hide');
            };
        }
    };
});

})();
