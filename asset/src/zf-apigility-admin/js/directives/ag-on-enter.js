(function() { 'use strict';

angular.module('ag-admin').directive('agOnEnter', 
    function() {
        return {
            restrict: 'A',
            link: function(scope, element, attr) {
                element.bind('keydown', function (e) {
                    element.focus();
                });

                element.bind('keyup', function (e) {
                    if (e.which !== 13) {
                        return;
                    }
                    e.stopPropagation();
                    scope.$apply(attr.agOnEnter);
                });
            }
        };
    }
);

})();
