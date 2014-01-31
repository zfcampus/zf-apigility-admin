(function() {'use strict';

angular.module('ag-admin').directive('collapse', function() {
    return {
        restrict: 'E',
        transclude: true,
        scope: {
            show: '&'
        },
        controller: ['$scope', '$parse', function($scope, $parse) {
            var head;
            var body;
            var buttons = [];
            var conditionals = {};
            var watchers = {};

            this.addButton = function(button) {
                /* Ensure we have boolean values for criteria flags */
                angular.forEach(button.criteria, function(flag, key) {
                    button.criteria[key] = !!flag;
                });
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
                        /* Trigger all watchers on this flag */
                        angular.forEach(watchers[flag], function(watcher) {
                            watcher(value);
                        });
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

                    /* cast to bool */
                    value = !!value;
                    
                    /* ensure we have an array of watchers for a given flag */
                    if (typeof watchers[flag] === 'undefined') {
                        watchers[flag] = [];
                    }

                    watchers[flag].push(function(newVal) {
                        /* cast to bool */
                        newVal = !!newVal;
                        conditionals[flag] = newVal;
                        element.toggleClass('hide', value !== newVal);
                    });
                });
            };

            $scope.showContainerButtons = this.showContainerButtons = function() {
                var criteria = false;
                angular.forEach(buttons, function(button) {
                    var currentCriteria = criteria;
                    angular.forEach(button.criteria, function(test, criteriaProp) {
                        if (! conditionals.hasOwnProperty(criteriaProp)) {
                            return;
                        }
                        currentCriteria = currentCriteria || (conditionals[criteriaProp] !== test);
                    });
                    button.element.toggleClass('hide', currentCriteria);
                });
            };

            $scope.hideContainerButtons = this.hideContainerButtons = function(state) {
                var bodyExpanded = body.hasClass('in');
                angular.forEach(buttons, function(button) {
                    if (state.hasOwnProperty('leave') && state.leave) {
                        button.element.toggleClass('hide', true);
                        return;
                    }

                    var currentCriteria = true;
                    angular.forEach(button.criteria, function(test, criteriaProp) {
                        if (!currentCriteria) {
                            return;
                        }
                        if (! conditionals.hasOwnProperty(criteriaProp)) {
                            return;
                        }
                        currentCriteria = (conditionals[criteriaProp] === test);
                    });
                    button.element.toggleClass('hide', currentCriteria);
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
        link: function(scope, element, attr) {
            if (attr.hasOwnProperty('show') && 
                typeof scope.show === 'function') {
                if (!scope.show()) {
                    element.toggleClass('hide', true);
                }
            }

            if (attr.hasOwnProperty('conditionals')) {
                scope.setConditionals(scope.$eval(attr.conditionals));
            }
        },
        template: '<div class="panel" ng-transclude></div>',
        replace: true
    };
}).directive('collapseHeader', function () {
    /* <collapse-header ...></collapse-header> */
    return {
        require: '^collapse',
        restrict: 'E',
        transclude: true,
        link: function(scope, element, attr, panelCtrl) {
            panelCtrl.setHead(scope);

            element.on('click', function(event) {
                panelCtrl.toggle();
            });

            element.on('mouseover', function(event) {
                panelCtrl.showContainerButtons();
            });

            element.on('mouseleave', function(event) {
                panelCtrl.hideContainerButtons({leave: true});
            });
        },
        template: '<div class="panel-heading" ng-transclude></div>',
        replace: true
    };
}).directive('collapseBody', function () {
    /* <collapse-body ...></collapse-body> */
    return {
        require: '^collapse',
        restrict: 'E',
        transclude: true,
        link: function(scope, element, attr, panelCtrl) {
            panelCtrl.setBody(element);
        },
        template: '<div class="panel-collapse collapse" ng-transclude></div>',
        replace: true
    };
}).directive('collapseButton', function () {
    /* <collapse-button [criteria="{...}"]>...</collapse-button> */
    return {
        require: '^collapse',
        restrict: 'A',
        link: function(scope, element, attr, panelCtrl) {
            var criteria = {};
            if (attr.hasOwnProperty('criteria')) {
                criteria = scope.$eval(attr.criteria);
                if (typeof criteria !== 'object') {
                    criteria = {};
                }
            }

            panelCtrl.addButton({criteria: criteria, element: element});

            element.addClass('hide');

            element.on('click', function(event) {
                panelCtrl.expand();
                panelCtrl.showContainerButtons();
                event.stopPropagation();
            });
        }
    };
}).directive('collapseFlag', function() {
    /* <a collapse-flag flags="{...}"></a> */
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
}).directive('collapseShow', function() {
    /* <div collapse-show criteria="{...}" class="hide">...</div> */
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

})();
