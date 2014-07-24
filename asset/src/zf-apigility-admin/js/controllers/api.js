(function() {
  'use strict';

angular.module('ag-admin').controller(
  'ApiController',
  function($scope, ApiRepository) {
    $scope.apiList = [];

    var updateList = function (apiCollection) {
      var apiList = [];
      angular.forEach(apiCollection, function (api) {
        var version = api.default_version;
        if (api.versions.length > 0) {
          version = api.versions.pop();
        }
        apiList.push({
          apiName: api.name,
          version: version
        });
      });
      $scope.apiList = apiList;
    };

    $scope.$on('api.updateList', function () {
      ApiRepository.getList(true).then(
          function (apiCollection) {
            updateList(apiCollection);
        }
    );
    });

    ApiRepository.getList().then(
        function (apiCollection) {
            updateList(apiCollection);
        }
    );
  }
);
})();
