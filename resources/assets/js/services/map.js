angular.module('besafe')
        .factory('MapService',['$q', '$rootScope', 'LocationService',function ($q, $rootScope, LocationService) {
            var myLatlng;
            var mapOptions;
            $rootScope.map;
            $rootScope.iconpath = "http://hoovert.com/images/icons";
            $rootScope.markers = [];
            $rootScope.polylines = [];
            var infoWindow;

            var createMap = function (latitude, longitude) {

                var myLatlng = new google.maps.LatLng(latitude, longitude);
                mapOptions = {
                    center: myLatlng,
                    zoom: 12,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                };
                if (document.getElementById("map")) {
                    $rootScope.map = new google.maps.Map(document.getElementById("map"), mapOptions);
                    infoWindow = new google.maps.InfoWindow();
                    $rootScope.geocoder = new google.maps.Geocoder();
                    $rootScope.mapActive = true;
                    $rootScope.mapLoaded = true;
                    $rootScope.tracking = true;
                    $rootScope.mapSemaphore = true;
                }
            }
            var createMarker = function (info) {
                damap = $rootScope.map;
                console.log("info received in marker");
                console.log(JSON.stringify(info));
                var pinclass = "";
                if (info.activity == "still") {
                    pinclass = "masculine-avatar.svg";
                    console.log("entro still");
                } else if (info.activity == "on_foot") {
                    console.log("entro on foot");
                    pinclass = "pedestrian-walking.svg";
                } else if (info.activity == "in_vehicle") {
                    pinclass = "car-front.svg";
                } else if (info.activity == "on_bicycle") {
                    pinclass = "cycling.svg";
                } else if (info.activity == "running") {
                    pinclass = "running.svg";
                } else if (info.activity == "") {
                    console.log("entro blank");
                    pinclass = "map-pin.svg";
                }
                var marker = new google.maps.Marker({
                    id: info.id,
                    user_id: info.user_id,
                    position: new google.maps.LatLng(info.lat, info.long),
                    map: damap,
                    animation: google.maps.Animation.DROP,
                    report_time: info.report_time,
                    speed: info.speed,
                    activity: info.activity,
                    battery: info.battery,
                    name: info.name,
                    status: info.status,
                    islast: info.islast,
                    icon: {
                        fillColor: '#ffffff',
                        url: $rootScope.iconpath + "/" + pinclass,
                        fillOpacity: 1,
                        strokeColor: '#ffffff',
                        strokeWeight: 0,
                        scaledSize: new google.maps.Size(35, 35)
                    }
                });
                marker.content = '<div class="infoWindowContent">' + info.id + ' ' + info.name + '</div>';
                google.maps.event.addListener(marker, 'click', function () {
                    infoWindow.setContent('<h2>' + marker.report_time + '</h2>' + marker.content);
                    infoWindow.open($rootScope.map, marker);
                });
                return marker;
            }
            var createLocationMarker = function (lat, long, trigger) {
                damap = $rootScope.map;
                console.log("info received in marker");
                var marker = new google.maps.Marker({
                    position: new google.maps.LatLng(lat, long),
                    map: damap,
                    animation: google.maps.Animation.DROP,
                    draggable: true,
                });
                google.maps.event.addListener(marker, 'dragend', function () {
                    let location = marker.getPosition();
                    setCenterMap(location.lat(), location.lng());
                    getAddressAndPostal(location.lat(), location.lng(), 1);
                });
                if (trigger) {
                    google.maps.event.trigger(marker, 'dragend');
                }
                $rootScope.locationMarker = marker;
                return marker;
            }
            var createReport = function (info) {
                damap = $rootScope.map;

                var marker = new google.maps.Marker({
                    id: info.id,
                    position: new google.maps.LatLng(info.lat, info.long),
                    map: damap,
                    animation: google.maps.Animation.DROP,
                    created_at: info.created_at,
                    name: info.name
                });
                marker.content = '<div class="infoWindowContent">' + info.id + ' ' + info.name + '</div>';
                google.maps.event.addListener(marker, 'click', function () {
                    infoWindow.setContent('<h2>' + marker.created_at + '</h2>' + marker.content);
                    infoWindow.open($rootScope.map, marker);
                });
                return marker;
            }
            var createStop = function (stop) {
                damap = $rootScope.map;
                console.log("creating stop", stop)
                var marker = new google.maps.Marker({
                    id: stop.id,
                    icon: stop.icon,
                    position: new google.maps.LatLng(stop.lat, stop.long),
                    map: damap,
                    animation: google.maps.Animation.DROP,
                    name: stop.name
                });
                marker.content = '<div class="infoWindowContent">' + stop.id + ' ' + stop.name + '</div>';
                google.maps.event.addListener(marker, 'click', function () {
                    infoWindow.setContent('<h2>' + marker.created_at + '</h2>' + marker.content);
                    infoWindow.open($rootScope.map, marker);
                });
                return marker;
            }
            var createRoute = function (route, color) {
                damap = $rootScope.map;
                console.log("createRoute", route)
                var flightPlanCoordinates = route.stopsLat;
                var flightPath = new google.maps.Polyline({
                    path: flightPlanCoordinates,
                    geodesic: true,
                    strokeColor: color,
                    strokeOpacity: 1.0,
                    strokeWeight: 2
                });
                flightPath.setMap(damap);
                return flightPath;
            }
            var getColor = function () {
                let routeColors = ["#FF0000", "#800000", "#FFFF00", "#808000", "#00FF00", "#008000", "#00FFFF", "#008080", "#0000FF", "#000080", "#FF00FF", "#800080"];
                let random = Math.round(Math.random() * (routeColors.length - 1));
                return routeColors[random];
            }
            var createPolygon = function (zone, color) {
                damap = $rootScope.map;
                console.log("createRoute", zone)
                var triangleCoords = JSON.parse(zone.coverage);

                // Construct the polygon.
                var bermudaTriangle = new google.maps.Polygon({
                    id: zone.id,
                    paths: triangleCoords,
                    strokeColor: color,
                    strokeOpacity: 0.8,
                    editable: true,
                    strokeWeight: 2,
                    fillColor: '#FF0000',
                    fillOpacity: 0.35,
                    draggable: true,
                    geodesic: true
                });
                bermudaTriangle.setMap(damap);
                bermudaTriangle.getPaths().forEach(function (path, index) {
                    google.maps.event.addListener(path, 'insert_at', function () {
                        console.log('zzinsert_at', zone.id);
                        $rootScope.$broadcast("zone-updated", zone);
                        zone.isActive = true;
                    });
                    google.maps.event.addListener(path, 'remove_at', function () {
                        console.log('zzinsert_at', zone.id);
                        $rootScope.$broadcast("zone-updated", zone);
                        zone.isActive = true;
                    });
                    google.maps.event.addListener(path, 'set_at', function () {
                        console.log('zzinsert_at', zone.id);
                        $rootScope.$broadcast("zone-updated", zone);
                    });
                });

                google.maps.event.addListener(bermudaTriangle, 'dragend', function () {
                    console.log('set_at.', bermudaTriangle.id);
                });
                return bermudaTriangle;
            }

            var consoleLogMarkers = function (damarkers) {
                for (marker in damarkers) {
                    if (damarkers[marker]) {
                        console.log("id: " + damarkers[marker].id);
                        console.log("speed: " + damarkers[marker].speed);
                        console.log("activity: " + damarkers[marker].activity);
                        console.log("battery: " + damarkers[marker].battery);
                        console.log("lat: " + damarkers[marker].lat);
                        console.log("long: " + damarkers[marker].long);
                        console.log("name: " + damarkers[marker].name);
                        console.log("type: " + damarkers[marker].type);
                        console.log("user_id: " + damarkers[marker].user_id);
                        console.log("report_time: " + damarkers[marker].report_time);
                    } else {
                        console.log("Marker is null: " + marker);
                        console.log(damarkers[marker]);
                    }

                }
            }
            var updateLocations = function (hash, page) {
                console.log("Updating locations");
                if ($rootScope.mapActive) {
                    console.log("Map Active");
                    if ($rootScope.mapSemaphore) {
                        console.log("rootscope mapsemaphore true");
                        $rootScope.mapSemaphore = false;
                        var where = "?page=" + page + "&hash_id=" + hash;
                        getServerLocations(where, hash);
                    }
                }
            }
            var deleteUserMarkers = function (dauser_id) {
                for (marker in $rootScope.markers) {
                    if ($rootScope.markers[marker]) {
                        if ($rootScope.markers[marker].user_id == dauser_id) {
                            $rootScope.markers[marker].setMap(null);
                            $rootScope.markers.splice(marker, 1);
                        }
                    } else {
                        $rootScope.markers.splice(marker, 1);
                    }
                }
            }
            var findUserInPolyline = function (dauser_id) {
                for (polyline in $rootScope.polylines) {
                    if ($rootScope.polylines[polyline].user_id == dauser_id) {
                        return $rootScope.polylines[polyline];
                    }
                }
                return null;
            };
            var addToUserPolyline = function (info) {
                console.log("add user to polyline");
                if (!$rootScope.mapLoaded) {
                    $rootScope.mapLoaded = true;
                    createMap(info.lat, info.long);
                }
                var coords = new google.maps.LatLng(info.lat, info.long);
                var poly = findUserInPolyline(info.user_id);
                if (poly) {
                    deleteUserMarkers(info.user_id);
                    console.log("pos added to existing poly");
                    var path = poly.getPath();
                    path.push(coords);
                    if (info.status == "emergency") {
                        poly.status = "emergency";
                    }
                } else {
                    poly = new google.maps.Polyline({
                        strokeColor: '#000000',
                        strokeOpacity: 1.0,
                        strokeWeight: 3,
                        user_id: info.user_id,
                        name: info.name,
                        status: info.status,
                        visible: true,
                        isActive: false,
                    });
                    var path = poly.getPath();
                    path.push(coords);
                    poly.setMap($rootScope.map);
                    console.log("new poly created and pos added: " + poly.user_id);
                    $rootScope.polylines.push(poly);
                }
                info.owner_type = "user";
                setCenterMap(info.lat, info.long);
                // Add a new marker at the new plotted point on the polyline.
                var marker = createMarker(info);
                $rootScope.activeMarker = marker;
                $rootScope.markers.push(marker);
            };
            var setCenterMap = function (lat, long) {
                console.log("Setting map center");
                myLatlng = new google.maps.LatLng(lat, long);
                if ($rootScope.map) {
                    $rootScope.map.setZoom(15);
                    $rootScope.map.setCenter(myLatlng);
                }
            }
            var checkMarkers = function (info) {
                var i = 0;
                for (target in info) {
                    if (info[target].id) {
                        checkLocationExists(info[target]);
                    }
                }
            }
            var checkLocationExists = function (location) {
                console.log("Checking database for location: " + location.id);
                if ($rootScope.activeMarker) {
                    if (location.id > $rootScope.activeMarker.id) {
                        location.status = true;
                        console.log("Location exists: ");
                        addToUserPolyline(location);
                    }
                } else {
                    addToUserPolyline(location);
                }

            };
            var postMapLocation = function () {
                $rootScope.map.addListener('dblclick', function (e) {
                    var location = e.latLng;
                    $rootScope.locationMarker.setPosition(location);
                    google.maps.event.trigger($rootScope.locationMarker, 'dragend');
                    setCenterMap(location.lat(), location.lng());
                    getAddressAndPostal(location.lat(), location.lng(), 1);
                });
                $rootScope.map.addListener('click', function (e) {
                    var location = e.latLng;
                    $rootScope.locationMarker.setPosition(location);
                    google.maps.event.trigger($rootScope.locationMarker, 'dragend');
                    setCenterMap(location.lat(), location.lng());
                    getAddressAndPostal(location.lat(), location.lng(), 1);
                });
            };

            var decodeAddressFromLatResult = function (results) {

                let container = "";
                try {
                    container = results[0].formatted_address;
                } catch (err) {

                }
                return container;
            }
            /**
             * prepares report data for creating a marker
             */
            var decodePostalFromLatResult = function (results) {
                if (results.length > 0) {
                    let container;
                    let address = results[0]
                    let found = false
                    let i = 0;
                    let components = address.address_components
                    let postal = "";
                    do {
                        container = components[i]
                        for (let item in container.types) {
                            if (container.types[item] == "postal_code") {
                                postal = container.long_name;
                                found = true;
                            }
                        }
                        i++;
                        if (i >= results.length) {
                            found = true;
                        }
                    } while (found == false);
                    console.log("decodePostalFromLatResult", postal);
                    return postal;
                }

            }
            var getAddressAndPostal = function (lat, long, attempts) {

                getAddressFromLat(lat, long).then((resp) => {
                    console.log("Address from lat", resp);
                    if (resp) {
                        let container = {
                            address: decodeAddressFromLatResult(resp),
                            postal: decodePostalFromLatResult(resp),
                            lat:lat,
                            long:long
                        }
                        $rootScope.shippingAddress = container;
                        console.log("Result", container);
                    } else {
                        console.log("Attempts", attempts);
                        if (attempts < 8) {
                            attempts++;
                            let vm = this;
                            setTimeout(function () {
                                vm.getAddressAndPostal(lat, long, attempts)
                            }, 1000);
                        }
                    }

                }).catch((error) => {
                    console.log('Error getting location', error);
                });
            }
            var getAddressFromLat = function (lat, long) {
                var deferred = $q.defer();
                const latlng = {
                    lat: parseFloat(lat),
                    lng: parseFloat(long),
                };
                $rootScope.geocoder.geocode({location: latlng}, (results, status) => {
                    if (status === "OK") {
                        if (results[0]) {
                            deferred.resolve(results);
                        } else {
                            deferred.resolve([]);
                        }
                    } else {
                        window.alert("Geocoder failed due to: " + status);
                    }
                });
                return deferred.promise;
            }
            var getServerLocations = function (where, hash) {
                LocationService.shared(where).then(function (data) {
                    if (data.total == 0) {
                        $rootScope.isActive = false;
                        alert("User has no active locations");
                    } else {
                        var pins = data.data;
                        checkMarkers(pins);
                        if (data.page < data.last_page) {
                            var dapage = data.page + 1;
                            var where = "?page=" + dapage + "&hash_id=" + hash;
                            getServerLocations(where, hash);
                        } else {
                            $rootScope.mapSemaphore = true;
                            setTimeout(function () {
                                console.log("Timeout Triggered");
                                updateLocations(hash, data.page);
                            }, $rootScope.sharedTimeout);
                        }
                    }
                },
                        function (data) {
                            console.log(data);
                        });
            }
            return {
                updateLocations: updateLocations,
                createMap: createMap,
                postMapLocation: postMapLocation,
                createStop: createStop,
                createRoute: createRoute,
                createLocationMarker: createLocationMarker,
                getColor: getColor,
                getAddressAndPostal: getAddressAndPostal,
                createPolygon: createPolygon,
                createReport: createReport
            };
        }])
        