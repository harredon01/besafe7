angular.module('besafe')

        .controller('RoutesCtrl', function ($scope, $rootScope, Routes, Food, MapService) {
            $scope.data = {};
            $scope.routes;
            $scope.page = 0;
            $scope.loadMore = true;
            $scope.scenario = 'simple';
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
                $scope.page = 0;
                $scope.routes = [];
                $scope.getRoutes();
            }
            $scope.getRouteColor = function () {

                let routeColors = ["#FF0000", "#800000", "#FFFF00", "#808000", "#00FF00", "#008000", "#00FFFF", "#008080", "#0000FF", "#000080", "#FF00FF", "#800080"];
                let random = Math.round(Math.random() * (routeColors.length - 1));
                return routeColors[random];
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
                        console.log("Creating map data stop")
                        stops[item].marker = MapService.createStop(stops[item]);
                    }
                    $scope.routes[route].stops = stops;
                    console.log("Creating map data route")
                    let color = $scope.getRouteColor();
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
                return route;
            }
            $scope.getRoutes = function () {
                $scope.page++;
                let url = "includes=stops.address&order_by=id,asc&page=" + $scope.page + "&type=" + $scope.scenario;
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
                        stops[item].marker.setMap(null);
                    }
                    $scope.routes[route].stops = stops;
                    $scope.routes[route].polyline.setMap(null);
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
            $scope.updateRouteStop = function (stop, route_id) {
                for (item in $scope.routes) {
                    if ($scope.routes[item].id == stop.route_id) {
                        $scope.routes.splice(item, 1);
                        $scope.routes.push(address);
                    }
                }
                Routes.updateRouteStop(stop, route_id).then(function (data) {

                },
                        function (data) {

                        });
            }
            $scope.buildRoute = function (route) {
                Routes.buildRoute(route.id).then(function (data) {
                    route.status = "scheduled";
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
                Food.getSummaryShipping().then(function (data) {
                },
                        function (data) {

                        });
            }
            $scope.getScenarioEmails = function () {
                Food.getScenarioStructure($scope.scenario).then(function (data) {
                },
                        function (data) {

                        });
            }

        })