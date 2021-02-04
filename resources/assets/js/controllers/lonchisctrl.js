angular.module('besafe')
        .controller('LonchisCtrl', ['$scope', 'Cart', '$rootScope', '$sce', 'Products', 'Modals', function ($scope, Cart, $rootScope, $sce, Products, Modals) {
                $scope.products = [
                    {
                        id: 80,
                        name: "Prueba",
                        activeVariant: {
                            id: 220,
                            price: 12000,
                            merchant_id: 1299,
                            quantity: 1
                        }
                    },
                    {
                        id: 80,
                        name: "Eco Friendly",
                        activeVariant: {
                            id: 220,
                            price: 12000,
                            merchant_id: 1299,
                            quantity: 1
                        }
                    },
                    {
                        id: 81,
                        name: "Envase Desechable",
                        activeVariant: {
                            id: 220,
                            price: 12000,
                            merchant_id: 1299,
                            quantity: 1
                        }
                    }
                ];
                $scope.options = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22];
                $scope.hasLocation = false;
                $scope.hasMerchant = false;
                $scope.isSearch = false;
                $scope.merchantObj = {};
                $scope.current = 1;
                $scope.last = 1;
                $scope.range;
                $scope.params = {}
                $scope.per_page = 1
                $scope.category_id = null;
                $scope.total = 1;
                $scope.local_total = 0;
                $scope.cantidad1 = 11;
                $scope.cantidad2 = 11;
                $scope.precio1 = 14200;
                $scope.precio1D = 150000;
                $scope.precio2 = 14200;
                $scope.precio2D = 150000;


                angular.element(document).ready(function () {

                });
                $rootScope.$on('loadCartVariants', function (event, args) {
                    $scope.updateProductsCart(args, false);
                    if ($rootScope.user) {
                        //$scope.checkFavorites();
                    }
                });
                $rootScope.$on('clearCart', function (event, args) {
                    console.log("Cart cleared resetting items")
                    $scope.cartCleared();

                });
                $rootScope.$on('deleteItem', function (event, args) {
                    console.log("item removed resetting items: ", args)
                    $scope.cartDeletedItem(args);
                });
                $scope.changeAmount = function (id) {
                    let discountFactor = 0;
                    if (id == 1) {
                        discountFactor = Math.floor($scope.cantidad1 / 11);
                    } else {
                        discountFactor = Math.floor($scope.cantidad2 / 11);
                    }
                    if (id == 1) {
                        $scope.precio1D = ($scope.precio1 * $scope.cantidad1 - (discountFactor * 11000));
                    } else {
                        $scope.precio2D = ($scope.precio2 * $scope.cantidad2 - (discountFactor * 11000));
                    }


                }
                $scope.changeStore = function () {
                    Cart.changeMerchant()
                }
                $scope.addCatFilter = function (category) {
                    $scope.params.category_id = category;
                    $scope.goTo(1);
                }
                $scope.addToCart = function (id) {
                    let postData = {};
                    if (id == 1) {
                        postData = {
                            product_variant_id: 220,
                            merchant_id: 1299,
                            quantity: 1,
                            item_id: null,
                            extras: {}
                        };
                    } else if (id == 2) {
                        postData = {
                            product_variant_id: 210,
                            merchant_id: 1299,
                            quantity: $scope.cantidad1,
                            item_id: null,
                            extras: {}
                        };
                    } else {
                        postData = {
                            product_variant_id: 220,
                            merchant_id: 1299,
                            quantity: $scope.cantidad2,
                            item_id: null,
                            extras: {}
                        };
                    }

                    Cart.postToServer(postData).then(function (data) {
                        $rootScope.$broadcast('loadHeadCart', data.cart);
                        window.location.href = "/checkout";
                    }, function () {

                    });

                }
                $scope.checkFavorites = function () {
                    let container = {
                        product_ids: $scope.prodIds
                    }
                    Products.checkFavorite(container).then(function (data) {
                        console.log("checkFavorites res", data);
                        if (data.status == "success") {
                            let results = data.data;
                            for (let k in results) {
                                for (let i in $scope.categories) {
                                    for (let j in $scope.categories[i].products) {
                                        if ($scope.categories[i].products[j].id == results[k].favoritable_id) {
                                            $scope.categories[i].products[j].isFavorite = true;
                                        }
                                    }
                                }
                            }

                        }
                    },
                            function (data) {

                            });
                }
                $scope.addFavorite = function (product) {
                    if ($rootScope.user) {
                        let container = {
                            object_id: product.id,
                            type: "Product"
                        }
                        Products.addFavorite(container).then(function (data) {
                            console.log("Add addFavorite", data);
                            if (data.status == "success") {
                                product.isFavorite = true;
                                Modals.showToast("Agregado", $("#prod-cont-" + product.id));
                            } else {
                                Modals.showToast(data.message, $("#prod-cont-" + product.id));
                            }
                        },
                                function (data) {
                                });
                    } else {
                        Modals.showToast("Debes estar logueado para guardar productos", $("#prod-cont-" + product.id));
                    }

                }
                $scope.deleteFavorite = function (product) {
                    let container = {
                        object_id: product.id,
                        type: "Product"
                    }
                    Products.deleteFavorite(container).then(function (data) {
                        console.log("Add addFavorite", data);
                        if (data.status == "success") {
                            Modals.showToast("Agregado", $("#prod-cont-" + product.id));
                        } else {
                            Modals.showToast(data.message, $("#prod-cont-" + product.id));
                        }
                    },
                            function (data) {
                            });
                }
                $scope.cartCleared = function () {
                    for (let i in $scope.categories) {
                        for (let j in $scope.categories[i].products) {
                            $scope.categories[i].products[j].item_id = null;
                        }
                    }
                }
                $scope.cartDeletedItem = function (item_id) {
                    for (let i in $scope.categories) {
                        for (let j in $scope.categories[i].products) {
                            if ($scope.categories[i].products[j].item_id == item_id) {
                                $scope.categories[i].products[j].item_id = null;
                            }
                        }
                    }
                }
                $scope.updateProductsCart = function (cart, total) {
                    cart:
                            for (let m in cart.items) {
                        for (let i in $scope.categories) {
                            for (let j in $scope.categories[i].products) {
                                $scope.categories[i].products[j].item_id = null;
                                for (let k in $scope.categories[i].products[j].variants) {
                                    let container = cart.items[m];
                                    let variant = $scope.categories[i].products[j].variants[k];
                                    if (container.attributes.product_variant_id == variant.id) {
                                        $scope.categories[i].products[j].item_id = container.id;
                                        $scope.categories[i].products[j].quantity = container.quantity;
                                        if (total) {
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
                    let container = {
                        quantity: product.quantity,
                        item_id: product.item_id,
                        "extras": []
                    };
                    Cart.updateCartItem(container).then(function (data) {
                        if (data.status == "error") {
                            Modals.showToast("Error Actualizando", $("#prod-cont-" + product.id));
                            alert(data.message);
                        } else {
                            Modals.showToast("Carrito actualizado", $("#prod-cont-" + product.id));
                            $rootScope.$broadcast('loadHeadCart', data.cart);
                        }
                    },
                            function (data) {

                            });
                }

            }])
        