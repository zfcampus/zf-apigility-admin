(function() {
  'use strict';

angular.module('ag-admin').controller(
  'ApiController',
  function($scope, ApiRepository) {
    $scope.apiList = [];

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

    $scope.$on('api.updateList', function () {
      ApiRepository.getList(true).then(function (apiCollection) {
        updateList(apiCollection);
      });
    });

    ApiRepository.getList().then(function (apiCollection) {
      updateList(apiCollection);
    });
  }
);
})();
