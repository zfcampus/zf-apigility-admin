(function(_) {'use strict';

angular.module('ag-admin').controller(
    'DbAdapterController',
    function ($scope, $state, $stateParams, flash, DbAdapterResource, dbAdapters, agFormHandler) {
        $scope.dbAdapters           = dbAdapters;
        $scope.showNewDbAdapterForm = false;
        $scope.activeAdapter        = $stateParams.adapter ? $stateParams.adapter : '';
        $scope.inEdit               = !!$stateParams.edit;

        $scope.resetForm = function () {
            agFormHandler.resetForm($scope);
            $scope.showNewDbAdapterForm = false;
            $scope.adapterName          = '';
            $scope.driver               = '';
            $scope.database             = '';
            $scope.dsn                  = '';
            $scope.username             = '';
            $scope.password             = '';
            $scope.hostname             = '';
            $scope.port                 = '';
            $scope.charset              = '';
            $scope.driver_options       = {};
            $scope.newOptionKey         = '';
            $scope.newOptionValue       = '';
            return true;
        };

        var updateDbAdapters = function (force, message) {
            DbAdapterResource.getList(force).then(
                function (updatedAdapters) {
                    if (message) {
                        flash.success = message;
                    }
                    $state.go($state.current, {edit: ''}, {
                        reload: true, inherit: true, notify: true
                    });
                }
            );
        };

        $scope.addNewDriverOption = function (options) {
            var key   = $scope.newOptionKey;
            var value = $scope.newOptionValue;

            if (key.length === 0 || value.length === 0) {
                return;
            }

            if (! $scope.driver_options) {
                $scope.driver_options = {};
            }

            $scope.driver_options[key] = value;
            $scope.newOptionKey   = '';
            $scope.newOptionValue = '';
        };

        $scope.addDriverOption = function (dbAdapter) {
            var key   = dbAdapter._newOptionKey;
            var value = dbAdapter._newOptionValue;

            if (key.length === 0 || value.length === 0) {
                return;
            }

            if (! dbAdapter.driver_options) {
                dbAdapter.driver_options = {};
            }

            dbAdapter.driver_options[key]    = value;
            dbAdapter._newOptionKey   = '';
            dbAdapter._newOptionValue = '';
        };

        $scope.removeNewDriverOption = function (driver_options, optionKey) {
            if (! $scope.driver_options || ! $scope.driver_options[optionKey]) {
                return;
            }
            delete $scope.driver_options[optionKey];
        };

        $scope.removeDriverOption = function (dbAdapter, optionKey) {
            if (! dbAdapter.driver_options || ! dbAdapter.driver_options[optionKey]) {
                return;
            }
            delete dbAdapter.driver_options[optionKey];
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

            if ($scope.dsn) {
                options.dsn = $scope.dsn;
            }

            if ($scope.driver_options && Object.keys($scope.driver_options).length > 0) {
                options.driver_options = $scope.driver_options;
            }

            DbAdapterResource.createNewAdapter(options).then(
                function (dbAdapter) {
                    updateDbAdapters(true, 'Database adapter created');
                    $scope.resetForm();
                }
            ).catch(
                function (error) {
                    agFormHandler.reportError(error, $scope);
                }
            );
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

            if (dbAdapter.dsn) {
                options.dsn = dbAdapter.dsn;
            }

            if (dbAdapter.driver_options && Object.keys(dbAdapter.driver_options).length > 0) {
                options.driver_options = dbAdapter.driver_options;
            }

            DbAdapterResource.saveAdapter(dbAdapter.adapter_name, options).then(
                function (dbAdapter) {
                    agFormHandler.resetForm($scope);
                    updateDbAdapters(true, 'Database adapter ' + dbAdapter.adapter_name + ' updated');
                }
            ).catch(
                function (error) {
                    agFormHandler.reportError(error, $scope);
                }
            );
        };

        $scope.removeDbAdapter = function (adapter_name) {
            DbAdapterResource.removeAdapter(adapter_name).then(
                function () {
                    updateDbAdapters(true, 'Database adapter ' + adapter_name + ' removed');
                    $scope.deleteDbAdapter = false;
                }
            );
        };
    }
);

})(_);
