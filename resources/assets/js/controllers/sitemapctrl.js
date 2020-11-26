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
                    if(type=="merchant" || type =="report"){
                        //$event.preventDefault()
                    }
                    console.log("Going");
                    let url = $event.target.href;
                    if (type.includes("nearby") || type.includes("coverage")) {
                        
                        if (type.includes("nearby")) {
                            url += "/nearby"
                        } else if (type.includes("coverage")) {
                            url += "/coverage"
                        }
                        console.log("Type", type);
                        console.log("target", $event.target.href);
                        $event.preventDefault()

                        if (!$rootScope.shippingAddress) {
                            if ($rootScope.user) {
                                Cart.showConfirm(url);
                            } else {
                                Cart.showConfirmExt(url);
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

                            console.log("res", Modals.turnObjectToUrl(params, url));
                            window.location.href = Modals.turnObjectToUrl(params, url)
                        }
                    } else {
                        window.location.href = url;
                    }
                }
            }])
        .controller('SearchCtrl', ['$scope', '$rootScope', '$cookies', function ($scope, $rootScope, $cookies) {
                $scope.category = "";
                $scope.searchText = "";
                $scope.showError = false;

                angular.element(document).ready(function () {
                });
                $scope.selectCat = function () {
                    if ($scope.category.length > 0) {
                        $scope.showError = false;
                    }
                }

                $scope.search = function () {
                    if ($scope.category.length > 0) {
                        console.log("Search: ", $scope.searchText, " Category: ", $scope.category);
                        let results = $scope.category.split("|");
                        let url = "";
                        if (results[0] == "merchants") {
                            url = "/a/merchant-search";
                        } else if (results[0] == "reports") {
                            url = "/a/report-search";
                        } else if (results[0] == "products") {
                            url = "/a/product-search";
                        }
                        if (results[2] == "nearby" || results[2] == "coverage") {
                            if ($rootScope.shippingAddress) {
                                if (results[1] == "0") {
                                    url += "?lat=" + $rootScope.shippingAddress.lat + "&long=" + $rootScope.shippingAddress.long + "&q=" + $scope.searchText
                                } else {
                                    url += "?categories=" + results[1] + "&lat=" + $rootScope.shippingAddress.lat + "&long=" + $rootScope.shippingAddress.long + "&q=" + $scope.searchText
                                }
                                
                            } else {
                                if (results[1] == "0") {
                                    url += "?q=" + $scope.searchText
                                } else {
                                    url += "?categories=" + results[1] + "&q=" + $scope.searchText
                                }
                                
                                $cookies.put("locationRefferrer", url, {path: "/"});
                                window.location.href = "/location";
                                return;
                            }
                        } else {
                            if (results[1] == "0") {
                                url += "?q=" + $scope.searchText
                            } else {
                                url += "?categories=" + results[1] + "&q=" + $scope.searchText
                            }
                        }
                        window.location.href = url;
                        //console.log("Url",url);
                    } else {
                        $scope.showError = true;
                    }
                }
            }])
        .controller('HomeCtrl', ['$scope', 'Cart', '$rootScope', 'Modals', function ($scope, Cart, $rootScope, Modals) {
                $scope.data = {};
                $scope.user = {};
                $scope.category;


                $scope.goTo = function (type, $event) {
                    if (type.includes("nearby") || type.includes("coverage")) {
                        let url = $event.currentTarget.href;
                        if (type.includes("nearby")) {
                            url += "/nearby"
                        } else if (type.includes("coverage")) {
                            url += "/coverage"
                        }
                        console.log("Type", type);
                        console.log("target", $event.currentTarget.href);
                        $event.preventDefault()

                        if (!$rootScope.shippingAddress) {
                            if ($rootScope.user) {
                                Cart.showConfirm(url);
                            } else {
                                Cart.showConfirmExt(url);
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
                            console.log("res", Modals.turnObjectToUrl(params, url));
                            window.location.href = Modals.turnObjectToUrl(params, url)
                        }
                    }
                }
            }])