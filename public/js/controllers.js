/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


angular.module('besafe')
        .controller('MapCtrl', function ($scope,  $rootScope, MapService, $http) {
            $scope.map = {center: {latitude: 45, longitude: -73}, zoom: 8};
            $rootScope.sharedTimeout = 8000;
            $rootScope.meTimeout = 100000;
            $rootScope.polylines = [];
            $rootScope.markers = [];
            $rootScope.after = 0;
            var url = window.location.href
            var res = url.split("/");
            $rootScope.hash = res[res.length-1];
            console.log($rootScope.hash);
            MapService.initMap();
            // Execute action on remove popover
            $rootScope.$on('StartTracking', function () {
                $rootScope.tracking = true;
                MapService.updateLocations(true);
            });
            $rootScope.$on('NoSharers', function () {
                alert('El usuario ha dejado de compartir');
            });           
            

        })