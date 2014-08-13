(function() {
  'use strict';

angular.module('ag-admin').controller(
  'CreateApiController',
  function($scope, $modalInstance, $rootScope, $state, $timeout, flash, ApiRepository, agFormHandler) {
    $scope.apiName = '';

    var resetForm = function () {
      agFormHandler.resetForm($scope);
      $scope.apiName = '';
    };
    
    $scope.createNewApi = function ($event) {
      var form = angular.element($event.target);
      form.find('input').attr('disabled', true);
      form.find('button').attr('disabled', true);
      form.find('.ag-validation-error').remove();

      /* Due to scoping issues in $modal, have to pull the
       * apiName from the $$childTail scope.
       */
      ApiRepository.createNewApi($scope.$$childTail.apiName).then(
        function (newApi) {
          // reset form, repopulate, redirect to new
          $modalInstance.close(newApi);
          resetForm();

          flash.success = 'New API is being created... Please wait';

          /* Angular has no way to emit to sibling controllers; use the
           * $rootScope to broadcast downwards instead.
           */
          $timeout(function () {
            $rootScope.$broadcast('api.updateList');
            $state.go('ag.api.version', {apiName: newApi.name, version: 1});
          }, 2000);
        }
      ).catch(
        function (error) {
          agFormHandler.reportError(error, $scope);
        }
      );
    };

    $scope.cancel = function() {
      resetForm();
      $modalInstance.dismiss();
    };
  }
);
})();
