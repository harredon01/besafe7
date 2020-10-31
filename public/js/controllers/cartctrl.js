﻿angular.module('besafe')
        .controller('CartCtrl', ['$scope', 'Cart', '$rootScope', function ($scope, Cart, $rootScope) {
                $scope.subtotal = 0;
                $scope.total = 0
                angular.element(document).ready(function () {
                    if (window.location.href == 'https://lonchis.com.co/auth/login' || window.location.href == 'http://www.lonchis.com.co/auth/login') {
                    } else {
                        $scope.getCart();
                    }
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
                    $scope.getCart();
                });
                $scope.getCart = function () {
                    Cart.getCart().then(function (data) {
                        console.log("Get Cart", data);
                        if (data.status == 'error') {
                            alert(data.message);
                        } else {
                            $scope.items = data.items;
                            $scope.totalItems = data.totalItems;
                            $scope.subtotal = data.subtotal;
                            $scope.total = data.total;
                        }
                    },
                            function (data) {

                            });
                }
                $scope.updateCartItem = function (product_variant_id) {
                    var quantity = angular.element(document.querySelector('input[name=quantity-' + product_variant_id + ']')).val();
                    console.log("da qty: " + quantity);
                    Cart.updateCartItem(product_variant_id, quantity).then(function (data) {
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
                    },
                            function (data) {

                            });
                }
                $scope.deleteCartItem = function (product_variant_id) {
                    Cart.updateCartItem(product_variant_id, 0).then(function (data) {
                        if (data.status == 'error') {
                            alert(data.message);
                        }
                        $scope.getCart();
                    },
                            function (data) {

                            });
                }

            }])