angular.module('besafe')

        .controller('RoutesCtrl', function ($scope, Routes, Food) {
            $scope.data = {};
            $scope.routes;
            $scope.page = 0;
            $scope.loadMore = true;
            $scope.scenario = 'simple';
            $scope.regionVisible = false;
            $scope.editAddress = false;
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
            $scope.buildRouteData = function (route) {
                let stops = route.stops;
                for (item in stops) {
                    stops[item].details = JSON.parse(stops[item].details);
                    let deliveries = stops[item].deliveries;
                    for (item2 in deliveries) {
                        deliveries[item2].details = JSON.parse(deliveries[item2].details);
                    }
                    stops[item].deliveries = deliveries;
                }
                route.stops = stops;
                return route;
            }
            $scope.getRoutes = function () {
                $scope.page++;
                let url = "includes=stops.deliveries&order_by=id,asc&page=" + $scope.page + "&type=" + $scope.scenario;
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

        })