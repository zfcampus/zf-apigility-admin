(function() {'use strict';

/**
 * Defines an object that can be used to dynamically fetch (and cache) a
 * template, populate an element with its contents, and bind the element
 * to a given scope.
 */
angular.module('ag-admin').factory(
  'AgTemplateInjector',
  function ($templateCache, $http, $q, $compile) {
    var defaultEmptyTemplate = 'html/empty.html';
    var templates = {};

    var fetchTemplate = function (template) {
      if (templates.hasOwnProperty(template)) {
        return $q.when(templates[template]);
      }

      return $http.get(template, {cache: $templateCache}).then(
        function (result) {
          templates[template] = result.data;
          return templates[template];
        }
      ).catch(
        function (error) {
          console.error('Unable to fetch template ' + template);
          console.error(error);
          return error;
        }
      );
    };

    var populateElement = function (element, contents, scope, onloadExpr) {
      element.html(contents);
      $compile(element.contents())(scope.$parent);

      if (onloadExpr) {
        scope.$eval(onloadExpr);
      }
    };

    return {
      defaultEmptyTemplate: defaultEmptyTemplate,
      fetchTemplate: fetchTemplate,
      populateElement: populateElement
    };
  }
);

})();
