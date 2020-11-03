angular.module('besafe')
        .controller('ProductsCtrl', ['$scope', 'Cart', '$rootScope', '$sce', '$cookies', '$mdDialog', 'Modals', function ($scope, Cart, $rootScope, $sce, $cookies, $mdDialog, Modals) {
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
                                $scope.categories[item].products[k].activeVariant = $scope.categories[item].products[k].variants[0];
                                $scope.categories[item].products[k].variant_id = $scope.categories[item].products[k].variants[0].id;
                                $scope.categories[item].products[k].quantity = $scope.categories[item].products[k].variants[0].min_quantity;
                                $scope.categories[item].products[k].item_id = null;
                            }
                        }
                    });
                    console.log($scope.categories);
                    document.getElementById("dissapear").remove();
                });
                $rootScope.$on('loadCartVariants', function (event, args) {
                    $scope.updateProductsCart(args,false);
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
                $scope.selectVariant = function (product) {
                    for (let item in product.variants) {
                        if (product.variant_id == product.variants[item].id) {
                            product.activeVariant = product.variants[item];
                        }
                    }
                }
                $scope.addCartItem = function (product) {
                    let container = {
                        id: product.activeVariant.id,
                        quantity: product.quantity,
                        item_id: product.item_id
                    }
                    Cart.addCartItem(container, []).then(function (data) {
                        console.log("Add cart", data);
                        if (data.status == "error") {
                            alert(data.message);
                        } else {
                            product.item_id = data.item.id;
                            product.quantity = data.item.quantity;
                            $rootScope.$broadcast('loadHeadCart', data.cart);
                        }
                    },
                            function (data) {

                            });
                }
                $scope.updateProductsCart = function (cart,total) {
                    cart:
                    for (let m in cart.items) {
                        for (let i in $scope.categories) {
                            for (let j in $scope.categories[i].products) {
                                $scope.categories[i].products[j].item_id = null;
                                for (let k in $scope.categories[i].products[j].variants) {
                                    let container = cart.items[m];
                                    let variant = $scope.categories[i].products[j].variants[k];
                                    if(container.attributes.product_variant_id == variant.id){
                                        $scope.categories[i].products[j].item_id = container.id;
                                        $scope.categories[i].products[j].quantity = container.quantity;
                                        if(total){
                                            continue cart;
                                        }
                                        
                                    }
                                }
                            }
                        }
                    }

                }
                $scope.changeCartQuantity = function (product, change) {
                    if (change == "+") {
                        product.quantity++;
                    } else {
                        product.quantity--;
                    }
                    Cart.updateCartItem(product.item_id, product.quantity).then(function (data) {
                        if (data.status == "error") {
                            alert(data.message);
                        } else {
                            $rootScope.$broadcast('loadHeadCart', data.cart);
                        }
                    },
                            function (data) {

                            });
                }

            }])