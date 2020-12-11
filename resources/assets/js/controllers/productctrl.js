angular.module('besafe')
        .controller('ProductsCtrl', ['$scope', 'Cart', '$rootScope', '$sce', 'Products', 'Modals', function ($scope, Cart, $rootScope, $sce, Products, Modals) {
                $scope.categories = [];
                $scope.prodIds = [];
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

                angular.element(document).ready(function () {
                    $scope.clean();

                    var res = viewData.replace(/App\\Models\\/g, "");
                    console.log("viewData", res);
                    let container = JSON.parse(res);
                    $scope.$apply(function () {
                        $scope.buildProductsFromWhole(container);
                    });
                    console.log($scope.categories);
                    document.getElementById("dissapear").remove();
                    let params = Modals.getAllUrlParams();
                    console.log("Params", params)
                    if (params) {

                    } else {
                        params = {};
                    }
                    $scope.params = params;
                    $scope.params.includes = "files,merchant";

                    if ($scope.category_id) {
                        $scope.params.category_id = $scope.category_id;
                    }
                    let url = window.location.href;
                    if (url.includes("search")) {
                        $scope.isSearch = true;
                    }
                });
                $rootScope.$on('loadCartVariants', function (event, args) {
                    $scope.updateProductsCart(args, false);
                    if ($rootScope.user) {
                        $scope.checkFavorites();
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
                $scope.addCatFilter = function (category) {
                    $scope.params.category_id = category;
                    $scope.goTo(1);
                }
                $scope.buildProductsFromWhole = function (container) {
                    $scope.categories = container.categories;
                    $scope.current = container.page;
                    $scope.last = container.last_page;
                    $scope.current = container.page;
                    $scope.per_page = container.per_page;
                    $scope.total = container.total;
                    if (container.category) {
                        $scope.category_id = container.category.id;
                    }

                    console.log($scope.categories);
                    for (let item in $scope.categories) {
                        for (let k in $scope.categories[item].products) {
                            $scope.prodIds.push($scope.categories[item].products[k].id);

                            if (!$rootScope.merchant_id && $scope.categories[0].products[0].merchant_id) {
                                $rootScope.merchant_id = $scope.categories[0].products[0].merchant_id;
                            }
                            if (!$scope.categories[item].products[k].merchant_id && $scope.categories[item].products[k].merchants.length > 0) {
                                $scope.categories[item].products[k].merchant_id = $scope.categories[item].products[k].merchants[0].id
                            }
                            $scope.local_total++;
                            if ($scope.categories[item].products[k].files && $scope.categories[item].products[k].files.length > 0) {
                                $scope.categories[item].products[k].imgs = $scope.categories[item].products[k].files;
                            }
                            if ($scope.categories[item].products[k].imgs && $scope.categories[item].products[k].imgs.length > 0) {
                                $scope.categories[item].products[k].src = $scope.categories[item].products[k].imgs[0].file;
                            }
                            $scope.categories[item].products[k].description = $sce.trustAsHtml($scope.categories[item].products[k].description);
                            $scope.categories[item].products[k].activeVariant = $scope.categories[item].products[k].variants[0];
                            $scope.categories[item].products[k].variant_id = $scope.categories[item].products[k].variants[0].id;
                            $scope.categories[item].products[k].quantity = $scope.categories[item].products[k].variants[0].min_quantity;
                            $scope.categories[item].products[k].item_id = null;
                            $scope.categories[item].products[k].isFavorite = false;
                        }
                    }
                }
                $scope.buildProducts = function (resp) {
                    $scope.categories = Products.buildProductInformation(resp);
                    console.log("Result build product", $scope.categories);
                    let attributes = $scope.categories[0].products[0].merchant_attributes;
                    if (typeof attributes == "string") {
                        attributes = JSON.parse(attributes);
                    }

                    if (attributes.store_active) {
                        if (attributes.store_active == 1) {
                            $scope.storeActive = true;
                        }
                    }
                    $scope.merchantObj.merchant_name = $scope.categories[0].products[0].merchant_name;
                    $scope.merchantObj.merchant_description = $scope.categories[0].products[0].merchant_description;
                    $scope.merchantObj.src = $scope.categories[0].products[0].src;

                    //this.openTutorials();  
                    $scope.merchantObj.merchant_type = $scope.categories[0].products[0].merchant_type;
                    if ($scope.categories.length > 0) {
                        $scope.categories[0].more = true;
                        if ($scope.categories[0].products.length > 0) {
                            $scope.categories[0].products[0].more = true;
                        }
                        if ($scope.categories[0].products.length > 1) {
                            $scope.categories[0].products[1].more = true;
                        }
                    }
                    $scope.current = parseInt(resp.page);
                    $scope.prodIds = [];
                    $scope.last = parseInt(resp.last_page);
                    $scope.current = parseInt(resp.page);
                    $scope.per_page = parseInt(resp.per_page);
                    $scope.total = parseInt(resp.total);
                    $scope.local_total = 0;
                    for (let item in $scope.categories) {
                        for (let k in $scope.categories[item].products) {
                            $scope.local_total++;
                            $scope.prodIds.push($scope.categories[item].products[k].id);
                            $scope.categories[item].products[k].description = $sce.trustAsHtml($scope.categories[item].products[k].description);
                            $scope.categories[item].products[k].activeVariant = $scope.categories[item].products[k].variants[0];
                            $scope.categories[item].products[k].variant_id = $scope.categories[item].products[k].variants[0].id;
                            $scope.categories[item].products[k].quantity = $scope.categories[item].products[k].variants[0].min_quantity;
                            $scope.categories[item].products[k].item_id = null;
                        }
                    }
                }
                $scope.filterPrice = function () {
                    console.log("Filtering", $("#amount").val())
                    let str = $("#amount").val();
                    str = str.replace("$", "");
                    str = str.replace("$", "");
                    console.log("Filtering", str)
                    var res = str.split("-");
                    console.log("Filtering", res)
                    $scope.params.high = parseInt(res[1].trim());
                    $scope.params.low = parseInt(res[0].trim());
                    $scope.goTo(1);
                }
                $scope.goTo = function (page) {
                    $scope.params.page = page;
                    if ($rootScope.merchant_id) {
                        $scope.params.merchant_id = $rootScope.merchant_id;
                    }
                    Modals.showLoader();
                    $scope.categories = [];
                    if ($scope.isSearch) {
                        Products.searchProducts($scope.params).then(function (resp) {
                            console.log("params", $scope.params);
                            console.log("response", resp);
                            if (resp.total > 0) {
                                $scope.buildProductsFromWhole(resp);

                                //this.createSlides();
                            }
                            Modals.hideLoader();
                        },
                                function (data) {

                                });
                    } else {
                        Products.getProductsMerchant($scope.params).then(function (resp) {
                            console.log("params", $scope.params);
                            console.log("response", resp);
                            if (resp.products_total > 0) {
                                $scope.buildProducts(resp);

                                //this.createSlides();
                            }
                            Modals.hideLoader();
                        },
                                function (data) {

                                });
                    }

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
                        item_id: product.item_id,
                        product: product,
                        type: product.type
                    }
                    if((product.activeVariant.is_on_sale && product.activeVariant.sale <1)||(!product.activeVariant.is_on_sale && product.activeVariant.price <1)){
                        Modals.showToast("Ese producto no esta a la venta", $("#prod-cont-" + product.id));
                        return true;
                    }
                    Cart.addCartItem(container, []).then(function (data) {
                        console.log("Add cart", data);
                        if (data.status == "success") {
                            Modals.showToast("Carrito actualizado", $("#prod-cont-" + product.id));
                            product.item_id = data.item.id;
                            product.quantity = data.item.quantity;
                            $rootScope.$broadcast('loadHeadCart', data.cart);

                        } else {
                            Modals.showToast(data.message, $("#prod-cont-" + product.id));
                        }
                    },
                            function (data) {

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
                            Modals.showToast("Carrito actualizado", $("#prod-cont-" + product.id));
                            alert(data.message);
                        } else {
                            $rootScope.$broadcast('loadHeadCart', data.cart);
                        }
                    },
                            function (data) {

                            });
                }

            }])
        .controller('ProductDetailCtrl', ['$scope', 'Cart', '$rootScope', '$sce', 'Products', '$mdDialog', 'Modals', function ($scope, Cart, $rootScope, $sce, Products, $mdDialog, Modals) {
                $scope.hasLocation = false;
                $scope.hasMerchant = false;
                $scope.product = {};
                $scope.related_products = [];
                $scope.category_id = null;
                $scope.rating = {};

                angular.element(document).ready(function () {
                    $scope.clean();

                    var res = viewData.replace(/App\\Models\\/g, "");
                    console.log("viewData", res);
                    let container = JSON.parse(res);
                    console.log("Data", container);
                    $scope.product = container.product;
                    $scope.related_products = container.related_products[0].products;
                    for (let i in $scope.related_products) {
                        let activeVariant = $scope.related_products[i].variants[0];
                        for (let j in $scope.related_products[i].variants) {
                            if ($scope.related_products[i].variants[j].is_on_sale && activeVariant.is_on_sale) {
                                if ($scope.related_products[i].variants[j].sale < activeVariant.sale) {
                                    activeVariant = $scope.related_products[i].variants[j]
                                }
                            } else if (!$scope.related_products[i].variants[j].is_on_sale && activeVariant.is_on_sale) {
                                if ($scope.related_products[i].variants[j].price < activeVariant.sale) {
                                    activeVariant = $scope.related_products[i].variants[j]
                                }
                            } else if (!$scope.related_products[i].variants[j].is_on_sale && !activeVariant.is_on_sale) {
                                if ($scope.related_products[i].variants[j].price < activeVariant.price) {
                                    activeVariant = $scope.related_products[i].variants[j]
                                }
                            } else if ($scope.related_products[i].variants[j].is_on_sale && !activeVariant.is_on_sale) {
                                if ($scope.related_products[i].variants[j].sale < activeVariant.price) {
                                    activeVariant = $scope.related_products[i].variants[j]
                                }
                            }
                        }
                        $scope.related_products[i].activeVariant = activeVariant;
                        $scope.related_products[i].variant_id = activeVariant.id;
                    }
                    $scope.rating.type = "Product";
                    $scope.rating.object_id = $scope.product.id;
                    $scope.rating.rating = '5';
                    $scope.product.variants = $scope.product.product_variants;
                    $scope.product.activeVariant = $scope.product.variants[0];
                    $scope.product.quantity = $scope.product.activeVariant.min_quantity;
                    $scope.product.variant_id = $scope.product.variants[0].id;
                    $scope.product.description = $sce.trustAsHtml($scope.product.description);
                    document.getElementById("dissapear").remove();
                });
                $rootScope.$on('loadCartVariants', function (event, args) {
                    $scope.updateProductsCart(args, false);
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
                        item_id: product.item_id,
                        product: product
                    }
                    if((product.activeVariant.is_on_sale && product.activeVariant.sale <1)||(!product.activeVariant.is_on_sale && product.activeVariant.price <1)){
                        Modals.showToast("Ese producto no esta a la venta", $("#prod-cont-" + product.id));
                        return true;
                    }
                    Cart.addCartItem(container, []).then(function (data) {
                        console.log("Add cart", data);
                        if (data.status == "success") {
                            product.item_id = data.item.id;
                            product.quantity = data.item.quantity;
                            $rootScope.$broadcast('loadHeadCart', data.cart);
                            Modals.showToast("Carrito actualizado", $("#add-cart-form"));

                        } else {
                            Modals.showToast(data.message, $("#add-cart-form"));
                        }
                    },
                            function (data) {

                            });
                }
                $scope.addRating = function () {
                    console.log("Rating", $scope.rating);
                    if (!$rootScope.user) {
                        console.log("Debes estar loguedo para comentar");
                        Modals.showToast("Debes estar loguedo para comentar", $("#toast-container"));
                        return;
                    }
                    if ($scope.rating.rating.length > 0) {
                        Modals.showToast("Agrega una calificacion", $("#toast-container"));
                        return;
                    }
                    if ($scope.rating.comment.length > 0) {
                        Modals.showToast("Agrega una observacion a tu calificacion", $("#toast-container"));
                        return;
                    }
                    Products.addRating($scope.rating).then(function (data) {
                        console.log("Add cart", data);
                        if (data.status == "success") {
                            Modals.showToast("Mensaje enviado. Si vuelves a enviar se anula el anterior", $("#toast-container"))

                        } else {
                            Modals.showToast(data.message, $("#toast-container"));
                        }
                    },
                            function (data) {

                            });
                }
                $scope.updateProductsCart = function (cart, total) {
                    let products = $scope.related_products;
                    for (let m in cart.items) {
                        for (let j in products) {
                            for (let k in products[j].variants) {
                                let container = cart.items[m];
                                let variant = products[j].variants[k];
                                if (container.attributes.product_variant_id == variant.id) {
                                    products[j].item_id = container.id;
                                    products[j].quantity = container.quantity;
                                    if (total) {
                                        break;
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
                            alert(data.message);
                        } else {
                            $rootScope.$broadcast('loadHeadCart', data.cart);
                        }
                    },
                            function (data) {
                            });
                }

            }])