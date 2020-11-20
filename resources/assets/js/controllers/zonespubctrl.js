angular.module('besafe')

        .controller('ZonesPubCtrl',['$scope', '$rootScope', 'Food', 'MapService', function ($scope, $rootScope, Food, MapService) {
            $scope.data = {};
            $scope.items = [];
            $scope.merchants = [{"name": "Plan de almuerzos", "value": 1299}, {"name": "Para preparar en casa", "value": 1300}, {"name": "Catering", "value": 1301}];
            $scope.providers = [{"name": "Basilikum", "value": "Basilikum"}, {"name": "Rapigo", "value": "Rapigo"}];
            $scope.activeMerchant = 1299 + "";
            $scope.activeProvider = "Basilikum";
            $scope.loadMore = true,
                    $scope.page = 0;
            angular.element(document).ready(function () {
                $scope.getItems();
                MapService.createMap(4.685419, -74.064161);
            });

            $scope.buildItemData = function (item) {
                item.isActive = false;
                //item.attributes = JSON.parse(item.attributes);
                return item;
            }
            $scope.activateMap = function () {
                $scope.mapActive = true;
                console.log("Creating map data")

                $scope.createMapData();
            }
            $scope.createMapData = function () {
                console.log("Creating map data")
                for (item in $scope.items) {
                    console.log("Creating map data item")
                    let color = MapService.getColor();
                    $scope.items[item].polygon = MapService.createPolygon($scope.items[item], color);
                }
            }
            $scope.clearMap = function () {
                for (item in $scope.items) {
                    console.log("Creating map data item")
                    $scope.items[item].polygon.setMap(null);
                    $scope.items[item].isActive = false;
                }
            }
            $scope.changeScenario = function () {
                $scope.clearMap();
                $scope.items = [];
                $scope.page = 0;
                $scope.getItems();
            }
            $scope.getColor = function () {
                let routeColors = ["#FF0000", "#800000", "#FFFF00", "#808000", "#00FF00", "#008000", "#00FFFF", "#008080", "#0000FF", "#000080", "#FF00FF", "#800080"];
                let random = Math.round(Math.random() * (routeColors.length - 1));
                return routeColors[random];
            }
            $scope.selectItem = function (zone) {
                $scope.clearMap();
                zone.isActive = true;
                zone.polygon.setMap($rootScope.map);
            }
            $scope.createItem = function () {
                $scope.clearMap();
                let coverage = [{"lat": 4.661880, "lng": -74.056724},
                    {"lat": 4.656747, "lng": -74.060458}, {"lat": 4.656277, "lng": -74.052304}];
                let item = {
                    "city_id": 524,
                    "region_id": 11,
                    "address_id": 14,
                    "country_id": 1,
                    "merchant_id": $scope.activeMerchant,
                    "provider": $scope.activeProvider,
                    "coverage": JSON.stringify(coverage),
                    "lat": 4.649824,
                    "long": -74.058881
                };
                Food.createZoneItem(item).then(function (data) {
                    let color = MapService.getColor();
                    let item = data.item;
                    item.polygon = MapService.createPolygon(item, color);
                    $scope.items.push(item);
                },
                        function (data) {

                        });
            }

            $scope.getItems = function () {
                $scope.page++;
                let url = "order_by=id,desc&page=" + $scope.page + "&merchant_id=" + $scope.activeMerchant + "&provider=" + $scope.activeProvider;
                Food.getZones(url).then(function (data) {
                    if (data.page == data.last_page) {
                        $scope.loadMore = false;
                    }
                    console.log("Items", data.data);
                    let itemsCont = data.data;
                    for (item in itemsCont) {
                        itemsCont[item] = $scope.buildItemData(itemsCont[item]);
                    }
                    $scope.items = $scope.items.concat(itemsCont);
                    $scope.activateMap();
                },
                        function (data) {

                        });
            }
            $scope.updateItem = function (data) {
                var item = data;
                var results = [];
                let last = null;
                item.polygon.getPaths().forEach(function (path, index) {
                    console.log("path", index);
                    let vertices = path;
                    for (var i = 0; i < vertices.getLength(); i++) {
                        var xy = vertices.getAt(i);
                        var point = {"lat": xy.lat(), "lng": xy.lng()};
                        results.push(point);
                        if (i == 0) {
                            last = point;
                        }
                    }
                });
                results.push(last);
                var polygon = item.polygon;
                delete item.polygon;
                delete item.isActive;
                item.coverage = JSON.stringify(results);
                console.log("Zone", item);
                console.log("Points", results);
                Food.updateZoneItem(item).then(function (data) {
                    item.polygon = polygon;
                    console.log("updateZoneItem", data);
                },
                        function (data) {

                        });
            }
            $scope.deleteItem = function (item) {
                console.log("Zone", item);
                Food.deleteZoneItem(item).then(function (data) {
                    for (item in $scope.items) {
                        console.log("Creating map data item")
                        $scope.items[item].polygon.setMap(null);
                        $scope.items[item].isActive = false;
                        $scope.items.splice(item, 1);
                    }

                },
                        function (data) {

                        });
            }

        }])