angular.module('besafe')

        .controller('CheckoutCartCtrl', function ($scope, $rootScope, LocationService, Users, Checkout, Products, $window) {
            $scope.data = {};
            angular.element(document).ready(function () {
                $scope.getCart();
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
            $scope.getCart = function () {
                Products.getCheckoutCart().then(function (data) {
                    if (data.status == 'error') {
                        alert(data.message);
                    } else {
                        if (data.items.length > 0) {
                            $scope.items = data.items;
                            $scope.totalItems = data.totalItems;
                            $scope.subtotal = data.subtotal;
                            $scope.shipping = data.shipping;
                            $scope.tax = data.tax;
                            $scope.sale = data.sale;
                            $scope.coupon = data.coupon;
                            $scope.discount = data.sale + data.coupon;
                            $scope.total = data.total;
                            if (data.is_shippable) {
                                $rootScope.$broadcast('Shippable');
                            } else {
                                $rootScope.$broadcast('NotShippable');
                            }
                        } else {
                            $window.location.href = '/products';
                        }


                    }
                },
                        function (data) {

                        });
            }
            $scope.updateCartItem = function (product_variant_id) {
                var quantity = angular.element(document.querySelector('input[name=check-quantity-' + product_variant_id + ']')).val();
                Products.updateCartItem(product_variant_id, quantity).then(function (data) {
                    if (data.status == 'error') {
                        alert(data.message);
                    }
                    $scope.getCart();
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
            $scope.deleteCartItem = function (product_variant_id) {
                Products.updateCartItem(product_variant_id, 0).then(function (data) {
                    if (data.status == 'error') {
                        alert(data.message);
                    }
                    $scope.getCart();
                },
                        function (data) {

                        });
            }
            $scope.save = function (isvalid) {
                $scope.submitted = true;
                if (isvalid) {
                    Users.saveAddress($.param($scope.data)).then(function (data) {
                        $scope.getAddresses();
                        $scope.data = {};
                        $scope.submitted = false;
                    },
                            function (data) {

                            });
                }
            }
            $scope.setCoupon = function (coupon) {

                Checkout.setCoupon(coupon).then(function (data) {
                    angular.element(document.querySelector('.replace-address')).html(data);
                },
                        function (data) {
                        });
            }
            $rootScope.$on('updateCheckoutCart', function (event, args) {
                $scope.getCart();

            });
        })
        .controller('CheckoutShippingCtrl', function ($scope, $rootScope, LocationService, Users, Checkout, Products, $window) {
            $scope.data = {};
            $scope.regionVisible = false;
            $scope.cityVisible = false;
            $scope.addAddress = false;
            $scope.visible = false;
            $rootScope.shippingAddressSet = true;
            $rootScope.shippingCondition = true;
            $rootScope.billingAddressSet = false;
            $rootScope.paymentMethodSet = false;

            $rootScope.$on('Shippable', function (event, args) {
                $rootScope.shippingAddressSet = false;
                $rootScope.shippingCondition = false;
                $rootScope.billingAddressSet = false;
                $rootScope.paymentMethodSet = false;
                $scope.visible = true;
            });


            angular.element(document).ready(function () {
                $scope.getAddresses();
                LocationService.getCountries("").then(function (data) {
                    $rootScope.countries = data.data;
                },
                        function (data) {

                        });
            });
            $rootScope.$on('NotShippable', function (event, args) {
                $scope.visible = false;

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
                    Users.saveAddress($.param($scope.data)).then(function (data) {
                        $scope.getAddresses();
                        $scope.selectAddress(data.address.id);
                        $scope.data = {};
                        $scope.submitted = false;
                        $scope.hideAddressForm();
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
            $scope.selectAddress = function (address_id) {
                Checkout.setShippingAddress(address_id).then(function (data) {
                    $rootScope.shippingAddressSet = true;
                    $scope.getShippingConditions(address_id);
                    for (x in $rootScope.addresses) {
                        console.log($rootScope.addresses[x].id);
                        console.log(address_id);
                        console.log("");
                        if ($rootScope.addresses[x].id == address_id) {
                            $rootScope.addresses[x].selectedB = true;
                        } else {
                            $rootScope.addresses[x].selectedB = false;
                        }
                    }
                },
                        function (data) {

                        });
            }
            $scope.getShippingConditions = function (address_id) {
                Checkout.getShippingConditions(address_id).then(function (data) {
                    for (item in data) {
                        data[item].value = data[item].value.replace("+", "");
                    }
                    $scope.shippingMethods = data;
                },
                        function (data) {

                        });
            }
            $scope.showAddressForm = function () {
                $scope.addAddress = true;
                $scope.data.type = "shipping";
            }
            $scope.hideAddressForm = function () {
                $scope.addAddress = false;
            }
            $scope.setShippingCondition = function (condition_id) {
                Checkout.setShippingCondition(condition_id).then(function (data) {
                    $rootScope.shippingCondition = true;
                    $rootScope.$broadcast('updateCheckoutCart');
                    for (x in $scope.shippingMethods) {
                        if ($scope.shippingMethods[x].id == condition_id) {
                            $scope.shippingMethods[x].selected = true;
                        } else {
                            $scope.shippingMethods[x].selected = false;
                        }
                    }
                },
                        function (data) {

                        });
            }
        })
        .controller('CheckoutBillingCtrl', function ($scope, $rootScope, LocationService, Users, Checkout, Products, $window) {
            $scope.data = {};
            $scope.data2 = {};
            $scope.data3 = {};
            $scope.data4 = {};
            $scope.banks = [];
            $scope.regionVisible = false;
            $scope.cityVisible = false;
            $scope.addAddress = false;
            $scope.credito = false;
            $scope.cash = false;
            $scope.isDigital = false;
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
            $rootScope.$on('Shippable', function (event, args) {
                $scope.isDigital = false;
            });
            $rootScope.$on('NotShippable', function (event, args) {
                $scope.isDigital = true;
            });
            $scope.save = function (isvalid) {
                $scope.submitted = true;
                if (isvalid) {
                    Users.saveAddress($.param($scope.data)).then(function (data) {
                        $scope.getAddresses();
                        $scope.selectAddress(data.address.id);
                        $scope.data = {};
                        $scope.submitted = false;
                        $scope.hideAddressForm();
                    },
                            function (data) {

                            });
                }
            }
            $scope.payCreditCard = function (isvalid) {
                $scope.submitted2 = true;
                if (isvalid) {
                    Checkout.payCreditCard($.param($scope.data2)).then(function (data) {
                        //$scope.data2 = {};
                    },
                            function (data) {

                            });
                }
            }
            $scope.payDebitCard = function (isvalid) {
                $scope.submitted3 = true;
                if (isvalid) {
                    Checkout.payDebitCard($.param($scope.data3)).then(function (data) {
                        window.location.href = data.transactionResponse.extraParameters.BANK_URL;
                    },
                            function (data) {

                            });
                }
            }
            $scope.payCash = function (isvalid) {
                $scope.submitted4 = true;
                if (isvalid) {
                    Checkout.payCash($.param($scope.data4)).then(function (data) {
                        window.location.href = data.transactionResponse.extraParameters.URL_PAYMENT_RECEIPT_HTML;
                    },
                            function (data) {

                            });
                }
            }

            $scope.selectAddress = function (address_id) {
                Checkout.setBillingAddress(address_id).then(function (data) {
                    $rootScope.billingAddressSet = true;
                    for (x in $rootScope.addresses) {
                        if ($rootScope.addresses[x].id = address_id) {
                            $rootScope.addresses[x].selected = true;
                        } else {
                            $rootScope.addresses[x].selected = false;
                        }
                    }
                },
                        function (data) {

                        });
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
            $scope.showMethod = function (method) {
                $scope.credito = false;
                $scope.cash = false;
                $scope.debito = false;
                if (method == "PSE") {
                    $scope.debito = true;
                    Checkout.getBanks().then(function (data) {
                        $scope.banks = data.banks;
                    },
                            function (data) {

                            });
                }
                if (method == "CC") {
                    $scope.credito = true;
                }
                if (method == "BALOTO") {
                    $scope.cash = true;
                }
            }
        })
        