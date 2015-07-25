// Define as an independent module. It will allow use it in the project separately
// TODO: Rewrite into CoffeeScript
angular.module('utility', []).factory('Utility', function () {
  var utility = {};

  // Converts api response to a readable error list
  utility.toErrorList = function (response) {
    var errorList = [];

    // If a response is null, false, empty or undefined return empty array
    if (!response || angular.isUndefined(response)) {
      return errorList;
    }

    // If response has a "errors" property fill errorList with global errors
    if (angular.isDefined(response.errors)) {
      errorList = response.errors;
    }

    // If response does have any children errors return global errors or empty array
    if (angular.isUndefined(response.children)) {
      return errorList;
    }

    angular.forEach(response.children, function (node) {
      if (angular.isDefined(node.errors) && angular.isArray(node.errors)) {
        angular.extend(errorList, node.errors);
      } else if (angular.isDefined(node.children)) {
        angular.extend(errorList, utility.toErrorList(node));
      }
    });

    return errorList;
  };

  return utility;
});
