(function() { 'use strict';

angular.module('ag-admin').directive('agOnEnter', 
    function() {
        return {
            restrict: 'A',
            link: function(scope, element, attr) {
console.log('Linking element on enter');
console.log(element);
console.log(attr.agOnEnter);
                element.bind('keydown', function (e) {
console.log('Detected keydown event');
console.log(e);
                    element.focus();
                });

                element.bind('keyup', function (e) {
console.log('Detected keyup event');
console.log(e);
                    if (e.which !== 13) {
console.log('Not an enter key');
                        return;
                    }
console.log('Enter key detected; evaluating expression');
                    e.stopPropagation();
                    scope.$apply(attr.agOnEnter);
                });
            }
        };
    }
);

})();
