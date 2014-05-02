(function() { 'use strict';

angular.module('ag-admin').directive('agCancelEdit', 
    function($state) {
        return {
            restrict: 'A',
            link: function(scope, element, attr) {
                element.on('click', function () {
                    $state.go($state.$current.name, { edit: null }, {reload: true});
                });
            }
        };
    }
);

})();
