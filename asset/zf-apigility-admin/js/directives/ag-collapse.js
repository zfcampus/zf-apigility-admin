'use strict';

var agCollapse = angular.module('ag-collapse', []);

/* <collapse ...></collapse> */
agCollapse.directive('collapse', function() {
    return {
        restrict: 'E',
        transclude: true,
        scope: {},
        controller: function($scope, $element) {
            var head;
            var body;

            this.setHead = function (headScope) {
                head = headScope;
            };

            this.setBody = function (bodyScope) {
                body = bodyScope;
            };

            this.expand = function() {
                body.expand = true;
            };

            this.collapse = function() {
                body.expand = false;
            };

            this.toggle = function() {
                body.expand = !body.expand;
            };
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
        controller: function($scope, $element) {
            $scope.toggle = function() {
                $scope.panel.toggle();
            };
        },
        link: function(scope, element, attrs, panelCtrl) {
            scope.panel = panelCtrl;
            panelCtrl.setHead(scope);
        },
        template: '<div class="panel-heading" ng-click="toggle()" ng-mouseover="showContainerButtons = true" ng-mouseleave="showContainerButtons = false" ng-transclude></div>',
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
        controller: function($scope, $element) {
            $scope.expand = false;
        },
        link: function(scope, element, attrs, panelCtrl) {
            panelCtrl.setBody(scope);
        },
    template: '<div ng-class="{\'panel-collapse\': \'panel-collapse\', collapse: \'collapse\', in: expand}" ng-mouseover="showContainerButtons = true" ng-mouseleave="showContainerButtons = false"><div class="panel-body" ng-transclude></div></div>',
        replace: true
    };
});
