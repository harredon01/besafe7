angular.module('besafe')

        .controller('OrdersCtrl', function ($scope, Orders) {
            $scope.data = {};
            $scope.orders;
            $scope.loadMore = true,
            $scope.page = 0;
            angular.element(document).ready(function () {
                $scope.getOrders();
            });
            
            $scope.buildOrderData = function (order) {
                let items = order.items;
                for (item in items) {
                    items[item].attributes = JSON.parse(items[item].attributes);
                }
                order.items = items;
                return order;
            }

            $scope.getOrders = function () {
                $scope.page++;
                let url = "includes=orderConditions,items,payments,user&order_by=id,desc&page=" + $scope.page;
                Orders.getOrders(url).then(function (data) {
                    if (data.page == data.last_page) {
                        $scope.loadMore = false;
                    }
                    let ordersCont = data.data;
                    for (item in ordersCont) {
                        ordersCont[item] = $scope.buildOrderData(ordersCont[item]);
                    }
                    $scope.orders = ordersCont;

                },
                        function (data) {

                        });
            }
            $scope.updateOrderStatus = function (status,order_id) {

                Orders.updateOrderStatus(status,order_id).then(function (data) {

                },
                        function (data) {

                        });
            }
            $scope.getStoreExport = function () {

                Orders.getStoreExport().then(function (data) {

                },
                        function (data) {

                        });
            }
            $scope.approveOrder = function (order) {
                Orders.approveOrder(order.id).then(function (data) {
                    order.status = "scheduled";
                },
                        function (data) {

                        });
            }

        })