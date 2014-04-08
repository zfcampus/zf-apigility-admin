(function() {
  'use strict';

angular.module('ag-admin').controller(
  'CreateApiButtonController',
  function($scope, $modal) {
    $scope.dialog = function() {
      $modal.open({
        templateUrl: 'html/modals/create-api-form.html',
        keyboard: true,
        controller: 'CreateApiController'
      });
    };
  }
);
})();
