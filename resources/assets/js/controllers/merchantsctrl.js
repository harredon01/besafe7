angular.module('besafe')

        .controller('MerchantsCtrl', ['$scope', 'Merchants', 'Modals', function ($scope, Merchants, Modals) {
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
                $scope.showAddress = true;
                $scope.showStore = true;
                $scope.submitted = false;
                $scope.category;
                angular.element(document).ready(function () {
                    var res = viewData.replace(/App\\Models\\/g, "");
//                    res = res.replace(/""{/g, '"\"{');
//                    res = res.replace(/}""/g, '}\""');
                    console.log("res", res);
                    let container = JSON.parse(res);
                    let url = window.location.href;
                    if(url.includes("urgencias")){
                        $scope.showStore = false;
                    }

                    $scope.category = container.category;
                    $scope.merchants = container.data;
                    for(let item in $scope.merchants){
                        for(let k in $scope.merchants[item].attributes.categories){
                            if($scope.merchants[item].attributes.categories[k].id == $scope.category.id){
                                $scope.merchants[item].activeCategory = $scope.merchants[item].attributes.categories[k];
                                break;
                            }
                        }
                    }
                    if($scope.merchants.length == 1){
                        $scope.openItem($scope.merchants[0]);
                    }
                    console.log("Data", $scope.merchants);
                    $scope.current = container.page;
                    $scope.per_page = container.per_page;
                    $scope.last = container.last_page;
                    $scope.total = container.total;
                    document.getElementById("dissapear").remove();
                });

                $scope.goTo = function (page) {
                    $scope.current = page;
                    $scope.merchants = [];
                    Modals.showLoader();
                    var where = "category_id=" + $scope.category.id + "&page=" + $scope.current + "&limit=" + 2;
                    if ($scope.category.type.includes("nearby")) {
                        Merchants.getMerchantsNearby(where).then(function (data) {
                            $scope.loadResults(data)
                        },
                                function (data) {
                                });
                    } else if ($scope.category.type.includes("coverage")) {
                        Merchants.getMerchantsCoverage(where).then(function (data) {
                            $scope.loadResults(data)
                        },
                                function (data) {
                                });
                    } else {
                        Merchants.getMerchants(where).then(function (data) {
                            $scope.loadResults(data)
                        },
                                function (data) {
                                });
                    }
                }

                $scope.loadResults = function (data) {
                    $scope.merchants = data.data;
                    $scope.current = parseInt(data.page);
                    $scope.per_page = parseInt(data.per_page);
                    $scope.last = parseInt(data.last_page);
                    $scope.total = parseInt(data.total);
                    Modals.hideLoader();
                }
                $scope.openItem = function (merchant) {
                    let url = "";
                    let merchant_id = merchant.id;
                    if (merchant.categorizable_id) {
                        merchant_id = merchant.categorizable_id;
                    }
                    if ($scope.category) {
                        url = "/a/products/" + $scope.category.url + "?merchant_id=" + merchant_id;
                    } else {
                        url = "/a/merchant/" + merchant.slug + "/products";
                    }

                    window.location.href = url;
                }
            }])
        .controller('MerchantDetailCtrl', ['$scope','$rootScope', 'Merchants', 'Modals', '$mdDialog','Cart', function ($scope,$rootScope, Merchants, Modals, $mdDialog,Cart) {
                $scope.data = {};
                $scope.user = {};

                $scope.merchant = {};
                angular.element(document).ready(function () {
                    var res = viewData.replace(/App\\Models\\/g, "");
//                    res = res.replace(/""{/g, '"\"{');
//                    res = res.replace(/}""/g, '}\""');
                    console.log("res", res);
                    let container = JSON.parse(res);

                    console.log("Data", container);
                    $scope.merchant = container;
                    $scope.merchant.availabilities = $scope.merchant.availabilities2;
                    console.log("$scope.merchant", $scope.merchant);
                    //document.getElementById("dissapear").remove();
                });
                $scope.booking = function () {
                    if ($rootScope.user) {
                        let params = {
                            "availabilities": $scope.merchant.availabilities,
                            "type": "Merchant",
                            "objectId": $scope.merchant.id,
                            "objectName": $scope.merchant.name,
                            "objectDescription": $scope.merchant.description,
                            "objectIcon": $scope.merchant.icon,
                            "expectedPrice": $scope.merchant.unit_cost,
                            "alert":"#book-button"
                        }
                        Cart.showBooking(params);
                    } else {
                        Modals.showToast("Registrate o ingresa para reservar",$("#book-button"));
                    }

                    console.log("Booking");

//                    $mdDialog.show(Modals.getAppPopup()).then(function (platform) {
//                        let url = "";
//                        if (platform == "ios") {
//                            url = ""
//                        } else if (platform == 'android') {
//                            url = ""
//                        } else if (platform == 'web') {
//                            url = ""
//                        }
//                        console.log("Url", platform, url)
//                        //window.location.href = url;
//
//                    }, function () {
//
//                    });
                }
            }])