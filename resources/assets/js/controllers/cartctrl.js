angular.module('besafe')
        .controller('CartCtrl', ['$scope', 'Cart', '$rootScope', function ($scope, Cart, $rootScope) {
                $scope.subtotal = 0;
                $scope.total = 0
                angular.element(document).ready(function () {
                    setTimeout(function () {
                        $scope.getCart();
                    }, 300);
                });
                $scope.clean = function () {
                    angular.forEach(angular.element(".item-attributes"), function (value, key) {
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
                $rootScope.$on('updateHeadCart', function () {
                    console.log("Get cart");
                    $scope.getCart();
                });
                $rootScope.$on('loadHeadCart', function (event, args) {
                    console.log("Loading cart", args);
                    $scope.loadCart(args);
                });
                $scope.getCart = function () {
                    Cart.getCart().then(function (data) {
                        console.log("Get Cart", data);
                        if (data.status == 'error') {
                            alert(data.message);
                        } else {
                            $scope.loadCart(data);
                            $rootScope.$broadcast('loadCartVariants', data);
                        }
                    },
                            function (data) {

                            });
                }
                $scope.loadCart = function (data) {
                    $rootScope.items = data.items;
                    $scope.totalItems = data.totalItems;
                    $scope.subtotal = data.subtotal;
                    $scope.total = data.total;
                }
                $scope.updateCartItem = function (product_variant_id) {
                    var quantity = angular.element(document.querySelector('input[name=quantity-' + product_variant_id + ']')).val();
                    console.log("da qty: " + quantity);
                    let container = {
                        quantity: quantity,
                        product_variant_id: $scope.product_variant_id,
                        "extras": []
                    };
                    Cart.updateCartItem(container).then(function (data) {
                        if (data.status == 'error') {
                            alert(data.message);
                        }
                        $scope.getCart();
                    },
                            function (data) {

                            });
                }
                $scope.clearCart = function () {
                    Cart.clearCart().then(function (data) {
                        $scope.getCart();
                        $rootScope.$broadcast('clearCart');
                    },
                            function (data) {

                            });
                }
                $scope.deleteCartItem = function (item_id) {
                    let container = {
                        quantity: 0,
                        item_id: item_id,
                        "extras": []
                    };

                    Cart.updateCartItem(container).then(function (data) {
                        console.log("delete", data);
                        if (data.status == 'error') {
                            alert(data.message);
                        } else {
                            $scope.loadCart(data.cart);
                            $rootScope.$broadcast('deleteItem', item_id);
                        }
                        //$scope.getCart();
                    },
                            function (data) {

                            });
                }

            }])