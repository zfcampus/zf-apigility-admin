(function() { 'use strict';

angular.module('ag-admin').directive('agFsPermsModal', 
    ['FsPermsResource',
    function(FsPermsResource) {
        var isEnabled = false;
        return {
            restrict: 'E',
            replace: true,
            templateUrl: 'html/modals/fs-perms.html',
            controller: ['$scope', function ($scope) {
                $scope.isAllowed = true;
                $scope.user      = '';

                FsPermsResource.getFsPermsStatus().then(function (status) {
                    $scope.isAllowed = status.fs_perms;
                    $scope.user      = status.www_user;
                });
            }],
            link: function(scope, element, attr) {
                scope.$watch('isAllowed', function (newValue, oldValue) {
                    if (! newValue) {
                        element.modal('show');
                    }
                });
            }
        };
    }]
);

})();
