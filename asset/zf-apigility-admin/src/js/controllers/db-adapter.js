(function(_) {'use strict';

angular.module('ag-admin').controller(
    'DbAdapterController',
    function ($scope, $state, $stateParams, flash, DbAdapterResource, dbAdapters) {
        $scope.dbAdapters           = dbAdapters;
        $scope.showNewDbAdapterForm = false;
        $scope.activeAdapter        = $stateParams.adapter ? $stateParams.adapter : '';
        $scope.inEdit               = !!$stateParams.edit;

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

        $scope.cancelEdit = function () {
            $state.go($state.$current.name, {edit: ''}, {reload: true});
        };

        $scope.startEdit = function () {
            $state.go($state.$current.name, {edit: true}, {notify: false});
        };

        var updateDbAdapters = function (force) {
            $scope.dbAdapters = [];
            DbAdapterResource.getList(force).then(function (updatedAdapters) {
                $scope.dbAdapters = updatedAdapters;
            });
        };

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
    }
);

})(_);
