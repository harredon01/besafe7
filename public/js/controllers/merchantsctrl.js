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
                $scope.submitted = false;
                $scope.category;
                angular.element(document).ready(function () {
                    var res = viewData.replace(/App\\Models\\/g, "");
//                    res = res.replace(/""{/g, '"\"{');
//                    res = res.replace(/}""/g, '}\""');
                    console.log("res", res);
                    let container = JSON.parse(res);

                    console.log("Data", container);
                    $scope.category = container.category;
                    $scope.merchants = container.data;
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
                    if(merchant.categorizable_id){
                        merchant_id = merchant.categorizable_id;
                    }
                    if($scope.category.type.includes("products")){
                        url = "/a/products/"+$scope.category.url+"?merchant_id="+merchant_id;
                    } else {
                        url = "/a/merchant/"+merchant.slug;
                    }
                    window.location.href=url;
                }
            }])
        .controller('MerchantDetailCtrl', ['$scope', 'Merchants', 'Modals', function ($scope, Merchants, Modals) {
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
                    $scope.merchant = container.merchant;

                    document.getElementById("dissapear").remove();
                });
            }])