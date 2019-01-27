angular.module('besafe')

        .controller('OrdersCtrl', function ($scope, Orders) {
            $scope.data = {};
            $scope.orders;
            angular.element(document).ready(function () {
                $scope.getOrders();
            });

            $scope.getOrders = function () {
                Orders.getOrders().then(function (data) {
                    $scope.orders = data.data;

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
            $scope.approveOrder = function (order) {
                Orders.approveOrder(order.id).then(function (data) {
                    order.status = "scheduled";
                },
                        function (data) {

                        });
            }

        })