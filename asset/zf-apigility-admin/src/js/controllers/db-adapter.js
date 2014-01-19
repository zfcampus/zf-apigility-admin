(function(_, $) {'use strict';

angular.module('ag-admin').controller(
    'DbAdapterController',
    ['$scope', '$location', 'flash', 'DbAdapterResource', function ($scope, $location, flash, DbAdapterResource) {
        $scope.dbAdapters = [];
        $scope.showNewDbAdapterForm = false;

        $scope.resetForm = function () {
            $scope.showNewDbAdapterForm = false;
            $scope.adapterName = '';
            $scope.driver      = '';
            $scope.database    = '';
            $scope.username    = '';
            $scope.password    = '';
            $scope.hostname    = '';
            $scope.port        = '';
            $scope.charset     = '';
            return true;
        };

        function updateDbAdapters(force) {
            $scope.dbAdapters = [];
            DbAdapterResource.fetch({force: force}).then(function (dbAdapters) {
                $scope.$apply(function () {
                    $scope.dbAdapters = _.pluck(dbAdapters.embedded.db_adapter, 'props');
                });
            });
        }
        updateDbAdapters(false);

        $scope.createNewDbAdapter = function () {
            var options = {
                adapter_name :  $scope.adapter_name,
                driver       :  $scope.driver,
                database     :  $scope.database,
                username     :  $scope.username,
                password     :  $scope.password,
                hostname     :  $scope.hostname,
                port         :  $scope.port,
                charset      :  $scope.charset
            };
            DbAdapterResource.createNewAdapter(options).then(function (dbAdapter) {
                flash.success = 'Database adapter created';
                updateDbAdapters(true);
                $scope.resetForm();
            });
        };

        $scope.saveDbAdapter = function (index) {
            var dbAdapter = $scope.dbAdapters[index];
            var options = {
                driver   :  dbAdapter.driver,
                database :  dbAdapter.database,
                username :  dbAdapter.username,
                password :  dbAdapter.password,
                hostname :  dbAdapter.hostname,
                port     :  dbAdapter.port,
                charset  :  dbAdapter.charset
            };
            DbAdapterResource.saveAdapter(dbAdapter.adapter_name, options).then(function (dbAdapter) {
                flash.success = 'Database adapter ' + dbAdapter.adapter_name + ' updated';
                updateDbAdapters(true);
            });
        };

        $scope.removeDbAdapter = function (adapter_name) {
            DbAdapterResource.removeAdapter(adapter_name).then(function () {
                flash.success = 'Database adapter ' + adapter_name + ' removed';
                updateDbAdapters(true);
                $scope.deleteDbAdapter = false;
            });
        };

        /* @todo Ideally, this should not be using jquery. Instead, it should
         * likely use a combination of ng-class and ng-click such that ng-click
         * changes a scope variable that will update ng-class. However, until I
         * can figure that out, this will do.
         *
         * Key though: stopPropagation is necessary for those buttons we mark as
         * "data-expand", as we do not want the parent -- the panel header -- to
         * toggle that back closed.
         */
        $scope.clickPanelHeading = function ($event, $index) {
            var panel = $('#collapse' + $index);
            var target = $($event.target);
            if (target.attr('data-expand')) {
                /* target is a button; expand the collapse */
                panel.toggleClass('in', true);
                $event.stopPropagation();
                return false;
            }

            panel.toggleClass('in');
        };

    }]
);

})(_, $);
