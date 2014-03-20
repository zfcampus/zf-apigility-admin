(function() {
  'use strict';

angular.module('ag-admin').controller(
  'ContentNegotiationController',
  function ($scope, $state, $stateParams, flash, selectors, ContentNegotiationResource) {
    var newSelector = {
      content_name: '',
      viewModel: '',
      selectors: {}
    };

    $scope.activeSelector = $stateParams.selector ? $stateParams.selector : '';
    $scope.inEdit         = !!$stateParams.edit;

    $scope.showNewSelectorForm = false;
    $scope.newSelector = JSON.parse(JSON.stringify(newSelector));
    $scope.selectors = JSON.parse(JSON.stringify(selectors));

    $scope.resetNewSelectorForm = function() {
      $scope.$broadcast('ag-form-submit-complete');
      $scope.$broadcast('ag-form-validation-errors-clear');
      $scope.showNewSelectorForm = false;
      $scope.newSelector = JSON.parse(JSON.stringify(newSelector));
    };

    $scope.cancelEdit = function () {
      $state.go($state.$current.name, {edit: ''}, {reload: true, inherit: true});
    };

    $scope.startEdit = function () {
      $state.go($state.$current.name, {edit: true}, {notify: true, inherit: true});
    };

    $scope.addViewModel = function (viewModel, selector) {
      selector.selectors[viewModel] = [];
      $scope.newSelector.viewModel = '';
    };

    $scope.removeViewModel = function (viewModel, selector) {
      delete selector.selectors[viewModel];
    };

    $scope.resetSelectorForm = function (selector) {
      $scope.$broadcast('ag-form-submit-complete');

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

      ContentNegotiationResource.createSelector($scope.newSelector).then(
        function (selector) {
          $scope.$broadcast('ag-form-submit-complete');
          selectors.push(selector);
          $scope.selectors.push(selector);
          flash.success = 'New selector created';
          $scope.resetNewSelectorForm();
        },
        function (error) {
          $scope.$broadcast('ag-form-submit-complete');

          if (error.status !== 400 && error.status !== 422) {
            /* generic, non-validation related error! */
            flash.error = 'Error submitting new API';
            return;
          }

          var validationErrors;

          if (error.status === 400) {
            validationErrors = [ 'Unexpected or missing data processing form' ];
          } else {
            validationErrors = error.data.validation_messages;
          }

          $scope.$broadcast('ag-form-validation-errors', validationErrors);
          flash.error = 'We were unable to validate your form; please check for errors.';
        }
      );
    };

    $scope.updateSelector = function (selector) {
      delete selector.viewModel;
      
      ContentNegotiationResource.updateSelector(selector).then(
        function (updated) {
          $scope.$broadcast('ag-form-submit-complete');

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
        },
        function (error) {
          $scope.$broadcast('ag-form-submit-complete');

          if (error.status !== 400 && error.status !== 422) {
            /* generic, non-validation related error! */
            flash.error = 'Error submitting new API';
            return;
          }

          var validationErrors;

          if (error.status === 400) {
            validationErrors = [ 'Unexpected or missing data processing form' ];
          } else {
            validationErrors = error.data.validation_messages;
          }

          $scope.$broadcast('ag-form-validation-errors', validationErrors);
          flash.error = 'We were unable to validate your form; please check for errors.';
        }
      );

    };

    $scope.removeSelector = function (selectorName) {
      ContentNegotiationResource.removeSelector(selectorName).then(function () {
        flash.success = 'Selector removed';

        ContentNegotiationResource.getList(true).then(function (updatedSelectors) {
          $state.go($state.current, {}, {
            reload: true, inherit: true, notify: true
          });
        });

      });
    };
  }
);

})();
