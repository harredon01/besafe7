angular.module('besafe')
        .factory('MapDashService',['$q', '$rootScope', 'LocationService', function ($q, $rootScope, LocationService) {
            var myLatlng;
            var mapOptions;
            $rootScope.map;
            $rootScope.iconpath = "http://hoovert.com/images/icons";
            $rootScope.meicon = "wasp.svg";
            $rootScope.markers = [];
            $rootScope.polylines = [];
            var infoWindow;

            var createMap = function (element) {
                var dalength = $rootScope.markers.length;
                var zoom = 12;
                var myLatlng = new google.maps.LatLng(4.704296, -74.073775);

                mapOptions = {
                    center: myLatlng,
                    zoom: zoom,
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                };

                var map = new google.maps.Map(document.getElementById(element), mapOptions);
                infoWindow = new google.maps.InfoWindow();
                if (element == "map") {
                    $rootScope.mapActive = true;
                    $rootScope.mapLoaded = true;
                    $rootScope.mapSemaphore = true;
                }


                return map;
            }
            var setCenterMap = function (lat, long) {
                console.log("Setting map center");
                myLatlng = new google.maps.LatLng(lat, long);
                if ($rootScope.map) {
                    $rootScope.map.setZoom(15);
                    $rootScope.map.setCenter(myLatlng);
                }
            }
            var getMarkers = function () {
                return $rootScope.markers;
            };
            var getUserMarker = function (dauser_id) {
                for (marker in $rootScope.markers) {
                    var container = $rootScope.markers[marker];
                    if (container.user_id == parseInt(dauser_id)) {
                        return container;
                    }
                }
                return null;
            };

            var findUserInPolyline = function (dauser_id) {
                for (polyline in $rootScope.polylines) {
                    if ($rootScope.polylines[polyline].user_id == dauser_id) {
                        return $rootScope.polylines[polyline];
                    }
                }
                return null;
            };
            var getUserPolyline = function (dauser_id) {
                for (polyline in $rootScope.polylines) {
                    if ($rootScope.polylines[polyline].user_id == dauser_id) {
                        return $rootScope.polylines[polyline];
                    }
                }
                return null;
            };
            var addToUserPolyline = function (info) {
                console.log("add user to polyline");
                removeFromUnknowns(info.user_id);
                if (info.islast == "1") {
                    deleteUserObjects(info.user_id);
                } else {
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
                            visible: $rootScope.rootMapMarkers,
                            isActive: false,
                        });
                        var path = poly.getPath();
                        path.push(coords);
                        if ($rootScope.rootMapMarkers) {
                            poly.setMap($rootScope.map);
                        } else {
                            poly.setMap(null);
                        }
                        console.log("new poly created and pos added: " + poly.user_id);
                        $rootScope.polylines.push(poly);

                    }
                    info.owner_type = "user";
                    checkActive(info);
                    // Add a new marker at the new plotted point on the polyline.
                    var marker = createMarker(info, $rootScope.rootMapMarkers);
                    $rootScope.markers.push(marker);
                }

            };
            var showAllPolylines = function (dauser_id) {
                for (polyline in $rootScope.polylines) {
                    $rootScope.polylines[polyline].setMap($rootScope.map);
                }
            };
            var showAllMerchants = function () {
                for (marker in $rootScope.merchants) {
                    $rootScope.merchants[marker].setMap($rootScope.map);
                }
            };
            var showAllReports = function () {
                for (marker in $rootScope.reports) {
                    $rootScope.reports[marker].setMap($rootScope.map);
                }
                console.log("Done showing reports");
            };

            var checkActive = function (info) {
                console.log("Check active: " + info.owner_type);
                console.log(JSON.stringify(info));
                console.log("Active type: " + $rootScope.mapActiveType + " id: " + $rootScope.mapId);
                if ($rootScope.mapActiveType) {

                } else {
                    $rootScope.mapActiveType = info.owner_type;
                }
                if ($rootScope.mapId) {

                } else {
                    $rootScope.mapId = info.user_id;
                }
                if ($rootScope.mapActiveType == info.owner_type) {
                    console.log("type the same mapid: " + $rootScope.mapId + " user: " + info.user_id);
                    if ($rootScope.mapActiveType == "me") {
                        setActive("me", -1, null);
                    }
                    if ($rootScope.mapId == info.user_id) {
                        console.log("Set center map, id the same");
                        setCenterMap(info.lat, info.long)
                    }
                }
            };
            var getNearbyMerchants = function (lat, lng) {
                var location = {lat: lat, long: lng, radius: $rootScope.radius};
                LocationService.merchants(location).then(function (data) {
                    var pins = data.merchants;
                    console.log(JSON.stringify(data));
                    console.log("merchants received from server: " + pins.length);
                    if (pins.length == 0) {
                        alert('No tenemos informacion de puntos de seguridad en ese punto');
                    } else {
                        cleanMerchants(data);
                    }
                },
                        function (data) {
                            console.log(data);
                        });
            };
            var loadUserTrip = function (user_id, trip) {
                $rootScope.rootMapMarkers = false;
                contactTripLocations(user_id, trip).then(function (res) {
                    console.log("locations in trip: " + trip + " count: " + res.length);
                    console.log(JSON.stringify(res));
                    if (res.length > 0) {
                        displayTrip(res);
                    } else {
                        alert('No encuentro ese viaje');
                    }
                },
                        function (data) {
                        });
            };
            var postMap = function () {
                $rootScope.map.addListener('dblclick', function (e) {
                    var location = e.latLng;
                    setCenterMap(location.lat(), location.lng());
                    getNearbyMerchants(location.lat(), location.lng());
                });
                if ($rootScope.memarkerpending) {
                    console.log("me marker was created before map so loading now");
                    $rootScope.memarkerpending = false;
                    $rootScope.memarker.setMap($rootScope.map);
                }
                if ($rootScope.merchantspending) {
                    console.log("merchants were created before map so loading now");
                    showAllMerchants();
                    showAllReports();
                    $rootScope.merchantspending = false;
                }
                setActive($rootScope.mapActiveType, $rootScope.mapId, null);
                alert('doble click para encontrar puntos de seguridad');
            };
            var setActive = function (type, id, latLng) {
                console.log("Setting as active: " + id + " type: " + type);
                $rootScope.mapActiveType = type;
                $rootScope.mapId = id;
                window.localStorage.setItem("MAP_ACTIVE_TYPE", type);
                window.localStorage.setItem("MAP_ACTIVE_ID", id);
                if (latLng) {
                    setCenterMap(latLng.lat(), latLng.lng());
                } else {
                    var centered = false;
                    if (type == "emergency" || type == "user" || type == "medical_emergency" || type == "me") {
                        $rootScope.tracking = true;
                        $rootScope.rootMapMarkers = true;
                        if (setVisibleUser($rootScope.mapId)) {
                            centered = true
                        }
                    } else if (type == "trip") {
                        var trip_id = $rootScope.contactTrip;
                        var contact_id = $rootScope.contactIdTrip;
                        loadUserTrip(contact_id, trip_id)
                        centered = true;
                    }
                    if (!centered) {
                        var markerslength = $rootScope.markers.length;
                        if (markerslength > 0) {
                            var marker = $rootScope.markers[markerslength - 1];
                            if (marker) {
                                var e = marker.getPosition();
                                var location = e.latLng;
                                if (location) {
                                    setActive("user", marker.user_id);
                                    return true;
                                }

                            }
                        }
                    }
                    if (!centered) {
                        if ($rootScope.trackingActive) {
                            if (type != "me") {
                                setActive("me", -1);
                            }
                            return true;
                        }

                    }
                }
            };

            var cleanMerchants = function (data) {
                var newmerchants = data.merchants;
                for (merchant in newmerchants) {
                    var found = false;
                    for (marker in $rootScope.merchants) {
                        if (newmerchants[merchant].id == $rootScope.merchants[marker].id) {
                            found = true;
                            break;
                        }
                    }
                    if (found == false) {
                        $rootScope.merchants.push(createMerchant(newmerchants[merchant]));
                    }
                }
                var newreports = data.reports;
                for (report in newreports) {
                    var found = false;
                    for (marker in $rootScope.reports) {
                        if (newreports[report].id == $rootScope.reports[marker].id) {
                            found = true;
                            break;
                        }
                    }
                    if (found == false) {
                        newreports[report].type = "Report";
                        $rootScope.reports.push(createMerchant(newreports[report]));
                    }
                }
                return null;
            }
            var deleteUserObjects = function (dauser_id) {
                for (polyline in $rootScope.polylines) {
                    if ($rootScope.polylines[polyline]) {
                        if ($rootScope.polylines[polyline].user_id = dauser_id) {
                            deleteUserMarkers($rootScope.polylines[polyline].user_id);
                            $rootScope.polylines[polyline].setMap(null);
                            $rootScope.polylines.splice(polyline, 1);
                        }
                    }
                }
            };
            var setVisibleUser = function (dauser_id) {
                if (dauser_id > 0) {
                    for (marker in $rootScope.markers) {
                        if ($rootScope.markers[marker].user_id = dauser_id) {
                            var latLng = $rootScope.markers[marker].getPosition();
                            setCenterMap(latLng.lat(), latLng.lng());
                            $rootScope.markers[marker].setMap($rootScope.map);
                            return true;
                        }
                    }
                } else {
                    if ($rootScope.memarker) {
                        var latLng = $rootScope.memarker.getPosition();
                        setCenterMap(latLng.lat(), latLng.lng());
                        return true;
                    }

                }
                return false;

            };
            var hideAllPolylines = function (dauser_id) {
                for (polyline in $rootScope.polylines) {
                    if ($rootScope.polylines[polyline]) {
                        $rootScope.polylines[polyline].setMap(null);
                    }
                }
            };
            var addToUnknowns = function (dauser_id) {
                var found = false;
                for (unknown in $rootScope.followingUnknown) {
                    if ($rootScope.followingUnknown[unknown] == dauser_id) {
                        found = true;
                    }
                }
                if (found == false) {
                    $rootScope.followingUnknown.push(dauser_id);
                }
            };
            var removeFromUnknowns = function (dauser_id) {
                for (unknown in $rootScope.followingUnknown) {
                    if ($rootScope.followingUnknown[unknown] == dauser_id) {
                        console.log("Removing user: " + dauser_id + " from unknowns");
                        $rootScope.followingUnknown.splice(unknown, 1);

                        return true;
                    }
                }
                return false;
            };
            var checkUnknownsMarker = function () {
                if ($rootScope.followingUnknown.length > 0) {
                    $rootScope.tracking = true;
                    $rootScope.mapSemaphore = true;
                    console.log("Unknown markers");
                    console.log(JSON.stringify($rootScope.followingUnknown));
                    setTimeout(function () {
                        console.log("Timeout Triggered unknowns");
                        updateLocations();
                    }, $rootScope.sharedTimeout);
                } else {
                    window.localStorage.setItem("USER_TRACKING", false);
                    console.log("No one is sharing location with user");
                    $rootScope.$broadcast('NoSharers');
                }
            };
            var createMarker = function (info, active) {
                if (active && $rootScope.map) {
                    damap = $rootScope.map;
                } else {
                    damap = null;
                }
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
                console.log("Icon path marker");
                console.log($rootScope.iconpath + "/" + pinclass);
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
                marker.content = '<div class="infoWindowContent">' + info.id + ' ' + info.name + '<br/><a onClick="window.open(\'http://waze.to/?ll=' + info.lat + ',' + info.long
                        + '&navigate=yes' + '\',\'_system\',\'location=yes\');return false;">Waze</a><br/><a href="tel:' + info.phone + '">' + info.phone + '</a></div>';
                google.maps.event.addListener(marker, 'click', function () {
                    infoWindow.setContent('<h2>' + marker.report_time + '</h2>' + marker.content);
                    infoWindow.open($rootScope.map, marker);
                    var latlong = marker.getPosition();
                    setActive("user", marker.user_id, latlong);
                });
                if (info.status == "emergency") {
                    var latLng = marker.getPosition();
                    setActive("emergency", info.user_id, latLng);
                }

                return marker;
            }
            var createMerchant = function (info) {
                if ($rootScope.map) {
                    damap = $rootScope.map;
                } else {
                    damap = null;
                    $rootScope.merchantspending = true;
                    console.log("Merchant pending from adding to map");
                }
                var pinclass = "";
                if (info.type == "police") {
                    pinclass = "police-shield.svg";
                } else if (info.type == "medical") {
                    pinclass = "medic-suitcase.svg";
                } else if (info.type == "Report") {
                    pinclass = "pin.svg";
                }
                console.log("icon: " + $rootScope.iconpath + "/" + pinclass + " for type: " + info.type);
                var marker = new google.maps.Marker({
                    id: info.id,
                    position: new google.maps.LatLng(info.lat, info.long),
                    map: damap,
                    animation: google.maps.Animation.DROP,
                    icon: {
                        fillColor: '#ffffff',
                        url: $rootScope.iconpath + "/" + pinclass,
                        fillOpacity: 1,
                        strokeColor: '#ffffff',
                        strokeWeight: 0,
                        scaledSize: new google.maps.Size(25, 25)
                    }
                });
                marker.content = '<div class="infoWindowContent">' + info.type + '<br/><a class="button icon-left ion-ios7-telephone button-calm button-outline" href="tel:+ ' + info.telephone + '">' + info.telephone + '</a><br/>' + info.address + '<br/><a onClick="window.open(\'http://waze.to/?ll=' + info.lat + ',' + info.long + '&navigate=yes' + '\',\'_system\',\'location=yes\');return false;">Waze</a></div>';
                var pinclass = "";
                if (info.type == "police") {
                    pinclass = "map-icon-police";
                } else if (info.type == "medical") {
                    pinclass = "map-icon-health";
                } else if (info.type == "Report") {
                    pinclass = "map-icon-report";
                    marker.content = '<div class="infoWindowContent">' + info.type + '<br/>' + info.address + '<br/><a onClick="window.open(\'http://waze.to/?ll=' + info.lat + ',' + info.long + '&navigate=yes' + '\',\'_system\',\'location=yes\');return false;">Waze</a></div>';
                }
                console.log("creating merchant");
                var infoWindow = new google.maps.InfoWindow();

                google.maps.event.addListener(marker, 'click', function () {
                    infoWindow.setContent('<h2>' + info.name + '</h2>' + marker.content);
                    infoWindow.open($rootScope.map, marker);
                });
                return marker;
            }
            var checkMarkers = function (info) {
                var def = $q.defer();
                var i = 0;
                for (target in info) {
                    if (info[target].id) {
                        i++;
                        if ($rootScope.lastLocation < info[target].id) {
                            $rootScope.lastLocation = info[target].id;
                        }
                        checkLocationExists(info[target]).then(function (res) {
                            i--;
                            if (i == 0) {
                                def.resolve("done");
                            }
                        }, function (err) {
                            console.log("Error in checking locations");
                            console.error(JSON.stringify(err));
                            i--;
                            if (i == 0) {
                                def.resolve("done");
                            }
                        });
                    }
                }
                return def.promise;
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
            var hideAllActiveMarkers = function () {
                hideMarkers($rootScope.markers);
                hideAllPolylines();
            }
            var hideMarkers = function (damarkers) {
                for (marker in damarkers) {
                    if (damarkers[marker]) {
                        damarkers[marker].setMap(null);
                    }
                }
            }
            var clearTemps = function () {
                if ($rootScope.tempMarkers) {
                    for (marker in $rootScope.tempMarkers) {
                        if ($rootScope.tempMarkers[marker]) {
                            $rootScope.tempMarkers[marker].setMap(null);
                        }
                    }
                    $rootScope.tempMarkers = [];
                }
                if ($rootScope.tempPolyline) {
                    $rootScope.tempPolyline.setMap(null);
                    $rootScope.tempPolyline = null;
                }
            }
            var showAllActiveMarkers = function () {
                showMarkers($rootScope.markers);
                showAllPolylines();
            }
            var showMarkers = function (damarkers) {
                for (marker in damarkers) {
                    if (damarkers[marker]) {
                        damarkers[marker].setMap($rootScope.map);
                    }
                }
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
                        console.log("trip: " + damarkers[marker].trip);
                        console.log("type: " + damarkers[marker].type);
                        console.log("user_id: " + damarkers[marker].user_id);
                        console.log("report_time: " + damarkers[marker].report_time);
                    } else {
                        console.log("Marker is null: " + marker);
                        console.log(damarkers[marker]);
                    }

                }
            }
            var getMarker = function (daid) {
                console.log($rootScope.markers.length);
                for (marker in $rootScope.markers) {
                    console.log("Comparing markers");
                    if ($rootScope.markers[marker]) {
                        console.log($rootScope.markers[marker].id);
                        console.log(daid);
                        if ($rootScope.markers[marker].id == daid) {
                            console.log("Marker found, returning now");
                            return $rootScope.markers[marker];
                        }
                    } else {
                        $rootScope.markers.splice(marker, 1);
                    }

                }
                return null;
            }

            var checkLocationExists = function (location) {
                var def = $q.defer();
                console.log("Checking database for location: " + location.id);
                var found = false;
                for (item in $rootScope.markers) {
                    if ($rootScope.markers[item].id == location.id) {
                        found = true;
                    }
                }
                if (found) {
                    location.status = true;
                    console.log("Location exists: ");
                    var marker = getUserMarker(location.user_id);
                    if (marker) {
                        console.log("User Marker Found");
                        if (marker.id >= location.id) {
                            console.log("User Marker older");
                        } else {
                            console.log("User Marker newer");
                            addToUserPolyline(location);
                        }
                    } else {
                        console.log("Marker does not exist");
                        addToUserPolyline(location);
                    }
                    def.resolve(location);
                } else {
                    location.status = true;

                    console.log("Location does not exists");
                    addToUserPolyline(location);
                }
                return def.promise;
            };
            var contactTrips = function (user_id, page, per_page) {
                var offset = (page - 1) * per_page;
                console.log("Trying sql contactTrips not implemented yet");

            };
            var contactTripsCount = function (user_id) {
                console.log("Trying sql user trips not implemented yet");
                var query = "SELECT COUNT(id) as total FROM locations where user_id = ?  group by trip";
            };
            var displayTrip = function (res) {
                var flightPlanCoordinates = [
                ];
                var container;
                if ($rootScope.tempMarkers) {
                    hideMarkers($rootScope.tempMarkers);

                }
                $rootScope.tempMarkers = [];
                console.log("Showing markers: " + res.length);
                console.log(JSON.stringify(res));
                var coords;
                var trip;
                var user;
                for (i = 0; i < res.length; i++) {
                    if (res[i]) {
                        coords = new google.maps.LatLng(res[i].lat, res[i].long);
                        console.log("Showing marker: ");
                        trip = res[i].trip;
                        user = res[i].user_id;
                        marker = createMarker(res[i], true);
                        $rootScope.tempMarkers.push(marker);
                        flightPlanCoordinates.push(coords);
                        container = res[i];
                    }
                }
                $rootScope.contactIdTrip = user;
                $rootScope.contactTrip = trip;
                setCenterMap(coords.lat(), coords.lng());
                var flightPath = new google.maps.Polyline({
                    path: flightPlanCoordinates,
                    geodesic: true,
                    strokeColor: '#FF0000',
                    strokeOpacity: 1.0,
                    strokeWeight: 2,
                    user_id: container.user_id,
                    name: container.name,
                    status: "closed",
                });
                if ($rootScope.tempPolyline) {
                    $rootScope.tempPolyline.setMap(null);
                    $rootScope.tempPolyline = null;
                }
                flightPath.setMap($rootScope.map);
                $rootScope.tempPolyline = flightPath;
            };

            var contactTripLocations = function (user_id, trip_id) {
                console.log("contact trip locations not implemented at present: " + user_id + " trip: " + trip_id);

            };
            var updateLocations = function () {
                console.log("Updating locations");
                if ($rootScope.tracking) {
                    console.log("rootscope tracking true");
                    if ($rootScope.mapSemaphore) {
                        console.log("rootscope mapsemaphore true");
                        $rootScope.mapSemaphore = false;
                        var lastLocation = $rootScope.lastLocation;
                        var dapage = 1;
                        var where = "?page=" + dapage + "&id_after=" + lastLocation;
                        getServerLocations(where, lastLocation);
                    } else {
                        checkUnknownsMarker();
                    }
                } else {
                    checkUnknownsMarker();
                }

            }
            var saveTrip = function (trip) {
                console.log("saveTrip: ");
                console.log(JSON.stringify(trip));
                LocationService.getLocationsTrip(trip)
                        .then(function (data) {
                            var pins = data.data;
                            console.log("Locations received from server: " + pins.length);
                            console.log(JSON.stringify(pins));
                            checkMarkers(pins);
                            if (data.page < data.last_page) {
                                if (data.page == 4) {
                                    $rootScope.$broadcast('receivedTripDetail', {trigger_id: trip.user_id});
                                    return;
                                } else {
                                    trip.page = data.page + 1;
                                    saveTrip(trip);
                                }

                            } else {
                                $rootScope.$broadcast('receivedTripDetail', {trigger_id: trip.user_id});
                            }

                        },
                                function (data) {
                                    console.log(data);
                                });
            }
            var getServerLocations = function (where, idafter) {
                LocationService.shared(where).then(function (data) {
                    if (data.total == 0) {
                        checkUnknownsMarker();
                    } else {
                        var pins = data.data;
                        pins.reverse();
                        checkMarkers(pins).then(function (data) {
                            $rootScope.mapSharedLoaded = true;
                        },
                                function (data) {
                                    console.log(data);
                                });
                        if (data.page < data.last_page) {
                            var dapage = data.page + 1;
                            var where = "?page=" + dapage + "&id_after=" + idafter;
                            getServerLocations(where, idafter);
                        } else {
                            $rootScope.mapSemaphore = true;
                            setTimeout(function () {
                                console.log("Timeout Triggered");
                                updateLocations();
                            }, $rootScope.sharedTimeout);
                        }
                    }
                },
                        function (data) {
                            console.log(data);
                        });
            }
            return {
                createMarker: createMarker,
                clearTemps: clearTemps,
                createMap: createMap,
                getNearbyMerchants: getNearbyMerchants,
                contactTripsCount: contactTripsCount,
                deleteUserObjects: deleteUserObjects,
                deleteUserMarkers: deleteUserMarkers,
                getUserPolyline: getUserPolyline,
                saveTrip: saveTrip,
                checkMarkers: checkMarkers,
                contactTripLocations: contactTripLocations,
                contactTrips: contactTrips,
                consoleLogMarkers: consoleLogMarkers,
                getMarker: getMarker,
                hideAllActiveMarkers: hideAllActiveMarkers,
                hideMarkers: hideMarkers,
                showAllActiveMarkers: showAllActiveMarkers,
                showMarkers: showMarkers,
                updateLocations: updateLocations,
                getMarkers: getMarkers,
                getUserMarker: getUserMarker,
                displayTrip: displayTrip,
                addToUnknowns: addToUnknowns,
                removeFromUnknowns: removeFromUnknowns,
                setActive: setActive,
                checkActive: checkActive,
                postMap: postMap
            };
        }])