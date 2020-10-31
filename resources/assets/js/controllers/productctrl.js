angular.module('besafe')
        .controller('ProductsCtrl',['$scope', 'Cart', '$rootScope', '$sce', function ($scope, Cart, $rootScope, $sce) {
            $scope.categories = [];
            angular.element(document).ready(function () {
                $scope.clean();
                $scope.categories = JSON.parse(viewData);
                for(let item in $scope.categories){
                    for(let k in $scope.categories[item].products){
                        $scope.categories[item].products[k].description = $sce.trustAsHtml($scope.categories[item].products[k].description);
                    }
                }
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
            $scope.tryClean = function (){
                console.log("data",viewData);
            }
            $scope.addCartItem = function (product_variant_id,merchant_id, quantity) {

                Cart.addCartItem(product_variant_id,merchant_id, quantity).then(function (data) {
                    if (data.status=="error") {
                        alert(data.message);
                    } else {
                        $rootScope.$broadcast('updateHeadCart');
                    }
                },
                        function (data) {

                        });
            }
        }])