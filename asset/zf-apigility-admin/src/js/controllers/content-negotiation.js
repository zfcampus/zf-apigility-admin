(function(_) {'use strict';

angular.module('ag-admin').controller(
  'ContentNegotiationController',
  ['$scope', '$location', 'flash', 'selectors', function ($scope, $location, flash, selectors) {
    var newSelector = {
      content_name: '',
      viewModel: '',
      selectors: {}
    };

    $scope.showNewSelectorForm = false;
    $scope.newSelector = _.cloneDeep(newSelector);
    $scope.selectors = selectors;

    $scope.resetNewSelectorForm = function() {
      $scope.showNewSelectorForm = false;
      $scope.newSelector = _.cloneDeep(newSelector);
    };

    $scope.addViewModel = function (viewModel, selector) {
      selector.selectors[viewModel] = [];
      $scope.newSelector.viewModel = '';
    };

    $scope.createSelector = function() {
      delete $scope.newSelector.viewModel;
      console.log($scope.newSelector);
      flash.success = 'New selector created';
      $scope.resetNewSelectorForm();
    };
  }]
);

})(_);
