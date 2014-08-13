(function() { 'use strict';

angular.module('ag-admin').directive('agConditionalInclude', 
    function(AgTemplateInjector, $compile) {
        return {
            restrict: 'E',
            transclude: true,
            link: function(scope, element, attr) {
                if (!attr.hasOwnProperty('src') || !attr.hasOwnProperty('condition')) {
                    console.error('ag-include requires both a "src" and a "condition" attribute; none provided!');
                    return;
                }

                scope.$watch(attr.condition, function (value) {
                    value = !!value;
                    var template = value ? attr.src : AgTemplateInjector.defaultEmptyTemplate;
                    AgTemplateInjector.fetchTemplate(template).then(
                        function (contents) {
                            element.html(contents);
                            $compile(element.contents())(scope);
                        }
                    );
                });
            }
        };
    }
);

})();
