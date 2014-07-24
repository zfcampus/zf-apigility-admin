(function() {
    'use strict';

angular.module('ag-admin').directive('agCollapse', function() {
    return {
        restrict: 'E',
        transclude: true,
        scope: {
            show: '&'
        },
        controller: function($scope, $parse, $state) {
            var active = false;
            var body;
            var bodyDisplayCallback;
            var buttons = [];
            var chevron;
            var conditionals = {};
            var head;
            var name;
            this.noChevron = false;
            var panel = this;
            var searchParam;
            var watchers = {};

            this.addButton = function(button) {
                /* Ensure we have boolean values for criteria flags */
                angular.forEach(button.criteria, function(flag, key) {
                    button.criteria[key] = !!flag;
                });
                buttons.push(button);
            };

            $scope.setActive = function() {
                if (body) {
                    panel.expand();
                }
            };

            $scope.setConditionals = function(newConditionals) {
                angular.forEach(newConditionals, function(value, key) {
                    conditionals[key] = !!value;
                });
                panel.setFlags(conditionals);
            };

            $scope.setName = function(panelName) {
                name = panelName;
            };

            $scope.setNoChevron = function(flag) {
                panel.noChevron = !!flag;
                if (chevron) {
                    chevron.remove();
                }
                panel.expand();
            };

            $scope.setSearchParam = function(panelSearchParam) {
                searchParam = panelSearchParam;
            };

            this.setFlags = function(flags) {
                angular.forEach(flags, function(value, flag) {
                    if (watchers.hasOwnProperty(flag)) {
                        /* Trigger all watchers on this flag */
                        angular.forEach(watchers[flag], function(watcher) {
                        /* Trigger all watchers on this flag */
                            watcher(value);
                        });
                    } else {
                        conditionals[flag] = !!value;
                    }
                });
            };

            this.addConditionalWatcher = function(conditionalCriteria, displayCallback) {
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
                        if (typeof displayCallback === 'function') {
                            displayCallback(newVal, value);
                        }
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

            this.setBody = function (bodyElement, displayCallback) {
                body = bodyElement;
                bodyDisplayCallback = displayCallback;

                if (body.hasClass('in')) {
                    panel.toggleChevron('up');
                }

                $scope.$watch(function () {
                    return body.attr('class');
                }, function (newClass) {
                    if (body.hasClass('in')) {
                        panel.toggleChevron('up');
                    } else {
                        panel.toggleChevron('down');
                    }
                });
            };

            this.setChevron = function (chevronElement) {
                chevron = chevronElement;
            };

            this.expand = function(completionAction) {
                if (body.hasClass('in')) {
                    /* nothing to do */
                    if (typeof completionAction === 'function') {
                        completionAction();
                    }
                    return;
                }

                bodyDisplayCallback(true);

                if (name && searchParam) {
                    var toParams = {};
                    toParams[searchParam] = name;
                    $state.go($state.$current.name, toParams, {reload: false, inherit: true, notify: false}).then(
                        function (success) {
                            if (typeof completionAction === 'function') {
                                completionAction();
                            }
                            return success;
                        }
                    );
                }
            };

            this.collapse = function(completionAction) {
                if (! body.hasClass('in')) {
                    /* nothing to do */
                    if (typeof completionAction === 'function') {
                        completionAction();
                    }
                    return;
                }

                if (panel.noChevron) {
                    return;
                }

                bodyDisplayCallback(false);

                if (searchParam) {
                    var toParams = {};
                    toParams[searchParam] = null;
                    $state.go($state.$current.name, toParams, {reload: false, inherit: true, notify: false}).then(
                        function (success) {
                            if (typeof completionAction === 'function') {
                                completionAction();
                            }
                            return success;
                        }
                    );
                }
            };

            this.toggle = function() {
                /* Doing this way to ensure location gets updated */
                if (body.hasClass('in')) {
                    panel.collapse();
                } else {
                    panel.expand();
                }

                panel.toggleChevron();
            };

            this.toggleChevron = function (flag) {
                if (panel.noChevron || !chevron) {
                    return;
                }

                if (typeof flag === 'undefined' || flag === null) {
                    if (body.hasClass('in')) {
                        flag = 'up';
                    } else {
                        flag = 'down';
                    }
                }

                var start = (flag === 'up')  ? 'down' : 'up';
                var end   = (start === 'up') ? 'down' : 'up';

                var startClass = 'glyphicon-chevron-' + start;
                var endClass   = 'glyphicon-chevron-' + end;

                if (chevron.hasClass(startClass)) {
                    chevron.removeClass(startClass);
                    chevron.addClass(endClass);
                }
            };
        },
        link: function(scope, element, attr) {
            if (attr.hasOwnProperty('show') && 
                typeof scope.show === 'function') {
                if (!scope.show()) {
                    element.toggleClass('hide', true);
                }
            }

            if (attr.hasOwnProperty('active')) {
                if (!!scope.$eval(attr.active)) {
                    scope.setActive();
                }
            }

            if (attr.hasOwnProperty('name')) {
                scope.setName(attr.name);
            }

            if (attr.hasOwnProperty('searchparam')) {
                scope.setSearchParam(attr.searchparam);
            }

            if (attr.hasOwnProperty('conditionals')) {
                scope.setConditionals(scope.$eval(attr.conditionals));
            }

            if (attr.hasOwnProperty('noChevron')) {
                scope.setNoChevron(true);
            }
        },
        template: '<div class="panel" ng-transclude></div>',
        replace: true
    };
}).directive('collapseHeader', function () {
    /* <collapse-header ...></collapse-header> */
    return {
        require: '^agCollapse',
        restrict: 'E',
        transclude: true,
        link: function(scope, element, attr, panelCtrl) {
            panelCtrl.setHead(scope);

            if (!panelCtrl.noChevron) {
                var chevron = angular.element('<i class="glyphicon glyphicon-chevron-down"></i>');
                var chevronWrapper = angular.element('<div class="ag-chevron pull-right"></div>');
                chevronWrapper.append(chevron);
                element.prepend(chevronWrapper);
                panelCtrl.setChevron(chevron);
            }

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
}).directive('collapseBody', function (AgTemplateInjector) {
    /* <collapse-body ...></collapse-body> */
    return {
        require: '^agCollapse',
        restrict: 'E',
        transclude: true,
        scope: true,
        link: function(scope, element, attr, panelCtrl) {
            var displayCallback = function (flag) {
                element.toggleClass('in', flag);
            };

            if (attr.hasOwnProperty('contentTemplate')) {
                /* template-driven; get templates */
                var contentTemplate = scope.$eval(attr.contentTemplate);
                var emptyTemplate   = AgTemplateInjector.defaultEmptyTemplate;
                if (attr.hasOwnProperty('emptyTemplate')) {
                    emptyTemplate = scope.$eval(attr.emptyTemplate);
                }

                /* create display callback */
                displayCallback = function (flag) {
                    var template = (flag) ? contentTemplate : emptyTemplate;
                    AgTemplateInjector.fetchTemplate(template).then(
                        function (contents) {
                            AgTemplateInjector.populateElement(element, contents, scope);
                            element.toggleClass('in', flag);
                        }
                    );
                };

                /* render default content */
                var template = element.hasClass('in') ? contentTemplate : emptyTemplate;
                AgTemplateInjector.fetchTemplate(template).then(
                    function (contents) {
                        AgTemplateInjector.populateElement(element, contents, scope);
                    }
                );
            }

            panelCtrl.setBody(element, displayCallback);
        },
        template: '<div class="panel-collapse collapse" ng-transclude></div>',
        replace: true
    };
}).directive('collapseButton', function () {
    /* <collapse-button [criteria="{...}"]>...</collapse-button> */
    return {
        require: '^agCollapse',
        restrict: 'A',
        scope: true,
        link: function(scope, element, attr, panelCtrl) {
            var clickAction;
            var criteria = {};

            if (attr.hasOwnProperty('criteria')) {
                criteria = scope.$eval(attr.criteria);
                if (typeof criteria !== 'object') {
                    criteria = {};
                }
            }

            if (attr.hasOwnProperty('collapseClick')) {
                clickAction = scope.$eval(attr.collapseClick);
            }

            panelCtrl.addButton({criteria: criteria, element: element});

            element.addClass('hide');

            element.on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                panelCtrl.expand(function () {
                    if (typeof clickAction === 'function') {
                        clickAction(event, element);
                    }
                });
                panelCtrl.showContainerButtons();
            });
        }
    };
}).directive('collapseFlag', function() {
    /* <a collapse-flag flags="{...}"></a> */
    return {
        require: '^agCollapse',
        restrict: 'A',
        scope: true,
        link: function(scope, element, attr, panelCtrl) {
            var clickAction;

            if (!attr.hasOwnProperty('flags')) {
                return;
            }

            var flags = scope.$eval(attr.flags);

            if (typeof flags !== 'object') {
                return;
            }

            if (attr.hasOwnProperty('collapseClick')) {
                clickAction = scope.$eval(attr.collapseClick);
            }

            element.on('click', function(event) {
                event.preventDefault();
                event.stopPropagation();
                panelCtrl.setFlags(flags);
                if (typeof clickAction === 'function') {
                    clickAction(event, element);
                }
            });
        }
    };
}).directive('collapseShow', function(AgTemplateInjector) {
    /* <div collapse-show criteria="{...}" [default-template="expr" toggled-template="expr"] class="hide">...</div> */
    return {
        require: '^agCollapse',
        restrict: 'A',
        transclude: true,
        link: function(scope, element, attr, panelCtrl) {
            var displayCallback = function (flag, compare) {
                element.toggleClass('hide', compare !== flag);
            };

            if (!attr.hasOwnProperty('criteria')) {
                return;
            }

            var criteria = scope.$eval(attr.criteria);

            if (typeof criteria !== 'object') {
                return;
            }

            if (attr.hasOwnProperty('defaultTemplate') && attr.hasOwnProperty('toggledTemplate')) {
                /* template-driven; get templates */
                var defaultTemplate = scope.$eval(attr.defaultTemplate);
                var toggledTemplate = scope.$eval(attr.toggledTemplate);

                /* create display callback */
                displayCallback = function (flag, compare) {
                    var template = (flag === compare) ? defaultTemplate : toggledTemplate;
                    AgTemplateInjector.fetchTemplate(template).then(
                        function (contents) {
                            AgTemplateInjector.populateElement(element, contents, scope);
                        }
                    );
                };

                /* render default content */
                AgTemplateInjector.fetchTemplate(defaultTemplate).then(
                    function (contents) {
                        AgTemplateInjector.populateElement(element, contents, scope);
                    }
                );
            }

            panelCtrl.addConditionalWatcher(criteria, displayCallback);
        }
    };
});

})();
