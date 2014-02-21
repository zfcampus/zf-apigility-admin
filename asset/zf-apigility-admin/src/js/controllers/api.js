(function() {
  'use strict';

angular.module('ag-admin').controller(
  'ApiController',
  function($scope, $state, $timeout, flash, apis, ApiRepository) {
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

      ApiRepository.createNewApi($scope.apiName).then(function (newApi) {
        // reset form, repopulate, redirect to new
        $scope.dismissModal();
        $scope.resetForm();
        form.find('input').attr('disabled', false);
        form.find('button').attr('disabled', false);

        flash.success = 'New API Created';
        $timeout(function () {
          ApiRepository.getList(true).then(function(apiCollection) {
            updateList(apiCollection);
          });
          $state.go('ag.api.version', {apiName: newApi.name, version: 1});
        }, 500);
      });
    };

    $scope.resetForm = function () {
      $scope.showNewApiForm = false;
      $scope.apiName = '';
    };

    updateList(apis);
  }
);
})();

