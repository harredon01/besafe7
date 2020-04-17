angular.module('besafe')

        .controller('DeliveriesCtrl', function ($scope, $rootScope, Food, MapService) {
            $scope.data = {};
            $scope.listArticles = {};
            $scope.deliveries = [];
            $scope.page = 0;
            $scope.loadMore = true;
            $scope.status = 'enqueue';
            $scope.activeDate = '';
            $scope.regionVisible = false;
            $scope.editAddress = false;
            $scope.mapActive = false;
            angular.element(document).ready(function () {

                let date = new Date();
                let day = date.getDay();
                if (day < 6) {
                    date.setDate(date.getDate() + 1);
                }  else if (day == 6) {
                    date.setDate(date.getDate() + 2);
                }
                $scope.activeDate = date.getFullYear() + '-' + (date.getMonth() + 1) + "-" + date.getDate();
                $scope.getArticles(new Date());


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
                let firstdelivery = $scope.deliveries[0];
                let count = 0;
                let i = 0;
                for (let item in $scope.deliveries) {
                    i++;
                    if ($scope.deliveries[item].address_id == firstdelivery.address_id) {
                        count++;
                    } else {
                        firstdelivery.icon = 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=' + count + '|FE6256|000000'
                        MapService.createStop(firstdelivery);
                        firstdelivery = $scope.deliveries[item];
                        count = 1;
                    }
                    if (i == $scope.deliveries.length) {
                        firstdelivery.icon = 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=' + count + '|FE6256|000000'
                        MapService.createStop(firstdelivery);
                    }
                }
            }
            $scope.getDeliveries = function () {
                console.log("Status", $scope.status);
                $scope.page++;
                let url = "includes=user,address&limit=50&order_by=address_id,asc&page=" + $scope.page + "&status=" + $scope.status + "&delivery<" + $scope.activeDate + " 23:59:59";
                Food.getDeliveries(url).then(function (data) {
                    let deliveriesCont = data.data;
                    if (data.page == data.last_page) {
                        $scope.loadMore = false;
                    }
                    if (deliveriesCont.length > 0) {
                        for (item in deliveriesCont) {
                            let delivery = deliveriesCont[item];
                            delivery.details = JSON.parse(delivery.details);
                            delivery.name = delivery.address.address;
                            delivery.lat = delivery.address.lat;
                            delivery.delivery = new Date(delivery.delivery);
                            delivery.long = delivery.address.long;
                            $scope.deliveries.push(delivery);
                        }
                    }
                    console.log("Deliveries", $scope.deliveries);
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
            $scope.sendNewsletter = function () {
                Food.sendNewsletter();
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
            $scope.updateDeliveryAddress = function (user, address) {

                Food.updateDeliveryAddress(user, address).then(function (data) {
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
                console.log("getArticle", id);
                for (let item in $scope.listArticles) {
                    if ($scope.listArticles[item].id == id) {
                        console.log("getArticleres", $scope.listArticles[item]);
                        return $scope.listArticles[item];
                    }
                }
            }
            $scope.replaceFood = function () {
                console.log("articles", $scope.listArticles);
                for (let item in $scope.deliveries) {
                    console.log("delivery", $scope.deliveries[item].id);
                    let attributes = $scope.deliveries[item].details;
                    if (attributes.dish) {
                        $scope.deliveries[item].build = false;
                        $scope.deliveries[item].type_id = attributes.dish.type_id;
                        let article = $scope.getArticle(attributes.dish.type_id);
                        if (article) {
                            attributes.tipoAlmuerzo = article.name;
                            for (item2 in article.attributes.entradas) {
                                if (article.attributes.entradas[item2].codigo == attributes.dish.starter_id) {
                                    attributes.entrada = article.attributes.entradas[item2].valor;
                                    $scope.deliveries[item].starter_id = attributes.dish.starter_id;
                                }
                            }
                            for (item3 in article.attributes.plato) {
                                if (article.attributes.plato[item3].codigo == attributes.dish.main_id) {
                                    attributes.plato = article.attributes.plato[item3].valor;
                                    $scope.deliveries[item].main_id = attributes.dish.main_id;
                                }
                            }
                            delete attributes.dish;
                            delete attributes.products;
                            delete attributes.merchant_id;
                            $scope.deliveries[item].details = attributes;
                        }
                        console.log("deliveryDone", $scope.deliveries[item].id);
                    } else {
                        $scope.deliveries[item].type_id = null;
                        $scope.deliveries[item].starter_id = null;
                        $scope.deliveries[item].main_id = null;
                        $scope.deliveries[item].build = true;
                    }
                }
            }
            $scope.selectMissingType = function (item) {
                let article = $scope.getArticle(item.type_id);
                item.starters = article.attributes.entradas;
                item.mains = article.attributes.plato;
            }
            $scope.selectDish = function (item) {
                let attributes = item.details;
                let article = $scope.getArticle(item.type_id);
                attributes.tipoAlmuerzo = article.name;
                for (item2 in article.attributes.entradas) {
                    if (article.attributes.entradas[item2].codigo == item.starter_id) {
                        attributes.entrada = article.attributes.entradas[item2].valor;
                    }
                }
                for (item3 in article.attributes.plato) {
                    if (article.attributes.plato[item3].codigo == item.main_id) {
                        attributes.plato = article.attributes.plato[item3].valor;
                    }
                }
                item.details = attributes;
                let container = {
                    "delivery_id": item.id,
                    "type_id": item.type_id,
                    "main_id": item.main_id
                };
                if (item.starter_id) {
                    container.starter_id = item.starter_id;
                }
                item.build = false;
                console.log("Updating dish: ", container);
                Food.updateMissingDish(container);
            }
            $scope.getArticles = function (date) {
                Food.getArticlesByDate($scope.activeDate).then(function (data) {
                    $scope.listArticles = data.data;
                    for (let item in $scope.listArticles) {
                        $scope.listArticles[item].attributes = JSON.parse($scope.listArticles[item].attributes);
                    }
                    $scope.getDeliveries();
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