(function() {
  'use strict';

angular.module('ag-admin').controller(
  'ApiController',
  function($scope, $state, flash, apis, ApiRepository) {
    $scope.showNewApiForm = false;
    $scope.apiList = [];

    var stateName = $state.current.name;
    var updateList = function (apiCollection) {
      var apiList = [];
      angular.forEach(apiCollection, function (api) {
        apiList.push({
          apiName: api.name,
          version: api.versions.pop()
        });
      });
      $scope.apiList = apiList;
    };

    $scope.createNewApi = function ($event) {
      var form = angular.element($event.target);
      form.find('input').attr('disabled', true);
      form.find('button').attr('disabled', true);
      form.find('.ag-validation-error').remove();

      ApiRepository.createNewApi($scope.apiName).then(
        function (newApi) {
          // reset form, repopulate, redirect to new
          $scope.dismissModal();
          $scope.$broadcast('ag-form-submit-complete');
          $scope.resetForm();

          flash.success = 'New API Created';

          ApiRepository.getList(true).then(function (apiCollection) {
            updateList(apiCollection);
            $state.go('ag.api.version', {apiName: newApi.name, version: 1});
          });
        },
        function (error) {
          $scope.$broadcast('ag-form-submit-complete');

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

          $scope.$broadcast('ag-form-validation-errors', validationErrors);
          flash.error = 'We were unable to validate your form; please check for errors.';
        }
      );
    };

    $scope.resetForm = function () {
      $scope.showNewApiForm = false;
      $scope.apiName = '';
      $scope.$broadcast('ag-form-validation-errors-clear');
    };

    updateList(apis);
  }
);
})();

