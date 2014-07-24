(function() { 'use strict';

angular.module('ag-admin').controller(
    'agFsPermsModal', 
    function($modal, FsPermsResource) {
        FsPermsResource.getFsPermsStatus().then(
            function (status) {
                if (status.fs_perms) {
                    return;
                }

                var user = status.www_user;

                $modal.open({
                    templateUrl: 'html/modals/fs-perms.html',
                    controller: function ($scope) {
                        $scope.user = user;
                    },
                    keyboard: false,
                    backdrop: 'static',
                });
            }
        );
    }
);

})();

