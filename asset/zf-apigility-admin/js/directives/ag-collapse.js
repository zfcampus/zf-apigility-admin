'use strict';

var agCollapse = angular.module('ag-collapse', []);

/* <collapse ...></collapse> */
agCollapse.directive('collapse', function() {
    return {
        restrict: 'E',
        transclude: true,
        scope: {
            buttonCriteria: '='
        },
        controller: ['$scope', '$parse', function($scope, $parse) {
            var head;
            var body;
            var buttons = [];

            angular.forEach($scope.buttonCriteria, function(value, key) {
                if ($scope.hasOwnProperty(key)) {
                    // do no overwrite existing properties
                    return;
                }
                $scope[key] = value;
            });

            this.addButton = function(button) {
                buttons.push(button);
            };

            $scope.showContainerButtons = function() {
                var criteria = false;
                angular.forEach(buttons, function(button) {
                    var buttonCriteria = criteria;
                    angular.forEach(button.scope.criteria, function(criteriaProp) {
                        if (! $scope.hasOwnProperty(criteriaProp)) {
                            return;
                        }
                        buttonCriteria = buttonCriteria || !!$scope[criteriaProp];
                    });
                    button.element.toggleClass('invisible', buttonCriteria);
                });
            };

            $scope.hideContainerButtons = function() {
                angular.forEach(buttons, function(button) {
                    var buttonCriteria = true;
                    angular.forEach(button.scope.criteria, function(criteriaProp) {
                        if (!buttonCriteria) {
                            return;
                        }
                        if (! $scope.hasOwnProperty(criteriaProp)) {
                            return;
                        }
                        /* !! to cast to boolean, ! to negate */
                        buttonCriteria = !!!$scope[criteriaProp];
                    });
                    button.element.toggleClass('invisible', buttonCriteria);
                });
            };

            this.setHead = function (headScope) {
                head = headScope;
            };

            this.setBody = function (bodyElement) {
                body = bodyElement;
            };

            this.expand = function() {
                body.addClass('in');
            };

            this.collapse = function() {
                body.removeClass('in');
            };

            this.toggle = function() {
                body.toggleClass('in');
            };
        }],
        link: function(scope, element, attrs) {
            element.on('mouseover', function(event) {
                scope.showContainerButtons();
            });

            element.on('mouseleave', function(event) {
                scope.hideContainerButtons();
            });
        },
        template: '<div class="panel panel-default" ng-transclude></div>',
        replace: true
    };
});

/* <collapse-header ...></collapse-header>
 * Should add itself to the collapse-panel
 * Should get a reference to the collapse-panel, so that it can tell the panel
 * to toggle the body.
 */
agCollapse.directive('collapseHeader', function () {
    return {
        require: '^collapse',
        restrict: 'E',
        transclude: true,
        scope: {},
        link: function(scope, element, attrs, panelCtrl) {
            panelCtrl.setHead(scope);

            element.on('click', function(event) {
                panelCtrl.toggle();
            });
        },
        template: '<div class="panel-heading" ng-transclude></div>',
        replace: true
    };
});

/* <collapse-body ...></collapse-body>
 * Should add itself to the collapse-panel
 */
agCollapse.directive('collapseBody', function () {
    return {
        require: '^collapse',
        restrict: 'E',
        transclude: true,
        scope: {},
        link: function(scope, element, attrs, panelCtrl) {
            panelCtrl.setBody(element);
        },
        template: '<div class="panel-collapse collapse"><div class="panel-body" ng-transclude></div></div>',
        replace: true
    };
});

/* <collapse-button [criteria="['prop1', ...]"]>...</collapse-button>
 */
agCollapse.directive('collapseButton', function () {
    return {
        require: '^collapse',
        restrict: 'E',
        transclude: true,
        scope: {
            criteria: '='
        },
        controller: ['$scope', function($scope) {
            if (!$scope.criteria || ! $scope.criteria instanceof Array) {
                $scope.criteria = [];
            }
        }],
        link: function(scope, element, attrs, panelCtrl) {
            panelCtrl.addButton({scope: scope, element: element});

            element.on('click', function(event) {
                event.stopPropagation();
            });
        },
        template: '<div class="pull-right invisible" ng-transclude></div>',
        replace: true
    };
});
