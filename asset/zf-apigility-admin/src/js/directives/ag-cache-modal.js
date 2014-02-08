(function() { 'use strict';

angular.module('ag-admin').directive('agCacheModal', 
    ['CacheEnabledResource',
    function(CacheEnabledResource) {
        var isEnabled = false;
        return {
            restrict: 'E',
            replace: true,
            templateUrl: 'zf-apigility-admin/dist/html/modals/cache-check.html',
            controller: ['$scope', function ($scope) {
                $scope.isEnabled = false;

                CacheEnabledResource.getCacheStatus().then(function (status) {
                    $scope.isEnabled = status;
                });
            }],
            link: function(scope, element, attr) {
                scope.$watch('isEnabled', function (newValue, oldValue) {
                    if (newValue) {
                        element.modal('show');
                    }
                });
            }
        };
    }]
);

})();
