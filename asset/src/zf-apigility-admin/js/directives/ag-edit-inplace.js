(function() {
    'use strict';

angular.module('ag-admin').directive('agEditInplace', function() {
    return {
        restrict: 'E',
        replace: true,
        scope: {
            'agInputName': '=name',
            validate: '&'
        },
        templateUrl: 'html/directives/ag-edit-inplace.html',
        controller: function($scope) {
            var initialValue;

            $scope.isFormVisible = false;

            $scope.setInitialValue = function (value) {
                initialValue = value;
            };

            $scope.resetForm = function () {
                $scope.agInputName = initialValue;
                $scope.isFormVisible = false;
            };
        },
        link: function(scope, element, attr) {
            element.on('click', function(event) {
                event.stopPropagation();
            });

            scope.setInitialValue(scope.agInputName);

            var name = angular.element(element.children()[0]);
            var form = angular.element(element.children()[1]);

            if (attr.hasOwnProperty('validate') &&
                typeof scope.validate === 'function') {
                scope.submit = function (event) {
                    if (scope.validate(scope.agInputName)) {
                        scope.isFormVisible = false;
                    }
                };
            } else {
                scope.submit = function (event) {
                    scope.isFormVisible = false;
                };
            }

            form.on('submit', scope.submit);

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
