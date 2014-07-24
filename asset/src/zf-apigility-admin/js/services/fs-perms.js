(function() {'use strict';

angular.module('ag-admin').factory(
  'FsPermsResource',
  ['$http', 'flash', 'apiBasePath',
  function ($http, flash, apiBasePath) {

    var servicePath = apiBasePath + '/fs-permissions';

    return {
      getFsPermsStatus: function () {
        return $http({method: 'GET', url: servicePath}).then(
          function (response) {
            return response.data;
          }
        ).catch(
          function () {
            flash.error = 'Unable to fetch API filesystem writability status; you may need to refresh the page.';
          }
        );
      }
    };
  }]
);

})();
