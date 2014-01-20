(function() {'use strict';

angular.module('ag-admin').factory(
    'toggleSelection',
    function () {
        return function (model, $event) {
            var element = $event.target;
            if (element.checked) {
                model.push(element.value);
            } else {
                model.splice(model.indexOf(element.value), 1);
            }
        };
    }
);

})();
