(function() {'use strict';

angular.module('ag-admin').factory(
  'ContentNegotiationResource',
  ['$http', 'flash', 'apiBasePath',
  function ($http, flash, apiBasePath) {

    var servicePath = apiBasePath + '/content-negotiation';

    return {
      getList: function () {
        return $http({method: 'GET', url: servicePath}).then(
          function success(response) {
            return response.data._embedded.selectors;
          },
          function error() {
            flash.error = 'Unable to fetch content negotiation selectors; you may need to reload the page';
          }
        );
      }
    };
  }]
);

})();
