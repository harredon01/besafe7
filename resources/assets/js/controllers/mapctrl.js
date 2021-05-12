angular.module('besafe')
        .controller('MapLocationCtrl', ['$scope', '$rootScope', 'MapService', '$cookies', function ($scope, $rootScope, MapService, $cookies) {
                $scope.map = {center: {latitude: 4.653450, longitude: -74.049605}, zoom: 8};
                $rootScope.markers = [];
                $rootScope.addressContainer = null;
                $scope.geocodrr = null;
                (function () {
                    $scope.geocodrr = new google.maps.Geocoder();
                    if (typeof EventTarget !== "undefined") {
                        let func = EventTarget.prototype.addEventListener;
                        EventTarget.prototype.addEventListener = function (type, fn, capture) {
                            this.func = func;
                            if (typeof capture !== "boolean") {
                                capture = capture || {};
                                capture.passive = false;
                            }
                            this.func(type, fn, capture);
                        };
                    }
                    ;
                }());
                const input = document.getElementById("pac-input");
                const options = {
                    componentRestrictions: {country: "co"},
                    fields: ["address_components", "geometry"],
                    strictBounds: false,
                    types: ["address"],
                };
                const autocomplete = new google.maps.places.Autocomplete(input, options);
                google.maps.event.addListener(autocomplete, 'place_changed', function () {
                    console.log("Dragend map")
                    const place = autocomplete.getPlace();
                    console.log("place_changed", place)
                    
                    if (place.geometry) {
                        $rootScope.addressContainer = place.address_components[1]['long_name']+" # " +place.address_components[0]['long_name'] ;
                        $scope.activeMarker.setPosition(new google.maps.LatLng(place.geometry.location.lat(), place.geometry.location.lng()))
                        google.maps.event.trigger($scope.activeMarker, 'dragend');
                    } else {
                        $rootScope.addressContainer = place.name;
                        $scope.geocodrr.geocode({'address': place.name}, function (results, status) {
                            if (status == 'OK') {
                                console.log("Results")
                                console.log(results)
                                $scope.activeMarker.setPosition(new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng()))
                                google.maps.event.trigger($scope.activeMarker, 'dragend');
                            } else {
                                alert('Geocode was not successful for the following reason: ' + status);
                            }
                        });
                    }
                });
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
                    $rootScope.addressContainer = $rootScope.shippingAddress.address
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
                $scope.selectCity = function (latitude, longitude) {
                    console.log("Select city: ", latitude, longitude);
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
                    if($rootScope.addressContainer){
                        $rootScope.shippingAddress.address = $rootScope.addressContainer;
                    }
                    console.log("Loading address to cookie", $rootScope.addressContainer);
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
                    $scope.error = "la url es: " + url;
                    console.log("url", url);
                    window.location.href = url;
                }


            }])