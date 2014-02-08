(function() {
    'use strict';
/**
 * Borrowed from http://errietta.me/blog/bootstrap-angularjs-directives/
 */

/* <ag-tabs [parent="..."] ...>[<ag-tab-pane ...></ag-tab-pane>]</ag-tabs> */
angular.module('ag-admin').directive('agTabs', function() {
    return {
        restrict: 'E',
        transclude: true,
        scope: {
            parent: '='
        },
        controller: function($scope, $element) {
            var panes = $scope.panes = [];

            $scope.select = function(pane) {
                angular.forEach(panes, function(pane) {
                    pane.selected = false;
                });

                pane.selected = true;
            };

            this.addPane = function(pane) {
                if (panes.length === 0) {
                    $scope.select(pane);
                }

                panes.push(pane);
            };
        },
        link: function (scope, element, attr) {
            var tabType = 'nav-tabs';
            if (attr.hasOwnProperty('pills')) {
                tabType = 'nav-pills';
            }
            angular.forEach(element.children(), function (child) {
                child = angular.element(child);
                if (child.context.tagName !== 'UL') {
                    return;
                }
                child.addClass(tabType);
            });
        },
        template: '<div class="ag-tabs">' +
            '<ul class="nav">' +
            '<li ng-repeat="pane in panes" ng-class="{active:pane.selected}">'+
            '<a href="" ng-click="select(pane)">{{pane.title}}</a>' +
            '</li>' +
            '</ul>' +
            '<div class="tab-content" ng-transclude></div>' +
            '</div>',
        replace: true
    };
}).directive('agTabPane', function() {
    /* <ag-tab-pane ...></ag-tab-pane> */
    return {
        require: '^agTabs',
        restrict: 'E',
        transclude: true,
        scope: { title: '@' },
        link: function(scope, element, attrs, tabsCtrl) {
            tabsCtrl.addPane(scope);
        },
        template:
        '<div class="tab-pane" ng-class="{active: selected}" ng-transclude>' +
        '</div>',
        replace: true
    };
});

})();
