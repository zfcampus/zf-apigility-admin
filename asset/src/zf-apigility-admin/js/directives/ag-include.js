(function(console) { 'use strict';

angular.module('ag-admin').directive('agInclude', 
    function(AgTemplateInjector, $compile) {
        return {
            restrict: 'E',
            transclude: true,
            replace: true,
            link: function(scope, element, attr) {
                if (!attr.hasOwnProperty('src') && !attr.hasOwnProperty('condition')) {
                    console.error('ag-include requires a "src" or a "condition" attribute; none provided!');
                    return;
                }

                var src = attr.hasOwnProperty('condition') ? scope.$eval(attr.condition) : attr.src;

                AgTemplateInjector.fetchTemplate(src).then(
                    function (contents) {
                        element.html(contents);
                        $compile(element.contents())(scope);
                    }
                );
            }
        };
    }
);

})(console);
