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
        controller: function($scope, $element, $state) {
            var panes = $scope.panes = [];

            $scope.select = function(pane) {
                angular.forEach(panes, function(pane) {
                    if (pane.selected === true) {
                        pane.selected = false;
                        angular.forEach(pane.getWatchers(), function (watcher) {
                            watcher.deselect(pane);
                        });
                    }
                });

                pane.selected = true;
                angular.forEach(pane.getWatchers(), function (watcher) {
                    watcher.select(pane);
                });

                if (pane.name && pane.searchParam) {
                    var toParams = {};
                    toParams[pane.searchParam] = pane.name;
                    $state.go($state.$current.name, toParams);
                }
            };

            this.addPane = function(pane) {
                if (panes.length === 0 || pane.active) {
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
        controller: function ($scope) {
            var watchers = [];
            
            /* controller method so child scopes can call it */
            this.addWatcher = function (watcher) {
                watchers.push(watcher);
            };

            /* Scoped so that the tab controller can call it */
            $scope.getWatchers = function () {
                return watchers;
            };
        },
        link: function(scope, element, attr, tabsCtrl) {

            if (attr.hasOwnProperty('active')) {
              scope.active = !!scope.$eval(attr.active);
            }

            if (attr.hasOwnProperty('name')) {
              scope.name = attr.name;
            }

            if (attr.hasOwnProperty('searchparam')) {
              scope.searchParam = attr.searchparam;
            }

            tabsCtrl.addPane(scope);
        },
        template:
        '<div class="tab-pane" ng-class="{active: selected}" ng-transclude>' +
        '</div>',
        replace: true
    };
}).directive('agTabPaneVariableContent', function (AgTemplateInjector) {
    /* <ag-tab-pane-variable-content [empty-template="expression"]
     * content-template="expession"></ag-tab-pane-variable-content> */
    return {
        require: '^agTabPane',
        restrict: 'E',
        transclude: true,
        link: function (scope, element, attr, tab) {
            var emptyTemplate = AgTemplateInjector.defaultEmptyTemplate;
            var contentTemplate;
            var onloadExpr;

            /* content-template property is required */
            if (! attr.hasOwnProperty('contentTemplate')) {
                console.error('Missing content-template property in ag-tab-pane-variable-content directive; cannot continue');
                return;
            }
            contentTemplate = scope.$eval(attr.contentTemplate);

            /* Retrieve and evaluate empty-template property if present */
            if (attr.hasOwnProperty('emptyTemplate')) {
                emptyTemplate = scope.$eval(attr.emptyTemplate);
            }

            /* Retrieve the onload expression, if any */
            if (attr.hasOwnProperty('onload')) {
                onloadExpr = attr.onload;
            }

            /* Set the contents to the empty template to begin */
            AgTemplateInjector.fetchTemplate(emptyTemplate).then(
                function (contents) {
                    AgTemplateInjector.populateElement(element, contents, scope);
                }
            );

            /* Define the select method for the scope */
            scope.select = function (scope) {
                AgTemplateInjector.fetchTemplate(contentTemplate).then(
                    function (contents) {
                        AgTemplateInjector.populateElement(element, contents, scope, onloadExpr);
                    }
                );
            };

            /* Define the deselect method for the scope */
            scope.deselect = function (scope) {
                AgTemplateInjector.fetchTemplate(emptyTemplate).then(
                    function (contents) {
                        AgTemplateInjector.populateElement(element, contents, scope);
                    }
                );
            };

            /* Add the scope as a watch on the tab pane */
            tab.addWatcher(scope);
        }
    };
});

})();
