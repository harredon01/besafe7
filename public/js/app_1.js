/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


'use strict';
// angular.js main app initialization
var app = angular.module('besafe', ['besafe.constants', ]);
app.config(function ($interpolateProvider) {
    $interpolateProvider.startSymbol('{{');
    $interpolateProvider.endSymbol('}}');
}).run(function ($http) {
    $http.defaults.headers.common['X-CSRF-TOKEN'] = Laravel.csrfToken;
    $http.defaults.headers.common['Accept'] = "application/json";


}); 