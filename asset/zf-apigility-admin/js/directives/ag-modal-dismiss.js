'use strict';

var agModalDismiss = angular.module('ag-modal-dismiss', []);

agModalDismiss.directive('agModalDismiss', function() {
    return {
        restrict: 'A',
        link: function(scope, element, attr) {
            scope.dismissModal = function() {
                element.modal('hide');
            };
        }
    };
});
