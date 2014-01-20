(function() {'use strict';

angular.module('ag-admin').directive('agEditInplace', function() {
    return {
        restrict: 'E',
        replace: true,
        scope: {
            'agInputName': '=name'
        },
        templateUrl: 'zf-apigility-admin/dist/html/directives/ag-edit-inplace.html',
        controller: ['$scope', function($scope) {
            $scope.isFormVisible = false;
        }],
        link: function(scope, element, attr) {
            element.on('click', function(event) {
                event.stopPropagation();
            });

            var name = angular.element(element.children()[0]);
            var form = angular.element(element.children()[1]);

            scope.$watch('isFormVisible', function(newVal) {
                if (newVal) {
                    name.toggleClass('hide', true);
                    form.toggleClass('hide', false);
                    return;
                }

                name.toggleClass('hide', false);
                form.toggleClass('hide', true);
            });
        }
    };
});

})();
