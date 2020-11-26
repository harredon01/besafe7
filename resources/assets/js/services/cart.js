angular.module('besafe')
        .service('Cart', ['$q', '$http', '$mdDialog', 'Modals', '$cookies', '$rootScope', function ($q, $http, $mdDialog, Modals, $cookies, $rootScope) {

                var showConfirm = function (url) {
                    $mdDialog.show(Modals.getLocationPrompt()).then(function (answer) {
                        if (answer == "map") {
                            $cookies.put("locationRefferrer", url, {path: "/"});
                            window.location.href = "/location";
                        } else if (answer == 'address') {
                            showAdvanced();
                        }

                    }, function () {

                    });
                };
                var showQuestionaire = function (questions) {
                    $mdDialog.show(Modals.getQuestionairePopup(questions)).then(function (answer) {
                        console.log("questions", answer)

                    }, function () {

                    });
                };
                var showConfirmExt = function (url) {
                    $cookies.put("locationRefferrer", url, {path: "/"});
                    window.location.href = "/location";
                };
                var checkProduct = function (variant) {
                    console.log("Check prod", $rootScope.shippingAddress);
                    if (variant.is_shippable) {
                        if ($rootScope.shippingAddress) {
                            return checkMerchant();
                        }
                        showConfirm(window.location.href);
                        return false;
                    } else {
                        return checkMerchant();
                    }
                };
                var getCategoryFromUrl = function () {
                    let theUrl = window.location.href;
                    let segments = theUrl.split("/")
                    let category = segments[(segments.length - 1)];
                    if (category.includes("?")) {
                        category = category.split("?")[0];
                    }
                    if (isNaN(category)) {
                        return null;
                    }
                    return category;
                };
                var checkMerchant = function () {
                    if ($rootScope.merchant_id) {
                        return true;
                    } else {
                        let category = getCategoryFromUrl();
                        console.log("Searching for merchants in cat:", category);
                        showMerchants(category);
                    }
                };
                var changeMerchant = function () {
                    let category = getCategoryFromUrl();
                    console.log("Searching for merchants in cat:", category);
                    showMerchants(category);
                };
                var showAdvanced = function () {
                    $mdDialog.show(Modals.getAddressesPopup()).then(function (address) {
                        console.log("Got address", address);
                        $rootScope.shippingAddress = address;
                        $rootScope.lat = address.lat;
                        $cookies.put("shippingAddress", JSON.stringify(address), {path: "/"});
                        checkMerchant();
                    }, function () {
                        console.log("Got nothing");
                    });
                };
                var checkVariant = function (variant) {
                    console.log("Variant", variant);
                    if (variant.attributes.questions) {
                        showQuestionaire(variant.attributes.questions)
                    }
                };
                var showMerchants = function (category) {
                    $mdDialog.show(Modals.getMerchantsPopup(category)).then(function (merchant) {
                        console.log("Got merchant", merchant);
                        let params = Modals.getAllUrlParams();
                        let theUrl = window.location.href;
                        if (theUrl.includes("?")) {
                            theUrl = theUrl.split("?")[0];
                        }
                        params.merchant_id = merchant.id
                        let first = true;
                        for (var k in params) {
                            if (params.hasOwnProperty(k)) {
                                if (first) {
                                    theUrl = "?" + k + "=" + params[k];
                                } else {
                                    theUrl = "&" + k + "=" + params[k];
                                }
                            }
                        }

                        window.location.href = theUrl;
                    }, function () {
                        console.log("Got nothing");
                    });
                };
                var postToServer = function (postData) {
                    console.log("Post to server", postData);
                    var def = $q.defer();
                    $http({
                        method: "post",
                        url: "/api/cart/add",
                        data: postData
                    })
                            .success(function (data) {
                                // console.log(data);
                                def.resolve(data);
                            })
                            .error(function () {
                                def.reject("Failed to get nearby");
                            });
                    return def.promise;
                };

                var addCartItem = function (container, extras) {
                    console.log("Check variant: ", container);
                    let results = checkProduct(container);
                    container.merchant_id = $rootScope.merchant_id;
                    if (!container.item_id) {
                        container.item_id = null;
                    }
                    let actvVariant = container.product.activeVariant;
                    var def = $q.defer();
                    if (!results) {
                        def.resolve({status: "pending_location", message: "msg"});
                    } else {
                        if (actvVariant.attributes.questions) {
                            $mdDialog.show(Modals.getQuestionairePopup(actvVariant.attributes.questions)).then(function (results) {
                                let data = [];
                                for (let i in results) {
                                    let container = {
                                        "name": results[i].name,
                                        "value": results[i].value
                                    }
                                    data.push(container);
                                }
                                let postData = {
                                    product_variant_id: container.id,
                                    merchant_id: container.merchant_id,
                                    quantity: container.quantity,
                                    item_id: container.item_id,
                                    extras: {data: data}
                                };
                                postToServer(postData).then(function (data) {
                                    def.resolve(data);
                                }, function () {
                                    def.reject("Failed to get nearby");
                                });
                            }, function () {
                                def.resolve({status: "pending_questions", message: "Debes completar estas preguntas para comprar este producto"});
                            });
                        } else {
                            let postData = {
                                product_variant_id: container.id,
                                merchant_id: container.merchant_id,
                                quantity: container.quantity,
                                item_id: container.item_id,
                                extras: extras
                            };
                            postToServer(postData).then(function (data) {
                                def.resolve(data);
                            }, function () {
                                def.reject("Failed to postToServer");
                            });
                        }
                    }
                    return def.promise;
                };
                var updateCartItem = function (item_id, quantity) {
                    var def = $q.defer();
                    $http({
                        method: "post",
                        url: "/api/cart/update",
                        data: {
                            item_id: item_id,
                            quantity: quantity
                        }
                    })
                            .success(function (data) {
                                // console.log(data);
                                def.resolve(data);
                            })
                            .error(function () {
                                def.reject("Failed to get nearby");
                            });

                    return def.promise;
                };
                var getCart = function () {
                    var def = $q.defer();
                    $http({
                        method: "GET",
                        url: "/api/cart/get"
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                            .error(function () {
                                def.reject("Failed to get nearby");
                            });
                    return def.promise;
                };
                var getCheckoutCart = function () {
                    var def = $q.defer();
                    $http({
                        method: "GET",
                        url: "/api/cart/checkout"
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                            .error(function () {
                                def.reject("Failed to get nearby");
                            });
                    return def.promise;
                };

                var clearCart = function () {

                    var def = $q.defer();

                    $http({
                        method: "post",
                        url: "/api/cart/clear",
                    })
                            .success(function (data) {
                                // console.log(data);
                                def.resolve(data);
                            })
                            .error(function () {
                                def.reject("Failed to get nearby");
                            });

                    return def.promise;
                };
                return {
                    getCheckoutCart: getCheckoutCart,
                    getCart: getCart,
                    addCartItem: addCartItem,
                    updateCartItem: updateCartItem,
                    showConfirm: showConfirm,
                    showConfirmExt: showConfirmExt,
                    clearCart: clearCart,
                    changeMerchant: changeMerchant
                };
            }])