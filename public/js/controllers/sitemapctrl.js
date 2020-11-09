angular.module('besafe')

        .controller('SitemapCtrl', ['$scope', 'Cart', '$rootScope', 'Modals', function ($scope, Cart, $rootScope, Modals) {
                $scope.data = {};
                $scope.user = {};
                $scope.current = 1;
                $scope.last = 1;
                $scope.total = 1;
                $scope.per_page = 1;
                $scope.merchants = [];
                $scope.categories = [];
                $scope.regionVisible = false;
                $scope.editMerchant = false;
                $scope.submitted = false;
                $scope.category;
                angular.element(document).ready(function () {
                });
                $rootScope.$on('updateShippingAddress', function () {
                    if ($rootScope.user) {
                        Cart.showConfirm(window.location.href);
                    } else {
                        Cart.showConfirmExt(window.location.href);
                    }
                });

                $scope.goTo = function (type, $event) {
                    if (type.includes("nearby") || type.includes("coverage")) {
                        console.log("Type", type);
                        console.log("target", $event.target.href);
                        $event.preventDefault()

                        if (!$rootScope.shippingAddress) {
                            if ($rootScope.user) {
                                Cart.showConfirm($event.target.href);
                            } else {
                                Cart.showConfirmExt($event.target.href);
                            }

                        } else {
                            let params = Modals.getAllUrlParams();
                            if (params && params.length > 0) {

                            } else {
                                params = {};
                            }
                            params.lat = $rootScope.shippingAddress.lat;
                            params.long = $rootScope.shippingAddress.long;
                            console.log("params", params);
                            let url = $event.target.href;
                            if (type.includes("nearby")) {
                                url += "/nearby"
                            } else if (type.includes("coverage")) {
                                url += "/coverage"
                            } 
                            console.log("res", Modals.turnObjectToUrl(params, url));
                            window.location.href = Modals.turnObjectToUrl(params, url)
                        }
                    }
                }
            }])