angular.module('besafe')

        .controller('ZonesCtrl', ['$scope', '$rootScope', 'Zones', 'MapService', 'Merchants', function ($scope, $rootScope, Zones, MapService, Merchants) {
                $scope.data = {};
                $scope.items = [];
                $scope.merchants = [];
                $scope.providers = [{"name": "Mi Paquete", "value": "MiPaquete"}, {"name": "Rapigo", "value": "Rapigo"}, {"name": "Propio", "value": "Propio"}], {"name": "Basilikum", "value": "Basilikum"};
                $scope.activeMerchant;
                $scope.activeMerchantObject = {};
                $scope.searchTerms = "";
                $scope.changeMerchant = false;
                $scope.createNewMerchant = false;
                $scope.loadMore = true,
                        $scope.page = 0;
                angular.element(document).ready(function () {
                    console.log("Getting merchants and zones");
                    $scope.getMerchants();
                    MapService.createMap(4.685419, -74.064161);
                });

                $scope.getMerchants = function () {
                    Merchants.getMerchantsUser().then(function (data) {
                        if (data.status == "success") {
                            $scope.merchants = data.data;
                            $scope.activeMerchant = $scope.merchants[0].id;
                            $scope.getItems();
                        }

                    },
                            function (data) {

                            });
                }
                $rootScope.$on("zone-updated", function (evt, data) {
                    for (item in $scope.items) {
                        if ($scope.items[item].id == data.id) {
                            $scope.items[item].isActive = true;
                            $scope.$digest();
                        }
                    }
                });

                $scope.buildItemData = function (item) {
                    item.isActive = false;
                    //item.attributes = JSON.parse(item.attributes);
                    return item;
                }
                $scope.changeActiveMerchant = function () {
                    $scope.changeMerchant = true;
                }
                $scope.createItemStart = function () {
                    $scope.createNewMerchant = true;
                }
                $scope.cancelChangeMerchant = function () {
                    $scope.changeMerchant = false;
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
                    let centerMap = $rootScope.map.getCenter();
                    let center = {"lat": centerMap.lat(), "lng": centerMap.lng()};
                    console.log("Center Map", center.lat);
                    let coverage = [{"lat": center.lat + 0.006, "lng": center.lng},
                        {"lat": center.lat - 0.006, "lng": center.lng - 0.006}, {"lat": center.lat - 0.006, "lng": center.lng + 0.006}, {"lat": center.lat + 0.006, "lng": center.lng}];
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
                    Zones.createZoneItem(item).then(function (data) {
                        let color = MapService.getColor();
                        let item = data.item;
                        item.isActive = false;
                        item.polygon = MapService.createPolygon(item, color);
                        $scope.items.push(item);
                    },
                            function (data) {

                            });
                }

                $scope.getItems = function () {
                    $scope.page++;
                    let url = "order_by=id,desc&page=" + $scope.page + "&merchant_id=" + $scope.activeMerchant;
                    Zones.getZones(url).then(function (data) {
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
                $scope.searchMerchants = function () {
                    $scope.page++;
                    Merchants.searchMerchants($scope.searchTerms).then(function (data) {
                        console.log("Items", data.data);
                        $scope.merchants = data.data;
                    },
                            function (data) {

                            });
                }
                $scope.selectMerchant = function (item) {
                    $scope.activeMerchantObject = item;
                    $scope.clearMap();
                    $scope.page = 0;
                    $scope.items = [];
                    $scope.activeMerchant = item.id + "";
                    $scope.changeMerchant = false;
                    $scope.getItems();
                }
                $scope.selectMerchantObject = function () {
                    console.log("Selecting merchant: ", $scope.activeMerchant);
                    $scope.clearMap();
                    $scope.page = 0;
                    $scope.items = [];
                    $scope.getItems();
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
                    Zones.updateZoneItem(item).then(function (data) {
                        item.polygon = polygon;
                        item.isActive = false;
                        console.log("updateZoneItem", data);
                    },
                            function (data) {

                            });
                }
                $scope.deleteItem = function (item) {
                    console.log("Zone", item);
                    Zones.deleteZoneItem(item).then(function (data) {
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