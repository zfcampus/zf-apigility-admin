'use strict';

var agInclude = angular.module('ag-include', []);

agInclude.directive('agInclude', ['$http', '$templateCache', '$compile', function($http, $templateCache, $compile) {
    return {
        restrict: 'E',
        transclude: true,
        replace: true,
        link: function(scope, element, attr) {
            if (!attr.hasOwnProperty('src')) {
                console.error('ag-include requires a "src" attribute; none provided!');
                return;
            }

            $http.get(attr.src, {cache: $templateCache})
                .success(function(response) {
                    var contents = $('<div/>').html(response).contents();
                    element.html(contents);
                    $compile(contents)(scope);
                });
        }
    };
}]);
