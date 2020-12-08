/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


'use strict';
// angular.js main app initialization
var app = angular.module('besafe', ['besafe.constants', 'ngCookies', 'ngMaterial', 'ngAnimate', 'ngAria']);
app.config(function () {
    /*$interpolateProvider.startSymbol('{{');
     $interpolateProvider.endSymbol('}}');*/
}).run(["$http", '$rootScope', '$cookies', 'Users', 'Modals','$location','$anchorScroll', function ($http, $rootScope, $cookies, Users, Modals,$location,$anchorScroll) {
//    $http.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
//    $http.defaults.headers.common['X-XSRF-TOKEN'] = Laravel.csrfToken;
        console.log("Searching for user");
        angular.element(document).ready(function () {
            let screenWidth = window.innerWidth;
            console.log("Screen width: ", screenWidth);
            let url = window.location.href;
            if (screenWidth < 450 && !url.includes("checkout")) {
                setTimeout(function () {
                    var old = $location.hash();
                    $location.hash('mobile-anchor');
                    $anchorScroll();
                    $location.hash(old);
                }, 1500);
            }

        });
        handleCartCookie();
        Users.getUser().then(function (data) {
            console.log("user loaded", data);
            $rootScope.user = data.user;
            $rootScope.hasSavedCard = data.savedCard;
            if (data.merchants > 0) {
                $rootScope.hasMerchants = true;
            } else {
                $rootScope.hasMerchants = false;
            }
            console.log("Has saved card", $rootScope.hasSavedCard);
            $rootScope.$broadcast('user_loaded');
        },
                function (data) {
                    document.getElementById("mi_cuenta").remove();
                    document.getElementById("mi_cuenta2").remove();
                });
        getCart();
        let shipping = $cookies.get("shippingAddress");
        if (shipping && shipping.length > 0) {
            $rootScope.shippingAddress = JSON.parse(shipping);
        }
        let results = Modals.getAllUrlParams(null);
        console.log("Checking params", results);
        if (results.merchant_id && results.merchant_id.length > 0) {
            $rootScope.merchant_id = results.merchant_id;
        }
        if (results.lat && results.lat.length > 0) {
            console.log("saving lat");
            $rootScope.lat = results.lat;
        }
        $http.defaults.headers.common['Accept'] = "application/json";
        function handleCartCookie() {
            let uuid = $cookies.get("cart-uuid");
            if (uuid && uuid.length > 0) {
                console.log("Cart cookie found");
            } else {
                let uuid = generateUUID();
                console.log("setting cart cookie");
                $cookies.put("cart-uuid", uuid, {path: "/"});
            }
            $http.defaults.headers.common['X-device-id'] = uuid;
        }
        function generateUUID() { // Public Domain/MIT
            var d = new Date().getTime();//Timestamp
            var d2 = (performance && performance.now && (performance.now() * 1000)) || 0;//Time in microseconds since page-load or 0 if unsupported
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                var r = Math.random() * 16;//random number between 0 and 16
                if (d > 0) {//Use timestamp until depleted
                    r = (d + r) % 16 | 0;
                    d = Math.floor(d / 16);
                } else {//Use microseconds since page-load if supported
                    r = (d2 + r) % 16 | 0;
                    d2 = Math.floor(d2 / 16);
                }
                return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
            });
        }
        function getCart() {
            console.log("Get cart");
            $rootScope.$broadcast('updateHeadCart');
        }
        $rootScope.changeShippingHeader = function () {
            console.log("changeShippingHeader");
            $rootScope.$broadcast('updateShippingAddress');
        }




    }]); 