'use strict';

var agCollapse = angular.module('ag-collapse', []);

/* <collapse ...></collapse> */
agCollapse.directive('collapse', function() {
    return {
        restrict: 'E',
        transclude: true,
        scope: {},
        controller: ['$scope', function($scope) {
            var head;
            var body;
            var buttons = [];

            this.addButton = function(button) {
                buttons.push(button);
            };

            $scope.showContainerButtons = function() {
                angular.forEach(buttons, function(button) {
                    button.element.toggleClass('invisible', false);
                });
            };

            $scope.hideContainerButtons = function() {
                angular.forEach(buttons, function(button) {
                    button.element.toggleClass('invisible', true);
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

/* <collapse-button [criteria="..."]>...</collapse-button>
 * @todo arbitrary criteria
 */
agCollapse.directive('collapseButton', function () {
    return {
        require: '^collapse',
        restrict: 'E',
        transclude: true,
        scope: {
            criteria: '@'
        },
        link: function(scope, element, attrs, panelCtrl) {
            panelCtrl.addButton({scope: scope, element: element});
        },
        template: '<div class="pull-right invisible" ng-transclude></div>',
        replace: true
    };
});
