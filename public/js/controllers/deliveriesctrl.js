angular.module('besafe')

        .controller('DeliveriesCtrl', function ($scope, $rootScope, Food, MapService) {
            $scope.data = {};
            $scope.listArticles = {};
            $scope.deliveries;
            $scope.page = 0;
            $scope.loadMore = true;
            $scope.status = 'enqueue';
            $scope.activeDate = '';
            $scope.regionVisible = false;
            $scope.editAddress = false;
            $scope.mapActive = false;
            angular.element(document).ready(function () {
                $scope.getArticles(new Date());
                setTimeout(function(){ $scope.getDeliveries(); }, 500);
                
            });
            $scope.activateMap = function () {
                $scope.mapActive = true;
                console.log("Creating map data")
                MapService.createMap(4.685419, -74.064161);
                $scope.createMapData();
            }
            $scope.changeScenario = function () {
                $scope.getDeliveries();
            }
            $scope.createMapData = function () {
                console.log("Creating map data")
                for (item in $scope.deliveries) {
                    $scope.deliveries[item].marker = $scope.buildDeliveryData($scope.deliveries[item]);
                }
            }
            $scope.buildDeliveryData = function (delivery) {
                delivery.details = JSON.parse(delivery.details);
                delivery.name = delivery.address.address;
                delivery.lat = delivery.address.lat;
                delivery.delivery = new Date(delivery.delivery);
                delivery.long = delivery.address.long;
                if ($scope.mapActive) {
                    delivery.marker = MapService.createStop(delivery);
                }
                return delivery;
            }
            $scope.getDeliveries = function () {
                console.log("Status", $scope.status);
                $scope.page++;
                let url = "includes=user,address&order_by=id,asc&page=" + $scope.page + "&status=" + $scope.status+"&delivery<"+$scope.activeDate+" 23:59:59";
                Food.getDeliveries(url).then(function (data) {
                    let deliveriesCont = data.data;
                    if (data.page == data.last_page) {
                        $scope.loadMore = false;
                    }
                    for (item in deliveriesCont) {
                        deliveriesCont[item] = $scope.buildDeliveryData(deliveriesCont[item]);
                    }
                    $scope.deliveries = deliveriesCont;
                    console.log("Deliveries",$scope.deliveries);
                    $scope.replaceFood();
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
                for (item in $scope.deliveries) {
                    $scope.deliveries[item].marker.setMap($rootScope.map);
                }
            }
            $scope.hideAll = function () {
                for (item in $scope.deliveries) {
                    $scope.deliveries[item].marker.setMap(null);
                }
            }
            $scope.showDelivery = function (delivery) {
                $scope.hideAll();
                delivery.marker.setMap($rootScope.map);
            }

            $scope.clean = function () {
                $scope.data = {};
                $scope.regionVisible = false;
                $scope.cityVisible = false;
            }
            $scope.updateDeliveryAddress = function (user,address) {

                Food.updateDeliveryAddress(user,address).then(function (data) {
                    window.location.reload();
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
            $scope.getArticle = function (id) {
                console.log("getArticle",id);
                for (let item in $scope.listArticles) {
                    if ($scope.listArticles[item].id == id) {
                        console.log("getArticleres",$scope.listArticles[item]);
                        return $scope.listArticles[item];
                    }
                }
            }
            $scope.replaceFood = function () {
                console.log("articles",$scope.listArticles);
                for (let item in $scope.deliveries) {
                    let attributes = $scope.deliveries[item].details;
                    let article = $scope.getArticle(attributes.dish.type_id);
                    
                    attributes.tipoAlmuerzo = article.name;
                    for (item in article.attributes.entradas) {
                        if (article.attributes.entradas[item].codigo == attributes.dish.starter_id) {
                            attributes.entrada = article.attributes.entradas[item].valor;
                        }
                    }
                    for (item in article.attributes.plato) {
                        if (article.attributes.plato[item].codigo == attributes.dish.main_id) {
                            attributes.plato = article.attributes.plato[item].valor;
                        }
                    }
                    delete attributes.dish;
                    $scope.deliveries[item].details = attributes;
                }
            }
            $scope.getArticles = function (date) {
                let day = date.getDay();
                if (day < 5) {
                    date.setDate(date.getDate() + 1);
                } else if (day == 5) {
                    date.setDate(date.getDate() + 3);
                } else if (day == 6) {
                    date.setDate(date.getDate() + 2);
                }
                $scope.activeDate = date.getFullYear() + '-' + (date.getMonth() + 1) + "-" + date.getDate();
                Food.getArticlesByDate($scope.activeDate).then(function (data) {
                    $scope.listArticles = data.data;
                    for (let item in $scope.listArticles) {
                        $scope.listArticles[item].attributes = JSON.parse($scope.listArticles[item].attributes);
                    }
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

        })