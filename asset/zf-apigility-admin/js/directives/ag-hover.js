'use strict';

var agHover = angular.module('ag-hover', []);

agHover.directive('agHover', function() {
  return {
    restrict: 'A',
    controller: ['$scope', function($scope) {
      var target;

      this.setTarget = function(element) {
        target = element;
      };

      this.toggleHide = $scope.toggleHide = function(flag) {
        target.toggleClass('hide', flag);
      };
    }],
    link: function(scope, element, attr) {
      element.on('mouseover', function(event) {
        scope.toggleHide(false);
      });

      element.on('mouseleave', function(event) {
        scope.toggleHide(true);
      });
    }
  };
});

agHover.directive('agHoverTarget', function() {
  return {
    require: '^agHover',
    restrict: 'A',
    link: function(scope, element, attr, hoverCtrl) {
      if (hoverCtrl) {
        hoverCtrl.setTarget(element);
        hoverCtrl.toggleHide(true);
      }
    }
  };
});
