angular.module('besafe')
        .controller('MapDashCtrl',['$scope', '$rootScope', 'MapDashService', 'API', function ($scope, $rootScope, MapDashService, API) {
            $scope.map = {center: {latitude: 45, longitude: -73}, zoom: 8};
            $rootScope.sharedTimeout = 8000;
            $rootScope.sharedTimeout2 = 180000;
            $rootScope.meTimeout = 100000;
            $scope.trackingIcon = API.icons+"icon.svg";
            $scope.followingIcon = API.icons+"maps-and-flags.svg";




            $scope.showInMap = function (dauser) {
                var marker;
                for (mark in $rootScope.markers) {
                    if ($rootScope.markers[mark]) {
                        if ($rootScope.markers[mark].user_id == dauser) {
                            marker = $rootScope.markers[mark];
                        }
                    }
                }
                var latLng = marker.getPosition();
                MapDashService.setActive("user", dauser, latLng);
                console.log("click on markers");
                google.maps.event.trigger(marker, 'click');// setCenter takes a LatLng object
            }
            $scope.updateLocations = function () {
                google.maps.event.trigger($rootScope.map, 'resize');
                $rootScope.rootMapMarkers = true;
                $rootScope.mapSemaphore = true;
                $rootScope.tracking = true;
                if(!$rootScope.trackingActive){
                    $rootScope.memarker.setMap(null);
                }
                MapDashService.clearTemps();
                MapDashService.updateLocations();
                MapDashService.showAllActiveMarkers();
            }

            // Execute action on remove popover
            $rootScope.$on('StartTracking', function () {
                $rootScope.tracking = true;
                console.log("start tracking");
                window.localStorage.setItem("USER_TRACKING", true);
                MapDashService.updateLocations();
            });
            $rootScope.$on('NoSharers', function () {
                $rootScope.tracking = false;
                if($rootScope.mapActiveType != "me" && $rootScope.mapActiveType != "trip"){
                    alert('Nadie te esta compartiendo');
                }
                for (polyline in $rootScope.polylines) {
                    MapDashService.deleteUserMarkers($rootScope.polylines[polyline].user_id);
                    $rootScope.polylines[polyline].setMap(null);
                    $rootScope.polylines.splice(polyline, 1);
                }
            });
            $scope.initMap = function () {
                $rootScope.mapActive = true;
                console.log("Entering map");
                if (!$rootScope.mapLoaded) {
                    console.log("Map not loaded loading now");
                    $rootScope.mapLoaded = true;
                    $rootScope.map = MapDashService.createMap("map");
                    setTimeout(function () {
                        MapDashService.postMap();
                    }, 500);

                }
                MapDashService.updateLocations();

            }
            $scope.initMap();
            $scope.$on('$stateChangeSuccess', function (event, toState, toParams, fromState, fromParams) {
                if (toState.name == 'tab.map') {
                    $scope.initMap();
                }

            });
            $scope.$on('$stateChangeStart', function (event, toState, toParams, fromState, fromParams) {
                if (fromState.name == 'tab.map') {
                    $rootScope.mapActive = false;
                }
            });

        }])