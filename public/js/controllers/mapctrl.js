angular.module('besafe')
        .controller('MapCtrl', function ($scope, $rootScope, MapService) {
            $scope.map = {center: {latitude: 45, longitude: -73}, zoom: 8};
            $rootScope.sharedTimeout = 8000;
            $rootScope.meTimeout = 100000;
            $rootScope.polylines = [];
            $rootScope.activeMarker = null;
            $rootScope.markers = [];
            $rootScope.mapLoaded = false;
            var url = window.location.href
            var res = url.split("/map/");
            console.log("url");
            console.log(JSON.stringify(res));
            if (res.length == 2) {
                $rootScope.hash = res[res.length - 1];
                console.log($rootScope.hash);
                MapService.createMap(45, -73);
                MapService.updateLocations($rootScope.hash,1);
            } else {
                var res = url.split("/safereportsext/");
                if (res.length == 2) {
                    var latitude = angular.element(document.querySelector('.latitude'));
                    var longitude = angular.element(document.querySelector('.longitude'));
                    var id = angular.element(document.querySelector('.id'));
                    var name = angular.element(document.querySelector('.name'));
                    var created_at = angular.element(document.querySelector('.created_at'));
                    var report = {
                        lat: latitude.html().trim(),
                        long: longitude.html().trim(),
                        id: id.html().trim(),
                        name: name.html().trim(),
                        created_at: created_at.html().trim(),
                    }
                    var myEl = angular.element(document.querySelector('#some-id'));
                    MapService.createMap(report.lat, report.long);
                    MapService.createReport(report);
                }
            }



        })