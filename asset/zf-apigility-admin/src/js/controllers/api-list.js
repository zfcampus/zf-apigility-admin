(function() {'use strict';

angular.module('ag-admin').controller(
    'ApiListController',
    ['$rootScope', '$scope', '$location', '$timeout', 'flash', 'ApiRepository', function($rootScope, $scope, $location, $timeout, flash, ApiRepository) {

        $scope.apis = [];
        $scope.showNewApiForm = false;

        $scope.refreshApiList = function () {
            ApiRepository.getList(true).then(function (apis) { $scope.apis = apis; });
        };

        $scope.createNewApi = function ($event) {
            var form = angular.element($event.target);
            form.find('input').attr('disabled', true);
            form.find('button').attr('disabled', true);

            ApiRepository.createNewApi($scope.apiName).then(function (newApi) {
                // reset form, repopulate, redirect to new
                $scope.dismissModal();
                $scope.resetForm();
                $scope.refreshApiList();

                flash.success = 'New API Created';
                $timeout(function () {
                    $location.path('/api/' + newApi.name + '/v1/overview');
                }, 500);
            });
        };

        $scope.resetForm = function () {
            $scope.showNewApiForm = false;
            $scope.apiName = '';
        };

        $rootScope.$on('refreshApiList', function () { $scope.refreshApiList(); });
    }]
);
})();
