(function() {'use strict';

angular.module('ag-admin').controller(
    'ApiListController',
    function($rootScope, $scope, ApiRepository) {

        $scope.apis = [];

        $scope.refreshApiList = function () {
            ApiRepository.getList(true).then(function (apis) { $scope.apis = apis; });
        };

        $rootScope.$on('refreshApiList', function () { $scope.refreshApiList(); });
    }
);
})();
