angular.module('besafe')
        .controller('ProductsCtrl', ['$scope', 'Cart', '$rootScope', '$sce', '$cookies', '$mdDialog','Modals',function ($scope, Cart, $rootScope, $sce, $cookies, $mdDialog,Modals) {
                $scope.categories = [];
                $scope.hasLocation = false;
                $scope.hasMerchant = false;
                
                angular.element(document).ready(function () {
                    $scope.clean();
                    $scope.categories = JSON.parse(viewData);
                    console.log($scope.categories);
                    $scope.$apply(function () {
                        for (let item in $scope.categories) {
                            for (let k in $scope.categories[item].products) {
                                $scope.categories[item].products[k].description = $sce.trustAsHtml($scope.categories[item].products[k].description);
                            }
                        }
                    });

                    console.log($scope.categories);
                    document.getElementById("dissapear").remove();
                });
                $scope.clean = function () {
                    angular.forEach(angular.element(".product-attributes"), function (value, key) {
                        var a = angular.element(value);
                        var obj = JSON.parse(a.html());
                        var html = "";
                        for (x in obj) {
                            html += x + ": " + obj[x] + " <br/>";
                        }
                        a.html(html)
                        a.css("display", "block");
                    });
                }
                $scope.changeStore = function () {
                    Cart.changeMerchant()
                }
                $scope.addCartItem = function (product_variant,extras) {
                    Cart.addCartItem(product_variant,extras).then(function (data) {
                        if (data.status == "error") {
                            alert(data.message);
                        } else {
                            $rootScope.$broadcast('updateHeadCart');
                        }
                    },
                            function (data) {

                            });
                }
                
            }])