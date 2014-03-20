(function() {'use strict';

angular.module('ag-admin').factory('agFormHandler', function (flash) {
    return {
        resetForm: function (scope) {
            scope.$broadcast('ag-form-submit-complete');
            scope.$broadcast('ag-form-validation-errors-clear');
        },
        reportError: function (error, scope) {
            scope.$broadcast('ag-form-submit-complete');

            if (error.status !== 400 && error.status !== 422) {
                /* generic, non-validation related error! */
                flash.error = 'Error submitting new API';
                return;
            }

            var validationErrors;

            if (error.status === 400) {
                validationErrors = [ 'Unexpected or missing data processing form' ];
            } else {
                validationErrors = error.data.validation_messages;
            }

            scope.$broadcast('ag-form-validation-errors', validationErrors);
            flash.error = 'We were unable to validate your form; please check for errors.';
        }
    };
});

})(_);
