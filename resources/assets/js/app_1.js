/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


'use strict';
// angular.js main app initialization
var app = angular.module('besafe', ['besafe.constants','ngCookies']);
app.config(function () {
    /*$interpolateProvider.startSymbol('{{');
    $interpolateProvider.endSymbol('}}');*/
}).run(["$http",function ($http) {
//    $http.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
//    $http.defaults.headers.common['X-XSRF-TOKEN'] = Laravel.csrfToken;
    $http.defaults.headers.common['Accept'] = "application/json";



}]); 