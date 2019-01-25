angular.module('besafe')

        .controller('RoutesCtrl', function ($scope, Routes) {
            $scope.data = {};
            $scope.routes;
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
            $scope.getRoutes = function () {
                Routes.getRoutes().then(function (data) {
                    $scope.routes = data.addresses;

                },
                        function (data) {

                        });
            }
            $scope.clean = function () {
                $scope.data = {};
                $scope.regionVisible = false;
                $scope.cityVisible = false;
            }
            $scope.updateRouteStop = function (stop,route_id) {
                for (item in $scope.routes) {
                    if ($scope.routes[item].id == stop.route_id) {
                        $scope.routes.splice(item, 1);
                        $scope.routes.push(address);
                    }
                }
                Routes.updateRouteStop(stop,route_id).then(function (data) {

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

        })