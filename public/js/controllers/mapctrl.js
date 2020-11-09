angular.module('besafe')
        .controller('MapLocationCtrl', ['$scope', '$rootScope', 'MapService', '$cookies', function ($scope, $rootScope, MapService, $cookies) {
                $scope.map = {center: {latitude: 4.653450, longitude: -74.049605}, zoom: 8};
                $rootScope.activeMarker = null;
                $rootScope.markers = [];
                $rootScope.mapLoaded = false;
                var url = window.location.href;
                var res = url.split("/map/");
                console.log("url");
                console.log(JSON.stringify(res));
                MapService.createMap(4.653450, -74.049605);
                MapService.postMapLocation();
                console.log(navigator.geolocation);
                if ($rootScope.shippingAddress && $rootScope.shippingAddress.lat) {
                    console.log("Loading address from cookie",$rootScope.shippingAddress);
                    MapService.createLocationMarker($rootScope.shippingAddress.lat, $rootScope.shippingAddress.long, true);
                } else {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(function (position) {
                            $scope.$apply(function () {
                                console.log("Position", position);
                                $scope.position = position;
                                MapService.createLocationMarker(position.coords.latitude, position.coords.longitude, true);
                            });
                        }, function (position) {
                            $scope.$apply(function () {
                                console.log("Position", position);
                                $scope.initMap();
                            });
                        });
                    } else {
                        $scope.default();
                    }
                }

                $scope.default = function () {
                    MapService.createLocationMarker(4.653450, -74.049605, false);
                }
                $scope.cancel = function () {
                    window.history.back();
                }
                $scope.saveLocation = function () {
                    console.log("Loading address to cookie",$rootScope.shippingAddress);
                    $cookies.put("shippingAddress", JSON.stringify($rootScope.shippingAddress), {path: "/"});
                    console.log("shippingAddress", JSON.stringify($rootScope.shippingAddress));
                    let locationS = $cookies.get('locationRefferrer');
                    $cookies.remove('locationRefferrer');
                    let url = "";
                    if (locationS && locationS.length > 0) {
                        url = "";
                        console.log("location", locationS);
                        if (locationS.includes('?')) {
                            url = locationS + "&lat=" + $rootScope.shippingAddress.lat + "&long=" + $rootScope.shippingAddress.long
                        } else {
                            url = locationS + "?lat=" + $rootScope.shippingAddress.lat + "&long=" + $rootScope.shippingAddress.long
                        }
                        console.log("url", url);
                        //url = locationS;
                    } else {
                        url = "/?lat=" + $rootScope.shippingAddress.lat + "&long=" + $rootScope.shippingAddress.long
                    }
                    console.log("url", url);
                    window.location.href = url;
                }


            }])