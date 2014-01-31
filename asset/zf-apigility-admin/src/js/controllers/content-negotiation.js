(function(_) {'use strict';

angular.module('ag-admin').controller(
  'ContentNegotiationController',
  ['$scope', '$location', 'flash', 'selectors', 'ContentNegotiationResource', function ($scope, $location, flash, selectors, ContentNegotiationResource) {
    var newSelector = {
      content_name: '',
      viewModel: '',
      selectors: {}
    };

    $scope.showNewSelectorForm = false;
    $scope.newSelector = _.cloneDeep(newSelector);
    $scope.selectors = _.cloneDeep(selectors);

    $scope.resetNewSelectorForm = function() {
      $scope.showNewSelectorForm = false;
      $scope.newSelector = _.cloneDeep(newSelector);
    };

    $scope.addViewModel = function (viewModel, selector) {
      selector.selectors[viewModel] = [];
      $scope.newSelector.viewModel = '';
    };

    $scope.removeViewModel = function (viewModel, selector) {
      delete selector.selectors[viewModel];
    };

    $scope.resetSelectorForm = function (selector) {
      /* Reset to original values */
      var name = selector.content_name;
      var originalSelector;
      angular.forEach(selectors, function (value) {
        if (originalSelector || value.content_name !== name) {
          return;
        }
        originalSelector = value;
      });
      if (! originalSelector) {
        return;
      }
      angular.forEach($scope.selectors, function (value, key) {
        if (value.content_name !== originalSelector.content_name) {
          return;
        }
        $scope.selectors[key] = originalSelector;
      });
    };

    $scope.createSelector = function() {
      delete $scope.newSelector.viewModel;

      ContentNegotiationResource.createSelector($scope.newSelector).then(function (selector) {
        selectors.push(selector);
        $scope.selectors.push(selector);
        flash.success = 'New selector created';
        $scope.resetNewSelectorForm();
      });
    };

    $scope.updateSelector = function (selector) {
      delete selector.viewModel;
      
      ContentNegotiationResource.updateSelector(selector).then(function (updated) {
        /* Update original selector on success, so that view matches */
        var updatedSelector = false;
        angular.forEach(selectors, function (value, key) {
          if (updatedSelector || value.content_name !== updated.content_name) {
            return;
          }
          selectors[key] = updated;
          updatedSelector = true;
        });

        flash.success = 'Selector updated';
      });

    };

    $scope.removeSelector = function (selectorName) {
      ContentNegotiationResource.removeSelector(selectorName).then(function () {
        flash.success = 'Selector removed';

        ContentNegotiationResource.getList().then(function (updatedSelectors) {
          selectors = updatedSelectors;
          $scope.selectors = _.cloneDeep(selectors);
        });

      });
    };
  }]
);

})(_);
