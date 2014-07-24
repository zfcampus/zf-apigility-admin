(function() { 'use strict';

angular.module('ag-admin').controller(
    'agCacheModal', 
    function($modal, CacheEnabledResource) {
        CacheEnabledResource.getCacheStatus().then(
            function (status) {
                if (! status) {
                    return;
                }

                $modal.open({
                    templateUrl: 'html/modals/cache-check.html',
                    keyboard: false,
                    backdrop: 'static'
                });
            }
        );
    }
);

})();

