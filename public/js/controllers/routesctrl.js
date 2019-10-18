angular.module('besafe')

        .controller('RoutesCtrl', function ($scope, $rootScope, Routes, Food, MapService) {
            $scope.data = {};
            $scope.routes;
            $scope.page = 0;
            $scope.loadMore = true;
            $scope.scenario = 'simple';
            $scope.status = 'pending';
            $scope.provider = 'Rapigo';
            $scope.regionVisible = false;
            $scope.editAddress = false;
            $scope.mapActive = false;
            angular.element(document).ready(function () {
                $scope.getRoutes();
            });
            $scope.save = function (isvalid) {
                $scope.submitted = true;
                if (isvalid) {
                    var existing = false;
                    if ($scope.data.address_id) {
                        existing = true;
                    }
                    Routes.saveAddress($.param($scope.data)).then(function (data) {
                        if (existing) {
                            $scope.updateAddress(data.address);
                        } else {
                            $scope.routes.push(data.address);
                        }

                        $scope.data = {};
                        $scope.submitted = false;
                        $scope.editAddress = false;
                    },
                            function (data) {

                            });
                }
            }
            $scope.changeScenario = function () {
                //$scope.provider= "Basilikum";
                console.log("provider", $scope.provider);
                $scope.page = 0;
                $scope.hideAll();
                if ($scope.provider == "Basilikum") {
                    $scope.scenario = "preorganize";
                }
                $scope.routes = [];
                $scope.getRoutes();
            }
            $scope.activateMap = function () {
                $scope.mapActive = true;
                console.log("Creating map data")
                MapService.createMap(4.685419, -74.064161);
                $scope.createMapData();
            }
            $scope.createMapData = function () {
                console.log("Creating map data")
                for (route in $scope.routes) {
                    let stops = $scope.routes[route].stops;
                    for (item in stops) {
                        console.log("Creating map data stop");
                        stops[item].icon = 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=' + stops[item].amount + '|FE6256|000000';
                        stops[item].marker = MapService.createStop(stops[item]);
                    }
                    $scope.routes[route].stops = stops;
                    console.log("Creating map data route")
                    let color = MapService.getColor();
                    $scope.routes[route].polyline = MapService.createRoute($scope.routes[route], color);
                }
            }
            $scope.buildRouteData = function (route) {
                let stops = route.stops;
                let stopsLat = [];
                for (item in stops) {
                    stops[item].details = JSON.parse(stops[item].details);
                    let stopCord = {"lat": stops[item].address.lat, "lng": stops[item].address.long}
                    stopsLat.push(stopCord);
                    stops[item].name = stops[item].address.address;
                    stops[item].lat = stops[item].address.lat;
                    stops[item].long = stops[item].address.long;
                    stops[item].moving_delivery = null;
                    if ($scope.mapActive) {
                        stops[item].marker = MapService.createStop(stops[item]);
                    }

                    /*let deliveries = stops[item].deliveries;
                     for (item2 in deliveries) {
                     deliveries[item2].details = JSON.parse(deliveries[item2].details);
                     }
                     stops[item].deliveries = deliveries;*/
                }
                route.stops = stops;
                route.stopsLat = stopsLat;
                if ($scope.mapActive) {
                    let color = $scope.getRouteColor();
                    route.polyline = MapService.createRoute(route, color);
                }
                setTimeout(function () {
                    var el = document.getElementById('route-' + route.id + '-table');
                    var sortable = new Sortable(el, {
                        group: "routes",
                        dataIdAttr: route.id,
                        onAdd: function (/**Event*/evt) {
                            console.log("On add", evt);
                            let attributes = evt.item.attributes;
                            let routeString = evt.target.id;
                            routeString = routeString.replace("route-", "");
                            routeString = routeString.replace("-table", "");
                            let route = parseInt(routeString);
                            let stop = null;
                            for (item in attributes) {
                                if (attributes[item].name == "data-stop") {
                                    stop = attributes[item].value;
                                }
                            }
                            $scope.updateRouteLocation(route, stop, evt.newIndex, 1000);
                        },
                        onUpdate: function (/**Event*/evt) {
                            // same properties as onEnd
                            let attributes = evt.item.attributes;
                            let route = null;
                            let stop = null;
                            for (item in attributes) {
                                if (attributes[item].name == "data-route") {
                                    route = attributes[item].value;
                                }
                                if (attributes[item].name == "data-stop") {
                                    stop = attributes[item].value;
                                }
                            }
                            if (route && stop) {
                                $scope.updateRouteLocation(route, stop, evt.newIndex, evt.oldIndex);
                            }

                        },
                    });
                    console.log("Setting sortable");
                }, 1000);


                return route;
            }
            $scope.updateRouteLocation = function (route, stop, index, old) {
                console.log("Route", route);
                console.log("stop", stop);
                console.log("index", index);
                let movingStop = null
                let displacedStops = [];
                for (item in $scope.routes) {
                    if ($scope.routes[item].id == route) {
                        let container = $scope.routes[item];
                        let counter = 1;
                        for (stopI in container.stops) {
                            if (container.stops[stopI].id == stop) {
                                movingStop = container.stops[stopI];
                            } else {
                                if (old > index) {
                                    if (counter >= index) {
                                        displacedStops.push(container.stops[stopI]);
                                    }
                                } else {
                                    if (counter > index) {
                                        displacedStops.push(container.stops[stopI]);
                                    }
                                }
                            }
                            counter++;
                        }
                    }
                }
                
                console.log("displacedStops", displacedStops);
                if(!movingStop){
                    movingStop = {};
                    movingStop.route_id = route;
                    movingStop.id = stop;
                }
                console.log("Moving stop", movingStop);
                $scope.updateRouteStop(movingStop);
                
                setTimeout(function () {
                    let counter = 1;
                    if (displacedStops.length > 0) {
                        for (item in displacedStops) {
                            $scope.updateSpeed(displacedStops[item], counter);
                            counter++;
                            console.log("displacing stop", displacedStops[item]);
                            if (counter == displacedStops.length) {
                                setTimeout(function () {
                                    window.location.reload();
                                }, 300 * (counter + 1));
                            }
                        }
                    } else {
                        setTimeout(function () {
                            window.location.reload();
                        }, 300 * (counter + 1));
                    }
                }, 1000);
            }
            $scope.updateSpeed = function (stop, counter) {
                setTimeout(function () {
                    $scope.updateRouteStop(stop);
                }, 300 * (counter + 1));
            }
            $scope.getRoutes = function () {
                console.log("Status", $scope.status);
                console.log("provider", $scope.provider);
                $scope.page++;
                let url = "includes=stops.address&order_by=id,asc&page=" + $scope.page + "&type=" + $scope.scenario + "&status=" + $scope.status + "&provider=" + $scope.provider;
                if ($scope.status != "pending") {
                    url = "includes=stops.address&order_by=id,asc&page=" + $scope.page + "&status=" + $scope.status;
                }

                Routes.getRoutes(url).then(function (data) {
                    let routesCont = data.data;
                    if (data.page == data.last_page) {
                        $scope.loadMore = false;
                    }
                    for (item in routesCont) {
                        routesCont[item] = $scope.buildRouteData(routesCont[item]);
                    }
                    $scope.routes = routesCont;
                },
                        function (data) {

                        });
            }
            $scope.clean = function () {
                $scope.data = {};
                $scope.regionVisible = false;
                $scope.cityVisible = false;
            }
            $scope.sendReminder = function () {
                Food.sendReminder().then(function (data) {
                },
                        function (data) {

                        });
            }
            $scope.showAll = function () {
                console.log("Creating map data")
                for (route in $scope.routes) {
                    let stops = $scope.routes[route].stops;
                    for (item in stops) {
                        console.log("Creating map data stop")
                        stops[item].marker.setMap($rootScope.map);
                    }
                    $scope.routes[route].stops = stops;
                    $scope.routes[route].polyline.setMap($rootScope.map);
                }
            }
            $scope.hideAll = function () {
                console.log("Creating map data")
                for (route in $scope.routes) {
                    let stops = $scope.routes[route].stops;
                    for (item in stops) {
                        console.log("Creating map data stop")
                        if (stops[item].marker) {
                            stops[item].marker.setMap(null);
                        }
                    }
                    $scope.routes[route].stops = stops;
                    if ($scope.routes[route].polyline) {
                        $scope.routes[route].polyline.setMap(null);
                    }
                }
            }
            $scope.showRoute = function (route) {
                $scope.hideAll();
                console.log("Creating map data")
                let stops = route.stops;
                for (item in stops) {
                    console.log("Creating map data stop")
                    stops[item].marker.setMap($rootScope.map);
                }
                route.polyline.setMap($rootScope.map);
            }

            $scope.clean = function () {
                $scope.data = {};
                $scope.regionVisible = false;
                $scope.cityVisible = false;
            }
            $scope.updateRouteStop = function (stop) {
                Routes.updateRouteStop(stop.id, stop.route_id).then(function (data) {
                    
                },
                        function (data) {

                        });
            }
            $scope.updateRouteDelivery = function (stop) {
                Routes.updateRouteStop(stop.route_id, stop.moving_delivery, stop.id).then(function (data) {

                },
                        function (data) {

                        });
            }
            $scope.deleteRoute = function (route_id) {
                Routes.deleteRoute(route_id).then(function (data) {

                },
                        function (data) {

                        });
            }
            $scope.deleteStop = function (stop_id) {
                Routes.deleteStop(stop_id).then(function (data) {

                },
                        function (data) {

                        });
            }
            $scope.sendStopToNewRoute = function (stop) {
                Routes.sendStopToNewRoute(stop).then(function (data) {
                    window.location.reload();
                },
                        function (data) {

                        });
            }
            $scope.addReturnStop = function (route) {
                Routes.addReturnStop(route).then(function (data) {
                    window.location.reload();
                },
                        function (data) {

                        });
            }
            $scope.buildRoute = function (route) {
                Food.buildScenarioRouteId(route.id).then(function (data) {
                    route.status = "scheduled";
                },
                        function (data) {

                        });
            }
            $scope.getPurchaseOrder = function () {
                Food.getPurchaseOrder().then(function (data) {
                },
                        function (data) {

                        });
            }
            $scope.regenerateScenarios = function () {
                Food.regenerateScenarios().then(function (data) {
                },
                        function (data) {

                        });
            }
            $scope.regenerateDeliveries = function () {
                Food.regenerateDeliveries().then(function (data) {
                },
                        function (data) {

                        });
            }
            $scope.getTotalShippingCosts = function () {
                Food.getSummaryShipping($scope.status).then(function (data) {
                },
                        function (data) {

                        });
            }
            $scope.getScenarioOrganization = function () {
                let data = $scope.getInputs();
                Food.getScenarioOrganizationStructure(data).then(function (data) {
                },
                        function (data) {

                        });
            }
            $scope.buildScenarioLogistics = function () {
                let data = $scope.getInputs();
                Food.buildScenarioLogistics(data).then(function (data) {
                },
                        function (data) {

                        });
            }
            $scope.getInputs = function () {
                let data;
                if ($scope.status == "enqueue") {
                    data = {
                        "status": "enqueue"
                    };
                } else {
                    data = {
                        "status": $scope.status,
                        "provider": $scope.provider,
                        "type": $scope.scenario
                    };
                }
                return data;
            }
            $scope.getScenarioEmails = function () {
                let data = $scope.getInputs();
                Food.getScenarioStructure(data).then(function (data) {
                },
                        function (data) {

                        });
            }

        })