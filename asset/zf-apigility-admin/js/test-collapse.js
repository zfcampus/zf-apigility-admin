'use strict';

var module = angular.module('ag', ['ag-collapse']);

module.controller(
    'TestController',
    ['$scope', function($scope) {
        $scope.panels = [
            { title: "title 1" },
            { title: "title 2" },
            { title: "title 3" },
            { title: "title 4" }
        ];
    }]
);
