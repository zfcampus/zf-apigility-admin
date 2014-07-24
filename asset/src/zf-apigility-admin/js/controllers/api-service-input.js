(function(_) {'use strict';

angular.module('ag-admin').controller('ApiServiceInputController', function ($scope, $state, $modal, flash, agFormHandler) {
    // get services from $parent
    $scope.service = (typeof $scope.$parent.restService != 'undefined') ? $scope.$parent.restService : $scope.$parent.rpcService;
    $scope.filterOptions = $scope.$parent.filterOptions;
    $scope.validatorOptions = $scope.$parent.validatorOptions;

    $scope.help = function () {
        $modal.open({
            templateUrl: 'html/modals/help-input-filter.html',
            keyboard: true
        });
    };

    $scope.addInput = function() {
        // Test first to see if we have a value
        if (!$scope.newInput || $scope.newInput === null || $scope.newInput === '' || $scope.newInput.match(/^\s+$/)) {
            flash.error = 'Must provide a field name!';
            return;
        }

        // Test to see if we already have an input by this name first
        var found = false;
        $scope.service.input_filter.every(function (input) {
            if ($scope.newInput === input.name) {
                found = true;
                return false;
            }
            return true;
        });

        if (found) {
            flash.error = 'Field by the name ' + $scope.newInput + ' already exists!';
            return;
        }

        // Add the input to the input filter
        $scope.service.input_filter.push({name: $scope.newInput, required: true, filters: [], validators: []});
        $scope.newInput = '';
    };

    $scope.validateInputName = function (name, current) {
        // Test first to see if we have a value
        if (!name || name === null || name === '' || name.match(/^\s+$/)) {
            flash.error = 'Field name can not be empty!';
            return false;
        }

        // Test to see if we already have an input by this name first
        var found = false;
        $scope.service.input_filter.every(function (input) {
            if (input === current) {
                return true;
            }
            if (name === input.name) {
                found = true;
                return false;
            }
            return true;
        });

        if (found) {
            flash.error = 'Field by the name ' + name + ' already exists!';
            return false;
        }

        return true;
    };

    $scope.removeInput = function (inputIndex) {
        $scope.service.input_filter.splice(inputIndex, 1);
    };

    $scope.removeOption = function (options, name) {
        delete options[name];
    };

    $scope.addFilter = function (input) {
        input.filters.push({name: input._newFilterName, options: {}});
        input._newFilterName = '';
    };

    $scope.removeFilter = function (input, filterIndex) {
        input.filters.splice(filterIndex, 1);
    };

    $scope.addFilterOption = function (filter) {
        if ($scope.filterOptions[filter.name][filter._newOptionName] == 'bool') {
            filter._newOptionValue = (filter._newOptionValue === true);
        }
        filter.options[filter._newOptionName] = filter._newOptionValue;
        filter._newOptionName = '';
        filter._newOptionValue = '';
    };

    $scope.addValidator = function (input) {
        input.validators.push({name: input._newValidatorName, options: {}});
        input._newValidatorName = '';
    };

    $scope.removeValidator = function (input, validatorIndex) {
        input.validators.splice(validatorIndex, 1);
    };

    $scope.addValidatorOption = function (validator) {
        if ($scope.validatorOptions[validator.name][validator._newOptionName] == 'bool') {
            validator._newOptionValue = (validator._newOptionValue === true);
        }
        validator.options[validator._newOptionName] = validator._newOptionValue;
        validator._newOptionName = '';
        validator._newOptionValue = '';
    };

    $scope.saveInput = function () {
        function removeUnderscoreProperties (value, key, collection) {
            if (typeof key == 'string' && ['_', '$'].indexOf(key.charAt(0)) != -1) {
                delete collection[key];
            } else if (value instanceof Object) {
                _.forEach(value, removeUnderscoreProperties);
            }
        }
        var modelInputFilter = _.cloneDeep($scope.service.input_filter);

        /* Trim out items that should not be submitted to the API */
        _.forEach(modelInputFilter, removeUnderscoreProperties);
        _.forEach(modelInputFilter, function (input) {
            if (input.hasOwnProperty('error_message') &&
                !input.error_message.length
            ) {
                delete input.error_message;
            }

            if (input.hasOwnProperty('showNewFilterForm')) {
                delete input.showNewFilterForm;
            }

            if (input.hasOwnProperty('showNewValidatorForm')) {
                delete input.showNewValidatorForm;
            }

            if (input.hasOwnProperty('file_upload') && input.file_upload) {
                input.type = 'Zend\\InputFilter\\FileInput';
                delete input.file_upload;
            }

            _.forEach(input.filters, function (filter) {
                if (filter.hasOwnProperty('showNewOptionForm')) {
                    delete filter.showNewOptionForm;
                }
            });

            _.forEach(input.validators, function (validator) {
                if (validator.hasOwnProperty('showNewOptionForm')) {
                    delete validator.showNewOptionForm;
                }
            });
        });

        var apiRepo = $scope.$parent.ApiRepository;
        apiRepo.saveInputFilter($scope.service, modelInputFilter).then(
            function (savedInputFilter) {
                agFormHandler.resetForm($scope);
                $scope.$parent.flash.success = 'Fields configuration saved.';
                $state.go($state.$current.name, {edit: null}, {reload: true});
            }
        ).catch(
            function (error) {
                agFormHandler.reportError(error, $scope);
            }
        );
    };
});

})(_);
