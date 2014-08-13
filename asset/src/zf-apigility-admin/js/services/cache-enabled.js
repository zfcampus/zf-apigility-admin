(function() {'use strict';

angular.module('ag-admin').factory(
  'CacheEnabledResource',
  ['$http', 'flash', 'apiBasePath',
  function ($http, flash, apiBasePath) {

    var servicePath = apiBasePath + '/cache-enabled';

    return {
      getCacheStatus: function () {
        return $http({method: 'GET', url: servicePath}).then(
          function (response) {
            return response.data.cache_enabled;
          }
        ).catch(
          function () {
            flash.error = 'Unable to fetch API opcode cache status; you may need to refresh the page.';
          }
        );
      }
    };
  }]
);

})();
