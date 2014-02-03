(function(_) {'use strict';

angular.module('ag-admin').controller(
    'ApiDocumentationController',
    ['$rootScope', '$scope', '$location', '$timeout', '$routeParams', 'flash', 'ApiRepository',
    function ($rootScope, $scope, $location, $timeout, $routeParams, flash, ApiRepository) {

        $scope.service = (typeof $scope.$parent.restService != 'undefined') ? $scope.$parent.restService : $scope.$parent.rpcService;

        // for rest
        if (typeof $scope.$parent.restService != 'undefined') {
            if (typeof $scope.service.documentation == 'undefined') {
                $scope.service.documentation = {};
            }
            if (typeof $scope.service.documentation.collection == 'undefined') {
                $scope.service.documentation.collection = {};
            }
            _.forEach($scope.service.collection_http_methods, function (allowed_method) {
                if (typeof $scope.service.documentation.collection[allowed_method] == 'undefined') {
                    $scope.service.documentation.collection[allowed_method] = {description: null, request: null, response: null};
                }
            });
            if (typeof $scope.service.documentation.entity == 'undefined') {
                $scope.service.documentation.entity = {};
            }
            _.forEach($scope.service.resource_http_methods, function (allowed_method) {
                if (typeof $scope.service.documentation.entity[allowed_method] == 'undefined') {
                    $scope.service.documentation.entity[allowed_method] = {description: null, request: null, response: null};
                }
            });
        } else {
            if (typeof $scope.service.documentation == 'undefined') {
                $scope.service.documentation = {};
            }
            _.forEach($scope.service.http_methods, function (allowed_method) {
                if (typeof $scope.service.documentation[allowed_method] == 'undefined') {
                    $scope.service.documentation[allowed_method] = {description: null, request: null, response: null};
                }
            });
        }

        var hasHalMediaType = function (mediatypes) {
            if (typeof mediatypes !== 'object' || !Array.isArray(mediatypes)) {
                return false;
            }

            if (mediatypes.lastIndexOf('application/hal+json') === -1) {
                return false;
            }

            return true;
        };

        var tab = function (num) {
            return new Array(num * 4).join(' ');
        };

        var createLink = function (rel, routeMatch, indent, append, type) {
            if (type == 'collection') {
                routeMatch = routeMatch.replace(/\[[a-zA-Z0-9_\/:\-]+\]$/, '');
            }
            if (append) {
                routeMatch += append;
            }
            return tab(indent) + "\"" + rel + "\": {\n" + tab(indent + 1) + "\"href\": \"" + routeMatch + "\"\n" + tab(indent) + "}";
        };

        var createLinks = function (links, indent) {
            return tab(indent) + "\"_links\": {\n" + links.join(",\n") + "\n" + tab(indent) + "}\n";
        };

        var createCollection = function (collectionName, routeMatch, params) {
            var entityLinks = [ createLink('self', routeMatch, 5) ];
            var collection = tab(1) + "\"_embedded\": {\n" + tab(2) + "\"" + collectionName + "\": [\n" + tab(3) + "{\n";
            collection += createLinks(entityLinks, 4);
            collection += params.join(",\n") + "\n" + tab(3) + "}\n" + tab(2) + "]\n" + tab(1) + "}";
            return collection;
        };

        $scope.generate = function(model, method, direction, restPart) {
            var doctext   = '';
            var docparams = [];
            var isHal     = false;
            var links     = [];

            if (direction == 'response' && $scope.service.accept_whitelist) {
                isHal = hasHalMediaType($scope.service.accept_whitelist);
            }

            _.forEach($scope.service.input_filter, function (item) {
                docparams.push(tab(1) + '"' + item.name + '": "' + (item.description || '') + '"');
            });
            

            if (isHal && (restPart != 'collection' || method == 'POST')) {
                links.push(createLink('self', $scope.service.route_match, 2));
                doctext = "{\n" + createLinks(links, 1) + docparams.join(",\n") + "\n}";
            } else if (isHal && restPart == 'collection') {
                var collectionName = $scope.service.collection_name ? $scope.service.collection_name : 'items';
                _.forEach(docparams, function (param, key) {
                    docparams[key] = tab(3) + param;
                });
                links.push(createLink('self', $scope.service.route_match, 2, false, 'collection'));
                links.push(createLink('first', $scope.service.route_match, 2, '?page={page}', 'collection'));
                links.push(createLink('prev', $scope.service.route_match, 2, '?page={page}', 'collection'));
                links.push(createLink('next', $scope.service.route_match, 2, '?page={page}', 'collection'));
                links.push(createLink('last', $scope.service.route_match, 2, '?page={page}', 'collection'));
                doctext = "{\n" + createLinks(links, 1) + createCollection(collectionName, $scope.service.route_match, docparams) + "\n}";
            } else {
                doctext = "{\n" + docparams.join(",\n") + "\n}";
            }

            if (!model[direction]) {
                model[direction] = doctext;
            } else {
                model[direction] += "\n" + doctext;
            }

        };

        $scope.save = function() {
            ApiRepository.saveDocumentation($scope.service);
            $scope.$parent.flash.success = 'Documentation saved.';
        };

    }]
);

})(_);
