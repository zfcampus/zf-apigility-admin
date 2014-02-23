'use strict';

describe('Controller: DashboardController', function () {

    // load the controller's module
    beforeEach(module('ag-admin'));

    var DashboardController,
        scope;

    // Initialize the controller and a mock scope
    beforeEach(inject(function ($controller, $rootScope) {
        scope = $rootScope.$new();
        DashboardController = $controller('DashboardController', {
            $scope: scope
          }
        );
      }
    ));

    it('1 should be 1', function () {
        expect(1).toBe(1);
      }
    );
  }
);
