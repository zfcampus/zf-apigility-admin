'use strict';

var agCollapse = angular.module('ag-collapse', []);

/* <collapse ...></collapse> */
agCollapse.directive('collapse', function() {
    return {
        restrict: 'E',
        transclude: true,
        controller: ['$scope', '$parse', function($scope, $parse) {
            var head;
            var body;
            var buttons = [];
            var conditionals = {};
            var watchers = {};

            this.addButton = function(button) {
                buttons.push(button);
            };

            $scope.setConditionals = function(newConditionals) {
                angular.forEach(newConditionals, function(value, key) {
                    conditionals[key] = !!value;
                });
            };

            this.setFlags = function(flags) {
                angular.forEach(flags, function(value, flag) {
                    if (watchers.hasOwnProperty(flag)) {
                        var watcher = watchers[flag];
                        watcher(value);
                    } else {
                        conditionals[flag] = !!value;
                    }
                });
            };

            this.addConditionalWatcher = function(conditionalCriteria, element) {
                angular.forEach(conditionalCriteria, function(value, flag) {
                    if (!conditionals.hasOwnProperty(flag)) {
                        conditionals[flag] = false;
                    }

                    value = !!value;
                    
                    watchers[flag] = function(newVal) {
                        newVal = !!newVal;
                        if (conditionals[flag] === newVal) {
                            return;
                        }
                        conditionals[flag] = newVal;
                        element.toggleClass('hide', value !== newVal);
                    };
                });
            };

            $scope.showContainerButtons = function() {
                var criteria = false;
                angular.forEach(buttons, function(button) {
                    var currentCriteria = criteria;
                    angular.forEach(button.scope.criteria, function(criteriaProp) {
                        if (! conditionals.hasOwnProperty(criteriaProp)) {
                            return;
                        }
                        currentCriteria = currentCriteria || !!conditionals[criteriaProp];
                    });
                    button.element.toggleClass('invisible', currentCriteria);
                });
            };

            $scope.hideContainerButtons = function() {
                angular.forEach(buttons, function(button) {
                    var currentCriteria = true;
                    angular.forEach(button.scope.criteria, function(criteriaProp) {
                        if (!currentCriteria) {
                            return;
                        }
                        if (! conditionals.hasOwnProperty(criteriaProp)) {
                            return;
                        }
                        /* !! to cast to boolean, ! to negate */
                        currentCriteria = !!!conditionals[criteriaProp];
                    });
                    button.element.toggleClass('invisible', currentCriteria);
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
console.log("Linking collase");
            if (attrs.hasOwnProperty('conditionals')) {
console.log("Setting conditionals from attribute");
                scope.setConditionals(scope.$eval(attrs.conditionals));
            }

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

agCollapse.directive('agFlag', function() {
    return {
        require: '^collapse',
        restrict: 'A',
        link: function(scope, element, attr, panelCtrl) {
            if (!attr.hasOwnProperty('flags')) {
                return;
            }

            var flags = scope.$eval(attr.flags);

            if (typeof flags !== 'object') {
                return;
            }

            element.on('click', function(event) {
                panelCtrl.setFlags(flags);
            });
        }
    };
});

agCollapse.directive('agShow', function() {
    return {
        require: '^collapse',
        restrict: 'A',
        link: function(scope, element, attr, panelCtrl) {
            if (!attr.hasOwnProperty('criteria')) {
                return;
            }

            var criteria = scope.$eval(attr.criteria);

            if (typeof criteria !== 'object') {
                return;
            }

            panelCtrl.addConditionalWatcher(criteria, element);
        }
    };
});
