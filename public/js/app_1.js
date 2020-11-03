/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


'use strict';
// angular.js main app initialization
var app = angular.module('besafe', ['besafe.constants', 'ngCookies','ngMaterial','ngAnimate','ngAria']);
app.config(function () {
    /*$interpolateProvider.startSymbol('{{');
     $interpolateProvider.endSymbol('}}');*/
}).run(["$http", '$rootScope', '$cookies', 'Users','Modals', function ($http, $rootScope, $cookies, Users,Modals) {
//    $http.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
//    $http.defaults.headers.common['X-XSRF-TOKEN'] = Laravel.csrfToken;
        console.log("Searching for user");
        Users.getUser().then(function (data) {
                console.log("user loaded",data);
                $rootScope.user = data.user;
            },
                    function (data) {

                    });
        let shipping = $cookies.get("shippingAddress");
        if (shipping && shipping.length > 0) {
            $rootScope.shippingAddress = JSON.parse(shipping);
        }
        let results = Modals.getAllUrlParams(null);
        console.log("Checking params",results);
        if(results.merchant_id && results.merchant_id.length>0){
            $rootScope.merchant_id = results.merchant_id;
        }
        if(results.lat && results.lat.length>0){
            console.log("saving lat");
            $rootScope.lat = results.lat;
        }
        $http.defaults.headers.common['Accept'] = "application/json";



    }]); 