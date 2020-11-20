angular.module('besafe')

        .controller('CheckoutCartCtrl', ['$scope', '$rootScope', 'Users', 'Orders', 'Products', 'Modals', function ($scope, $rootScope, Users, Orders, Products, Modals) {
                $scope.data = {};
                $scope.coupon = "";
                $scope.isDigital = false;
                angular.element(document).ready(function () {
                    $scope.getCart(true);
                });
                $scope.cleanJson = function () {
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
                $scope.getCart = function (init) {
                    Products.getCheckoutCart().then(function (data) {
                        if (data.status == 'error') {
                            alert(data.message);
                        } else {
                            $scope.loadCart(data, init)
                        }
                    },
                            function (data) {

                            });
                }
                $scope.updateCartItem = function (item_id) {
                    var quantity = angular.element(document.querySelector('input[name=check-quantity-' + item_id + ']')).val();
                    Products.updateCartItem(item_id, quantity).then(function (data) {
                        if (data.status == 'error') {
                            alert(data.message);
                        }
                        $scope.getCart(false);
                    },
                            function (data) {
                            });
                }
                $scope.clearCart = function () {
                    Products.clearCart().then(function (data) {
                        $scope.getCart();
                    },
                            function (data) {
                            });
                }
                $scope.loadCart = function (data, init) {
                    if (data.items.length > 0) {
                        let items = data.items;
                        let shippable = false;
                        for (let i in items) {
                            let item = items[i];
                            console.log("Item: ", item.attributes);
                            if (item.attributes.is_shippable) {
                                shippable = true;
                            }
                        }
                        $scope.items = data.items;
                        $scope.totalItems = data.totalItems;
                        $scope.subtotal = data.subtotal;
                        $scope.shipping = data.shipping;
                        $scope.tax = data.tax;
                        $scope.sale = data.sale;
                        $scope.coupon = data.coupon;
                        $scope.discount = data.sale + data.coupon;
                        $scope.total = data.total;
                        console.log("Broadcasting shippable: ", shippable);
                        if (init) {
                            if (shippable) {
                                $rootScope.$broadcast('Shippable');
                            } else {
                                $scope.isDigital = true;
                                $rootScope.$broadcast('NotShippable');
                            }
                        }
                    } else {
                        window.location.href = '/';
                    }
                }
                $scope.prepareOrder = function () {
                    $rootScope.$broadcast('prepareOrder');
                }
                $scope.deleteCartItem = function (item_id) {
                    Products.updateCartItem(item_id, 0).then(function (data) {
                        if (data.status == 'error') {
                            alert(data.message);
                        }
                        $scope.getCart();
                    },
                            function (data) {

                            });
                }
                $scope.setCoupon = function () {
                    //setTimeout(function(){ Modals.hideLoader(); }, 2000);
                    if ($scope.coupon && $scope.coupon.length > 0) {
                        Modals.showLoader();
                        Orders.setCoupon($scope.coupon).then(function (resp) {
                            Modals.hideLoader();
                            if (resp) {
                                if (resp.status == "success") {
                                    $scope.getCart(false);
                                    $scope.coupon = "";
                                    Modals.showToast('Cupon agregado exitosamente', $("#checkout-cart"));
                                } else {
                                    Modals.showToast('No puedes usar ese cupon', $("#checkout-cart"));
                                }
                            }
                        },
                                function (data) {
                                });
                    } else {
                        Modals.showToast('Ingresa un cupon');
                    }
                }
                $rootScope.$on('updateCheckoutCart', function (event, args) {
                    $scope.getCart(false);

                });
                $rootScope.$on('updateLoadedCart', function (event, args) {
                    console.log("updateLoadedCart", args);
                    $scope.loadCart(args);

                });
            }])
        .controller('CheckoutShippingCtrl', ['$scope', '$rootScope', 'LocationService', 'Users', 'Cart', '$cookies', 'Orders', 'Modals', 'Billing',
            function ($scope, $rootScope, LocationService, Users, Cart, $cookies, Orders, Modals, Billing) {
                $scope.data = {};
                $scope.addressSet = {};
                $scope.shipping = [];
                $scope.regionVisible = false;
                $scope.addressesFetched = false;
                $scope.cityVisible = false;
                $scope.addAddress = false;
                $scope.visible = false;
                $rootScope.shippingAddressSet = true;
                $rootScope.shippingCondition = true;
                $rootScope.shippingConditionSet = false;
                $rootScope.billingAddressSet = false;
                $rootScope.paymentMethodSet = false;

                $rootScope.$on('Shippable', function (event, args) {
                    $rootScope.shippingAddressSet = false;
                    $rootScope.shippingCondition = false;
                    $rootScope.billingAddressSet = false;
                    $rootScope.paymentMethodSet = false;
                    $scope.visible = true;
                    let strAddress = $cookies.get("shippingAddress");
                    let address = null;
                    if (strAddress && strAddress.length > 0) {
                        address = JSON.parse(strAddress);
                    }
                    console.log("Shipping address: ", address);
                    console.log("$rootScope.user: ", $rootScope.user);
                    if (address) {
                        if (!address.name) {
                            $scope.addAddress = true;
                            $scope.data = address;

                            if ($rootScope.user) {
                                $scope.data.name = $rootScope.user.name;
                                $scope.data.phone = $rootScope.user.cellphone;
                            }
                            $scope.selectPlace($scope.data.country_id, $scope.data.region_id, $scope.data.city_id);
                        }
                        if (address.id) {
                            $scope.selectAddress(address);
                        }
                    } else {
                        $scope.getAddresses();
                    }
                });
                $scope.countries = [{name: "Colombia", id: 1}, {name: "Colombia2", id: 2}]

                angular.element(document).ready(function () {

                });
                $rootScope.$on('prepareOrder', function (event, args) {
                    $scope.prepareOrder();
                });
                $rootScope.$on('NotShippable', function (event, args) {
                    $scope.visible = false;
                });
                $rootScope.$on('getAddresses', function (event, args) {
                    $scope.getAddresses();
                });
                $scope.cleanJson = function () {
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
                $scope.save = function (isvalid) {
                    $scope.submitted = true;
                    if (isvalid) {
                        Modals.showLoader();
                        Users.saveAddress($scope.data).then(function (data) {
                            Modals.hideLoader();
                            if (data.status == "success") {
                                $scope.selectAddress(data.address);
                                $scope.data = {};
                                $scope.submitted = false;
                                $scope.hideAddressForm();
                            } else {

                            }

                        },
                                function (data) {

                                });
                    }
                }
                $scope.getAddresses = function () {
                    Users.getAddresses().then(function (data) {
                        $rootScope.addresses = data.addresses;

                    },
                            function (data) {

                            });
                }
                $scope.clean = function () {
                    $scope.data = {};
                    $scope.regionVisible = false;
                    $scope.cityVisible = false;
                }
                $scope.selectCountry = function (country_id) {
                    LocationService.getRegionsCountry(country_id).then(function (data) {
                        $scope.regions = data.data;
                        $scope.regionVisible = true;
                        $scope.cityVisible = false;

                    },
                            function (data) {

                            });
                }
                $scope.selectRegion = function (region_id) {
                    LocationService.getCitiesRegion(region_id).then(function (data) {
                        $scope.cities = data.data;
                        $scope.cityVisible = true;

                    },
                            function (data) {

                            });
                }
                $scope.selectPlace = function (country_id, region_id, city_id) {
                    //$scope.$apply(function () {
                    $scope.data.country_id = country_id;
                    LocationService.getRegionsCountry(country_id).then(function (data) {
                        $scope.regions = data.data;
                        $scope.regionVisible = true;
                        $scope.cityVisible = false;
                        console.log("Setting region", $scope.data.region_id);
                        $scope.data.region_id = region_id;
                        LocationService.getCitiesRegion(region_id).then(function (data) {
                            $scope.cities = data.data;
                            $scope.cityVisible = true;
                            console.log("Setting city_id", $scope.data.city_id);
                            console.log("Setting cities", $scope.cities);
                            $scope.data.city_id = city_id;
                        },
                                function (data) {
                                });
                    },
                            function (data) {
                            });
                    //});
                }

                $scope.selectAddress = function (address) {
                    $scope.addressSet = address;
                    let data = {address_id: address.id};
                    Modals.showLoader();
                    Orders.setShippingAddress(data).then(function (data) {
                        console.log("Set shipping address", data);
                        if (data.status == "success") {
                            $rootScope.shippingAddressSet = true;
                            $rootScope.shippingAddress = address;
                            $cookies.put("shippingAddress", JSON.stringify($rootScope.shippingAddress), {path: "/"});
                            let order = data.order;
                            let attributes = JSON.parse(order.attributes);
                            order.attributes = attributes;
                            $rootScope.activeOrder = order;
                            let providers = attributes.providers;
                            for (let i in providers) {
                                console.log("Providers: ", providers[i]);
                            }
                            console.log("Setting discounts", $rootScope.shippingAddressSet);
                            Orders.setDiscounts(order.id, "Booking").then(function (data) {
                                console.log("Discounts set", data);
                                let dataS = {"payers": [$rootScope.user.id], "platform": "Booking"};
                                Orders.checkOrder(order.id, dataS).then(function (resp) {
                                    console.log("Check Order Result", resp);
                                    Modals.hideLoader();
                                    let order = resp.order;
                                    if (resp.status == "success") {
                                        Modals.showToast("Direccion guardada", $("#checkout-shipping"));
                                        $rootScope.$broadcast('updateCheckoutCart');
                                    } else {
                                        $scope.handleCheckError(resp, order);
                                    }
                                },
                                        function (data) {
                                        });
                            },
                                    function (data) {
                                    });
                        } else {
                            Modals.hideLoader();
                            Modals.showToast("Fuera de cobertura para esta orden", $("#checkout-shipping"));
                        }

                    },
                            function (data) {
                            });
                }
                $scope.addCartItem = function (product_variant, extras) {
                    Cart.addCartItem(product_variant, extras).then(function (data) {
                        if (data.status == "error") {
                            alert(data.message);
                        } else {
                            $rootScope.$broadcast('updateCheckoutCart');
                        }
                    },
                            function (data) {

                            });
                }
                $scope.setShippingCondition = function (item) {
                    Modals.showLoader();
                    Orders.setPlatformShippingCondition($rootScope.activeOrder.id, item.platform).then(function (data) {
                        Modals.hideLoader();
                        console.log("setPlatformShippingPrice Result", data);
                        if (data.status == "success") {
                            $rootScope.$broadcast('updateLoadedCart', data.cart);
                            $rootScope.activeOrder = data.order;
                            $rootScope.shippingConditionSet = true;
                            Modals.showToast("Envio agregado exitosamente", $("#checkout-shipping"));
                        } else {
                            Modals.showToast("Hubo un error con ese metodo de envio", $("#checkout-shipping"));
                        }
                    },
                            function (data) {
                                Modals.showToast("Hubo un error con ese metodo de envio", $("#checkout-shipping"));
                            });
                }
                $scope.handleCheckError = function (resp, order) {
                    if (resp.type == "credits") {
                        let missing = parseInt(resp.required_credits);
                        console.log("Required credits", missing);
                        console.log("creditItem", resp.creditItem);
                        Modals.showToast("Hemos agregado un item obligatorio", $("#checkout-shipping"));
                        console.log("creditItemMerchant", resp.creditItemMerchant);
                        let container = {
                            order_id: order.id,
                            product_variant_id: resp.creditItem.id,
                            quantity: missing,
                            item_id: null,
                            merchant_id: resp.creditItemMerchant
                        };
                        $scope.addCartItem(container, []);
                    } else if (resp.type == "buyers") {
                        //
                    } else if (resp.type == "shipping") {
                        //this.api.toast('CHECKOUT_PREPARE.REQUIRES_SHIPPING');
                        Modals.showToast("Debes seleccionar proveedor de envio", $("#checkout-shipping"));
                        if ($scope.shipping.length == 0) {
                            $scope.expectedProviders = 0;
                            let attributes = JSON.parse(order.attributes);
                            if (attributes.providers) {
                                for (let item in attributes.providers) {
                                    $scope.expectedProviders++;
                                    $scope.getPlatformShippingPrice(resp.order.id, attributes.providers[item]);
                                }
                            }
                        }
                    } else if (resp.type == "delivery") {
                        /*this.api.toast('CHECKOUT_PREPARE.REQUIRES_DELIVERY');
                         this.api.dismissLoader();
                         let endDate = new Date();
                         if (this.currentItems.length == 0) {
                         this.getCart();
                         }
                         endDate.setDate(endDate.getDate() + 1);
                         //this.delivery = endDate.toISOString();
                         console.log("delivery", this.delivery);
                         this.requiresDelivery = true;*/
                    }
                }
                $scope.getPlatformShippingPrice = function (order_id, platform) {
                    Orders.getPlatformShippingPrice(order_id, platform).then(function (data) {
                        console.log("getPlatformShippingPrice Result", data);
                        if (data.status == "success") {
                            let container = {platform: platform, price: data.price}
                            $scope.shipping.push(container);
                        }
                    },
                            function (data) {

                            });
                }
                $scope.showAddressForm = function () {
                    $cookies.put("locationRefferrer", window.location.href, {path: "/"});
                    window.location.href = "/location";
                }
                $scope.hideAddressForm = function () {
                    $scope.addAddress = false;
                }
                $scope.changeAddress = function () {
                    console.log("Change address")
                    $rootScope.shippingAddressSet = false;
                    $scope.addAddress = false;
                    if ($rootScope.addresses && $rootScope.addresses.length > 0) {

                    } else {
                        $scope.getAddresses();
                    }
                }
                $scope.changeShipping = function () {
                    $rootScope.shippingConditionSet = false;
                }
                $scope.prepareOrder = function () {
                    Modals.showLoader();
                    $scope.showPayment = false;
                    $scope.shippingError = false;
                    let payers = [$rootScope.user.id];
                    let recurring_type = "limit";
                    let recurring_value = 3;
                    let container = {
                        "order_id": $rootScope.activeOrder.id,
                        "payers": payers,
                        "delivery_date": null,
                        "split_order": false,
                        "platform": "Booking",
                        "recurring": false,
                        "recurring_type": recurring_type,
                        "recurring_value": recurring_value,
                        "merchant_id": $rootScope.activeOrder.merchant_id
                    };
                    Orders.prepareOrder(container, "Booking").then(function (resp) {
                        Modals.hideLoader();
                        if (resp) {
                            if (resp.status == "success") {
                                Modals.showToast("Pago creado exitosamente", $("#checkout-cart"));
                                console.log("orderProvider", resp);
                                $rootScope.activePayment = resp.payment;
                                $rootScope.activeOrder = resp.order;
                                if ($rootScope.activePayment.total == $rootScope.activePayment.transaction_cost) {
                                    let container = {
                                        "payment_id": this.payment.id
                                    }
                                    Modals.showLoader();
                                    Billing.completePaidOrder(container).then(function (resp) {
                                        Modals.hideLoader();
                                        if (resp.status == "success") {
                                            $scope.transactionResponse(null, true);
                                        } else {
                                            $scope.showPayment = true;
                                            //this.scrollToTop();
                                        }
                                    }, (err) => {
                                        $scope.showPayment = true;
                                        //this.scrollToTop();
                                        console.log("completePaidOrderError", err);
                                    });
                                } else {
                                    $rootScope.$broadcast('activatePayment');
                                }
                            } else {
                                $scope.handleCheckError(resp);
                            }

                        }
                    }, (err) => {
                        Modals.hideLoader();
                        console.log("getCartError", err);
                        Modals.showToast("Hubo un error porfavor comunicate mas tarde", $("#checkout-cart"));
                    });
                }
            }])
        .controller('CheckoutBillingCtrl', ['$scope', '$rootScope', 'LocationService', 'Users', 'Billing', '$location', '$anchorScroll', 'Modals', '$mdDialog', 'Cart',
            function ($scope, $rootScope, LocationService, Users, Billing, $location, $anchorScroll, Modals, $mdDialog, Cart) {
                $scope.data = {};
                $scope.data2 = {};
                $scope.data3 = {};
                $scope.data4 = {};
                $scope.transaction = {};
                $scope.banks = [];
                $rootScope.paymentActive = false;
                $scope.creditCardVisible = false;
                $scope.creditPayerVisible = false;
                $scope.creditBuyerVisible = false;
                $scope.credito = false;
                $scope.showResult = false;
                $scope.resultHeader = "";
                $scope.resultBody = "";
                $scope.cash = false;
                $scope.debito = false;
                angular.element(document).ready(function () {
                    var d = new Date();
                    $scope.years = [];
                    $scope.months = [];
                    var year = d.getFullYear()
                    for (i = 0; i < 12; i++) {
                        if (i < 9) {
                            $scope.months.push("0" + (i + 1));
                        } else {
                            $scope.months.push(i + 1);
                        }

                    }
                    for (i = 0; i < 13; i++) {
                        $scope.years.push(year + i);
                    }
                });
                $rootScope.$on('activatePayment', function (event, args) {
                    $rootScope.paymentActive = true;
                    setTimeout(function () {
                        $location.hash('payment-methods');
                        $anchorScroll();
                    }, 800);
                    // call $anchorScroll()
                });
                $scope.keytab = function (event, maxlength) {
                    console.log("Event", event)
                    var target = event.target || event.srcElement;
                    let nextInput = target.nextElementSibling; // get the sibling element
                    console.log('nextInput', nextInput);

                    console.log('target', target);
                    console.log('targetvalue', target.value);
                    console.log('targettype', target.nodeType);
                    if (target.value.length < maxlength) {
                        return;
                    }
                    if (nextInput == null)  // check the maxLength from here
                        return;
                    else
                        nextInput.focus();   // focus if not null
                }
                $scope.creditTab = function (event) {
                    let target = event.target || event.srcElement;
                    let value = target.value;
                    let branch = (/^(?:5[1-5][0-9]{2}|222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720)[0-9]{12}$/.test(value)) ? "MASTERCARD"
                            : (/^(4[0-9]{12}(?:[0-9]{3})?$)|(^606374[0-9]{10}$)/.test(value)) ? "VISA"
                            : (/^3[47][0-9]{13}$/.test(value)) ? 'AMEX'
                            : (/^[35](?:0[0-5]|[68][0-9])[0-9]{11}$|^30[0-5]{11}$|^3095[0-9]{10}$|^36[0-9]{12}$|^3[89][0-9]{12}$/.test(value)) ? 'DINERS'
                            : (/^6(?:011|5[0-9]{2}|4[4-9][0-9])[0-9]{12}$/.test(value)) ? 'DISCOVER'
                            : (/^589562[0-9]{10}$/.test(value)) ? 'NARANJA'
                            : (/^603488[0-9]{10}$|^2799[0-9]{9}$/.test(value)) ? 'SHOPPING'
                            : (/^604(([23][0-9]{2})|(400))[0-9]{10}$|^589657[0-9]{10}$/.test(value)) ? 'CABAL'
                            : (/^603493[0-9]{10}$/.test(value)) ? 'ARGENCARD'
                            : (/^590712[0-9]{10}$/.test(value)) ? 'CODENSA'
                            : (/^627180[0-9]{10}$/.test(value)) ? 'CMR'
                            : "";

                    console.log("Credit branch", branch);
                    $scope.data2.cc_branch = branch;
                }
                $scope.payCreditCard = function (isvalid) {
                    $scope.submitted2 = true;
                    if (isvalid) {
                        $scope.data2.platform = "Booking";
                        $scope.data2.email = true;
                        $scope.data2.payment_id = $rootScope.activePayment.id;
                        Modals.showLoader();
                        Billing.payCreditCard($scope.data2, "PayU").then(function (data) {
                            Modals.hideLoader();
                            console.log("Response payu", data)
                            Cart.clearCart();
                            if (data.message != "error") {
                                $scope.showResult = true;
                                $scope.transaction = data.transaction;
                                if (data.transaction.transaction_state == "APPROVED") {
                                    $scope.resultHeader = "Pago aprobado";
                                    $scope.resultBody = "Tu pago ha sido completado, revisa tu correo para recibir tu compra";
                                } else if (data.transaction.transaction_state == "PENDING") {
                                    $scope.resultHeader = "Pago pendiente";
                                    $scope.resultBody = "Tu transaccion esta siendo verificada, cuando complete la verificacion recibiras un correo con el resultado";
                                } else {
                                    $scope.resultHeader = "Pago negado";
                                    $scope.resultBody = "Tu transaccion ha sido negada. Porfavor intenta otro metodo u otra tarjeta. En Mi cuenta > Mis Pagos puedes reintentar el pago";
                                }
                            } else {
                                Modals.showToast("Hubo un error. Porfavor verifica tus datos", $("#checkout-payment"));
                            }
                            //$scope.data2 = {};
                        },
                                function (data) {

                                });
                    }
                }
                $scope.selectOption = function (option) {
                    $scope.data4.payment_method = option;
                }
                $scope.populatePayer = function () {
                    if ($scope.data2.payer_name) {
                        if ($scope.data2.payer_name.length == 0) {
                            $scope.data2.payer_name = $scope.data2.cc_name;
                        }
                    } else {
                        $scope.data2.payer_name = $scope.data2.cc_name;
                    }
                }
                $scope.payDebitCard = function (isvalid) {
                    $scope.submitted3 = true;
                    if (isvalid) {
                        $scope.data3.platform = "Booking";
                        $scope.data3.email = true;
                        $scope.data3.payment_id = $rootScope.activePayment.id;
                        Modals.showLoader();
                        Billing.payDebit($scope.data3, "PayU").then(function (data) {
                            if (data.code == "SUCCESS") {
                                if (data.transactionResponse.state == "PENDING") {
                                    //this.showPrompt();
                                    Cart.clearCart().then(function (resp) {
                                        window.location.href = data.transactionResponse.extraParameters.BANK_URL;
                                    },
                                            function (data) {
                                                Modals.hideLoader();
                                            });
                                } else {
                                    Modals.hideLoader();
                                    Modals.showToast("Hubo un problema. Porfavor intenta otro medio. ", $("#checkout-payment"));
                                }
                            } else {
                                Modals.hideLoader();
                                Modals.showToast("Hubo un problema. Porfavor intenta otro medio. ", $("#checkout-payment"));
                            }
                        },
                                function (data) {
                                    Modals.hideLoader();
                                });
                    }
                }
                $scope.payCash = function (isvalid) {
                    $scope.submitted4 = true;
                    if (isvalid) {
                        Modals.showLoader();
                        $scope.data4.platform = "Booking";
                        $scope.data4.email = true;
                        $scope.data4.payment_id = $rootScope.activePayment.id;
                        Billing.payCash($scope.data4, "PayU").then(function (data) {
                            Modals.hideLoader();
                            console.log("after payCash");
                            console.log(JSON.stringify(data));
                            if (data.code == "SUCCESS") {
                                Cart.clearCart().then(function (resp) {
                                    if (data.transactionResponse.extraParameters.URL_PAYMENT_RECEIPT_HTML) {
                                        window.location.href = data.transactionResponse.extraParameters.URL_PAYMENT_RECEIPT_HTML;
                                    } else {
                                        Modals.showToast("Hubo un problema. Porfavor intenta otro medio. ", $("#checkout-payment"));
                                    }
                                },
                                        function (data) {
                                        });
                            } else {
                                Modals.showToast("Hubo un problema. Porfavor intenta otro medio. ", $("#checkout-payment"));
                            }
                        },
                                function (data) {
                                });
                    }
                }

                $scope.clean = function () {
                    $scope.data = {};
                    $scope.regionVisible = false;
                    $scope.cityVisible = false;
                }
                $scope.clean2 = function () {
                    $scope.data2 = {};
                }
                $scope.fill = function () {
                    $scope.data2.cc_branch = "VISA";
                    $scope.data2.cc_name = "APPROVED";
                    $scope.data2.cc_number = "4111111111111111";
                    $scope.data2.cc_security_code = "123";
                    $scope.data2.cc_expiration_month = "09";
                    $scope.data2.cc_expiration_year = "2017";
                    $scope.data2.payer_email = "harredon01@gmail.com";
                    $scope.data2.payer_id = "321321321";

                }
                $scope.fill2 = function () {
                    $scope.data3.user_type = "N";
                    $scope.data3.pse_reference2 = "CC";
                    $scope.data3.pse_reference3 = "1020716535";
                    $scope.data3.payer_email = "harredon01@gmail.com";

                }
                $scope.fill3 = function () {
                    $scope.data4.payer_email = "harredon01@gmail.com";

                }
                $scope.selectCountry = function (country_id) {
                    LocationService.getRegionsCountry(country_id).then(function (data) {
                        $scope.regions = data.data;
                        $scope.regionVisible = true;
                        $scope.cityVisible = false;

                    },
                            function (data) {

                            });
                }
                $scope.selectRegion = function (region_id) {
                    LocationService.getCitiesRegion(region_id).then(function (data) {
                        $scope.cities = data.data;
                        $scope.cityVisible = true;

                    },
                            function (data) {

                            });
                }
                $scope.selectPlace = function (country_id, region_id, city_id) {
                    $scope.data.country_id = country_id;
                    LocationService.getRegionsCountry(country_id).then(function (data) {
                        $scope.regions = data.data;
                        $scope.regionVisible = true;
                        $scope.cityVisible = false;
                        $scope.data.region_id = region_id;
                        LocationService.getCitiesRegion(region_id).then(function (data) {
                            $scope.cities = data.data;
                            $scope.cityVisible = true;
                            $scope.data.city_id = city_id;
                        },
                                function (data) {

                                });
                    },
                            function (data) {

                            });
                }
                $scope.showAddressForm = function () {
                    $scope.addAddress = true;
                    $scope.data.type = "billing";
                }
                $scope.hideAddressForm = function () {
                    $scope.addAddress = false;
                }
                $scope.useUserDebit = function () {
                    $scope.data3.user_type = "N";
                    $scope.data3.doc_type = "N";
                    $scope.data3.payer_name = $rootScope.user.name;
                    $scope.data3.payer_id = $rootScope.user.docNum;
                    $scope.data3.payer_phone = $rootScope.user.cellphone;
                    $scope.data3.payer_email = $rootScope.user.email;
                }
                $scope.useUserCredit = function () {
                    $scope.data2.cc_name = $rootScope.user.name;
                    $scope.data2.payer_name = $rootScope.user.name;
                    $scope.data2.payer_id = $rootScope.user.docNum;
                    $scope.data2.payer_phone = $rootScope.user.cellphone;
                    $scope.data2.payer_email = $rootScope.user.email;
                    $scope.addAddress = false;
                }
                $scope.useUserCash = function () {
                    $scope.data4.payer_email = $rootScope.user.email;
                    $scope.data4.payer_id = $rootScope.user.docNum;
                }
                $scope.fetchAddressForUse = function (typeFetch, typeUse) {
                    if (typeFetch == 'shipping') {
                        $scope.setAddressInfields(typeUse, $rootScope.shippingAddress);
                    } else if (typeFetch == 'other') {
                        $mdDialog.show(Modals.getAddressesPopup()).then(function (address) {
                            console.log("Got address", address);
                            $scope.setAddressInfields(typeUse, address);
                        }, function () {
                            console.log("Got nothing");
                        });
                    }
                }
                $scope.setAddressInfields = function (type, address) {
                    if (type == "payer") {
                        $scope.data2.payer_address = address.address;
                        $scope.data2.payer_postal = address.postal;
                        $scope.data2.payer_city = address.cityName;
                        $scope.data2.payer_state = address.regionName;
                        $scope.data2.payer_country = address.countryCode;
                        $scope.data2.payer_phone = address.phone;
                    } else if (type == "buyer") {
                        $scope.data2.buyer_address = address.address;
                        $scope.data2.buyer_postal = address.postal;
                        $scope.data2.buyer_city = address.cityName;
                        $scope.data2.buyer_state = address.regionName;
                        $scope.data2.buyer_country = address.countryCode;
                        $scope.data2.buyer_phone = address.phone;
                    }
                }

                $scope.showMethod = function (method) {
                    $scope.credito = false;
                    $scope.cash = false;
                    $scope.debito = false;
                    if (method == "PSE") {
                        $scope.debito = true;
                        Billing.getBanks().then(function (data) {
                            $scope.banks = data.banks;
                        },
                                function (data) {
                                });
                    }
                    if (method == "CC") {
                        $scope.data2.buyer_country = "CO";
                        $scope.data2.payer_country = "CO";
                        if ($rootScope.addresses && $rootScope.addresses.length > 0) {
                        } else {
                            $rootScope.$broadcast('getAddresses');
                        }
                        $scope.credito = true;
                    }
                    if (method == "BALOTO") {
                        $scope.cash = true;
                    }
                }
            }])
        .controller('CheckoutGatewaysCtrl', function () {

        })
        