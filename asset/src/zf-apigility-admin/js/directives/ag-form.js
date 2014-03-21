(function() { 'use strict';

angular.module('ag-admin').directive('agForm', 
    function() {
        return {
            restrict: 'A',
            controller: ['$scope', function ($scope) {

                var createPanel = function () {
                    var panel = angular.element('<div></div>');
                    panel.addClass('panel panel-danger ag-validation-error');

                    var header = angular.element('<div></div>');
                    header.addClass('panel-heading');

                    var title = angular.element('<h4></h4>');
                    title.addClass('panel-title');
                    title.text('Validation Errors');

                    header.append(title);
                    panel.append(header);
                    return panel;
                };

                $scope.processErrors = function (errors, form) {
                    var messagePanel = createPanel();
                    var list = angular.element('<ul></ul>');
                    list.addClass('list-group');

                    angular.forEach(errors, function (value, key) {
                        if (Array.isArray(value)) {
                            value = value.join('<br />');
                        }

                        if (typeof value === 'object') {
                            var messages = [];
                            angular.forEach(value, function (m, k) {
                                messages.push(m);
                            });
                            value = messages.join('<br />');
                        }

                        if (typeof value !== 'string') {
                            return;
                        }

                        if (typeof key === 'string') {
                            value = key + ': ' + value;
                        }

                        var item = angular.element('<li></li>');
                        item.addClass('list-group-item');
                        item.text(value);
                        list.append(item);
                    });

                    messagePanel.append(list);
                    form.append(messagePanel);
                };

                $scope.removeErrors = function (form) {
                    form.find('.ag-validation-error').remove();
                };
            }],
            link: function(scope, form, attr) {
                /* Listen to DOM submit event, and disable form elements */
                form.on('submit', function (event) {
                    scope.removeErrors(form);
                    form.find('input').attr('disabled', true);
                    form.find('button').attr('disabled', true);
                    form.find('select').attr('disabled', true);
                });

                /* Listen to "ag-form-submit-complete" event, and re-enable form
                 * elements */
                scope.$on('ag-form-submit-complete', function () {
                    form.find('input').attr('disabled', false);
                    form.find('button').attr('disabled', false);
                    form.find('select').attr('disabled', false);
                });

                /* Listen to "ag-form-validation-errors" event, and list errors
                 * passed */
                scope.$on('ag-form-validation-errors', function (event, errors) {
                    scope.messages = scope.processErrors(errors, form);
                });

                /* Listen to "ag-form-validation-errors-clear" event, and remove
                 * any validation error messages */
                scope.$on('ag-form-validation-errors-clear', function () {
                    form.find('.ag-validation-error').remove();
                });
            }
        };
    }
);

})();
