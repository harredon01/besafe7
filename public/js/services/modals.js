angular.module('besafe')
        .service('Modals', ['$mdToast', '$mdDialog', function ($mdToast, $mdDialog) {

                var getAddressesPopup = function () {
                    return {
                        controller: ['$scope', '$mdDialog', 'Addresses', '$rootScope', function ($scope, $mdDialog, Addresses, $rootScope) {
                                $scope.addresses = [];

                                $scope.getAddresses = function () {
                                    if ($rootScope.addresses && $rootScope.addresses.length > 0) {
                                        $scope.addresses = $rootScope.addresses;
                                    } else {
                                        Addresses.getAddresses().then(function (data) {
                                            $scope.addresses = data.addresses;

                                        },
                                                function (data) {

                                                });
                                    }

                                }
                                $scope.hide = function () {
                                    $mdDialog.hide();
                                };

                                $scope.cancel = function () {
                                    $mdDialog.cancel();
                                };

                                $scope.selectAddress = function (address) {
                                    $mdDialog.hide(address);
                                };
                                $scope.getAddresses();
                            }],
                        templateUrl: '/templates/addressList.html',
                        // Appending dialog to document.body to cover sidenav in docs app
                        // Modal dialogs should fully cover application to prevent interaction outside of dialog
                        parent: angular.element(document.body),
                        clickOutsideToClose: true,
                    }
                }
                var getAppPopup = function () {
                    return {
                        controller: ['$scope', '$mdDialog', 'Addresses', '$rootScope', function ($scope, $mdDialog, Addresses, $rootScope) {
                                $scope.addresses = [];

                                $scope.selectPlatform = function (platform) {
                                    $mdDialog.hide(platform);
                                    

                                }
                                $scope.hide = function () {
                                    $mdDialog.hide();
                                };

                                $scope.cancel = function () {
                                    $mdDialog.cancel();
                                };
                            }],
                        templateUrl: '/templates/mobileApp.html',
                        // Appending dialog to document.body to cover sidenav in docs app
                        // Modal dialogs should fully cover application to prevent interaction outside of dialog
                        parent: angular.element(document.body),
                        clickOutsideToClose: true,
                    }
                }
                var getMerchantsPopup = function (category) {
                    return {
                        controller: ['$scope', '$mdDialog', 'Merchants', '$rootScope', function ($scope, $mdDialog, Merchants, $rootScope) {
                                $scope.merchants = [];

                                $scope.getMerchants = function () {
                                    let container = null;
                                    if (category) {
                                        container = {lat: $rootScope.shippingAddress.lat, long: $rootScope.shippingAddress.long, category: category};
                                    } else {
                                        container = {lat: $rootScope.shippingAddress.lat, long: $rootScope.shippingAddress.long};
                                    }
                                    Merchants.getMerchantsCoverage(container).then(function (data) {
                                        $scope.merchants = data.data;
                                    },
                                            function (data) {
                                            });
                                }
                                $scope.hide = function () {
                                    $mdDialog.hide();
                                };

                                $scope.cancel = function () {
                                    $mdDialog.cancel();
                                };

                                $scope.selectMerchant = function (merchant) {
                                    $mdDialog.hide(merchant);
                                };
                                $scope.getMerchants();
                            }],
                        templateUrl: '/templates/merchantsList.html',
                        // Appending dialog to document.body to cover sidenav in docs app
                        // Modal dialogs should fully cover application to prevent interaction outside of dialog
                        parent: angular.element(document.body),
                        clickOutsideToClose: true,
                    }
                }

                var getLocationPrompt = function () {
                    return {
                        controller: ['$scope', '$mdDialog', function ($scope, $mdDialog) {
                                $scope.answer = function (answer) {
                                    $mdDialog.hide(answer);
                                };
                                $scope.cancel = function () {
                                    $mdDialog.cancel();
                                };
                                $scope.hide = function () {
                                    $mdDialog.hide();
                                };
                            }],
                        templateUrl: '/templates/locationPrompt.html',
                        // Appending dialog to document.body to cover sidenav in docs app
                        // Modal dialogs should fully cover application to prevent interaction outside of dialog
                        parent: angular.element(document.body),
                        clickOutsideToClose: true,
                    }
                }
                var getLocationExtPrompt = function () {
                    return {
                        controller: ['$scope', '$mdDialog', function ($scope, $mdDialog) {
                                $scope.answer = function (answer) {
                                    $mdDialog.hide(answer);
                                };
                                $scope.cancel = function () {
                                    $mdDialog.cancel();
                                };
                                $scope.hide = function () {
                                    $mdDialog.hide();
                                };
                            }],
                        templateUrl: '/templates/locationExtPrompt.html',
                        // Appending dialog to document.body to cover sidenav in docs app
                        // Modal dialogs should fully cover application to prevent interaction outside of dialog
                        parent: angular.element(document.body),
                        clickOutsideToClose: true,
                    }
                }
                var showToast = function (message, parent) {
                    if (message == "CLEAR_CART") {
                        message = "Debes vaciar tu carro para esa compra";
                    }
                    var pinTo = getToastPosition();
                    var toast = $mdToast.simple()
                            .textContent(message)
                            .position(pinTo)
                            .hideDelay(3000)
                    if (parent) {
                        toast = $mdToast.simple()
                                .textContent(message)
                                .position(pinTo)
                                .parent(parent)
                                .hideDelay(3000)
                    }

                    $mdToast.show(toast);
                }
                var last = {
                    bottom: false,
                    top: true,
                    left: false,
                    right: true
                };

                var toastPosition = angular.extend({}, last);

                var getToastPosition = function () {
                    sanitizePosition();

                    return Object.keys(toastPosition)
                            .filter(function (pos) {
                                return toastPosition[pos];
                            }).join(' ');
                };
                function sanitizePosition() {
                    var current = toastPosition;

                    if (current.bottom && last.top) {
                        current.top = false;
                    }
                    if (current.top && last.bottom) {
                        current.bottom = false;
                    }
                    if (current.right && last.left) {
                        current.left = false;
                    }
                    if (current.left && last.right) {
                        current.right = false;
                    }

                    last = angular.extend({}, current);
                }
                var showLoader = function () {
                    $mdDialog.show({
                        controller: function () {

                        },
                        template: '<md-dialog style="background-color:transparent;box-shadow:none">' +
                                '<div layout="row" layout-sm="column" layout-align="center center" aria-label="wait" style="overflow:hidden">' +
                                '<md-progress-circular md-mode="indeterminate" ></md-progress-circular>' +
                                '</div>' +
                                '</md-dialog>',
                        parent: angular.element(document.body),
                        clickOutsideToClose: true,
                        fullscreen: false
                    })
                            .then(function (answer) {

                            });
                }
                var hideLoader = function () {
                    $mdDialog.hide()
                            .then(function (answer) {

                            });
                }
                var turnObjectToUrl = function (params, theUrl) {
                    if (theUrl.includes("?")) {
                        theUrl = theUrl.split("?")[0];
                    }
                    let first = true;
                    for (var k in params) {
                        if (params.hasOwnProperty(k)) {
                            if (first) {
                                theUrl += "?" + k + "=" + params[k];
                                first = false;
                            } else {
                                theUrl += "&" + k + "=" + params[k];
                            }
                        }
                    }
                    return theUrl;
                }
                getAllUrlParams = function (url) {
                    // get query string from url (optional) or window
                    var queryString = url ? url.split('?')[1] : window.location.search.slice(1);

                    // we'll store the parameters here
                    var obj = {};

                    // if query string exists
                    if (queryString) {

                        // stuff after # is not part of query string, so get rid of it
                        queryString = queryString.split('#')[0];

                        // split our query string into its component parts
                        var arr = queryString.split('&');

                        for (var i = 0; i < arr.length; i++) {
                            // separate the keys and the values
                            var a = arr[i].split('=');

                            // set parameter name and value (use 'true' if empty)
                            var paramName = a[0];
                            var paramValue = typeof (a[1]) === 'undefined' ? true : a[1];

                            // (optional) keep case consistent
                            paramName = paramName.toLowerCase();
                            if (typeof paramValue === 'string')
                                paramValue = paramValue.toLowerCase();

                            // if the paramName ends with square brackets, e.g. colors[] or colors[2]
                            if (paramName.match(/\[(\d+)?\]$/)) {

                                // create key if it doesn't exist
                                var key = paramName.replace(/\[(\d+)?\]/, '');
                                if (!obj[key])
                                    obj[key] = [];

                                // if it's an indexed array e.g. colors[2]
                                if (paramName.match(/\[\d+\]$/)) {
                                    // get the index value and add the entry at the appropriate position
                                    var index = /\[(\d+)\]/.exec(paramName)[1];
                                    obj[key][index] = paramValue;
                                } else {
                                    // otherwise add the value to the end of the array
                                    obj[key].push(paramValue);
                                }
                            } else {
                                // we're dealing with a string
                                if (!obj[paramName]) {
                                    // if it doesn't exist, create property
                                    obj[paramName] = paramValue;
                                } else if (obj[paramName] && typeof obj[paramName] === 'string') {
                                    // if property does exist and it's a string, convert it to an array
                                    obj[paramName] = [obj[paramName]];
                                    obj[paramName].push(paramValue);
                                } else {
                                    // otherwise add the property
                                    obj[paramName].push(paramValue);
                                }
                            }
                        }
                    }
                    return obj;
                }


                return {
                    getAddressesPopup: getAddressesPopup,
                    getLocationPrompt: getLocationPrompt,
                    getLocationExtPrompt: getLocationExtPrompt,
                    getMerchantsPopup: getMerchantsPopup,
                    showToast: showToast,
                    getAppPopup:getAppPopup,
                    showLoader: showLoader,
                    turnObjectToUrl: turnObjectToUrl,
                    hideLoader: hideLoader,
                    getAllUrlParams: getAllUrlParams
                };
            }])