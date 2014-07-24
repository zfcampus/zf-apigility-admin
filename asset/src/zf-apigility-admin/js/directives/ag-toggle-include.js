(function() { 'use strict';

angular.module('ag-admin').directive('agToggleInclude', 
    function(AgTemplateInjector, $compile) {
        return {
            restrict: 'E',
            transclude: true,
            link: function(scope, element, attr) {
                if (!attr.hasOwnProperty('onTemplate') ||
                    !attr.hasOwnProperty('offTemplate') ||
                    !attr.hasOwnProperty('condition')) {
                    console.error('ag-toggle-include requires each of "condition", "on-template", and "off-template" attributes; one or more are missing!');
                    return;
                }

                scope.$watch(attr.condition, function (value) {
                    value = !!value;
                    var template = value ? attr.onTemplate : attr.offTemplate;
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
