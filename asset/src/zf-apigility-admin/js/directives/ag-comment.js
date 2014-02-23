(function() {'use strict';

/* <ag-comment>Comment you want stripped here</ag-comment> */
angular.module('ag-admin').directive('agComment', function() {
  return {
    restrict: 'E',
    compile: function(element, attr) {
        element.replaceWith('');
    }
  };
});

})();

