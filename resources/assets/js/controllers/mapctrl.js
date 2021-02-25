angular.module('besafe')
        .controller('MapLocationCtrl', ['$scope', '$rootScope', 'MapService', '$cookies', function ($scope, $rootScope, MapService, $cookies) {
                $scope.map = {center: {latitude: 4.653450, longitude: -74.049605}, zoom: 8};
                $rootScope.markers = [];
                $rootScope.mapLoaded = false;
                $scope.error = "nada";
                var url = window.location.href;
                var res = url.split("/map/");
                console.log("url");
                console.log(JSON.stringify(res));
                MapService.createMap(4.653450, -74.049605);
                MapService.postMapLocation();
                $scope.activeMarker = MapService.createLocationMarker(4.653450, -74.049605, true);
                console.log(navigator.geolocation);
                if ($rootScope.shippingAddress && $rootScope.shippingAddress.lat) {
                    console.log("Loading address from cookie", $rootScope.shippingAddress);
                    $scope.activeMarker.setPosition(new google.maps.LatLng($rootScope.shippingAddress.lat, $rootScope.shippingAddress.long))
                    google.maps.event.trigger($scope.activeMarker, 'dragend');
                } else {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(function (position) {
                            $scope.$apply(function () {
                                console.log("Position", position);
                                $scope.position = position;
                                $scope.activeMarker.setPosition(new google.maps.LatLng(position.coords.latitude, position.coords.longitude))
                                google.maps.event.trigger($scope.activeMarker, 'dragend');
                            });
                        });
                    } 
                }
                $scope.selectCity = function (latitude,longitude) {
                    console.log("Select city: ",latitude,longitude);
                    $scope.activeMarker.setPosition(new google.maps.LatLng(latitude, longitude));
                    google.maps.event.trigger($scope.activeMarker, 'dragend'); 
                }

                $scope.default = function () {
                    MapService.createLocationMarker(4.653450, -74.049605, false);
                }
                $scope.upZoom = function () {
                    let z = $rootScope.map.getZoom();
                    z++;
                    $rootScope.map.setZoom(z);
                }
                $scope.downZoom = function () {
                    let z = $rootScope.map.getZoom();
                    z--;
                    $rootScope.map.setZoom(z);
                }
                $scope.cancel = function () {
                    window.history.back();
                }
                $scope.saveLocation = function () {
                    console.log("Loading address to cookie", $rootScope.shippingAddress);
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
                    $scope.error = "la url es: "+url;
                    console.log("url", url);
                    window.location.href = url;
                }


            }])