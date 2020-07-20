angular.module('besafe')

        .controller('OrdersCtrl', function ($scope, Orders) {
            $scope.data = {};
            $scope.orders;
            $scope.from;
            $scope.to;
            $scope.loadMore = true,
                    $scope.page = 0;
            angular.element(document).ready(function () {
                $scope.buildDatePicker();
                $scope.getOrders();
            });

            $scope.buildDatePicker = function () {
                var dateFormat = "yy-mm-dd",
                        from = $("#from")
                        .datepicker({
                            defaultDate: "-1m",
                            changeMonth: true,
                            numberOfMonths: 2,
                            dateFormat: dateFormat
                        })
                        .on("change", function () {
                            to.datepicker("option", "minDate", $scope.getDate(this));
                            $scope.from = $scope.getDate(this);
                        }),
                        to = $("#to").datepicker({
                    defaultDate: "+1w",
                    changeMonth: true,
                    numberOfMonths: 2,
                    dateFormat: dateFormat
                })
                        .on("change", function () {
                            from.datepicker("option", "maxDate", $scope.getDate(this));
                            $scope.to = $scope.getDate(this);
                        });
            }
            $scope.getDate = function (element) {
                console.log("Get date",element.value);
                var date;
                try {
                    date = $.datepicker.parseDate("yy-mm-dd", element.value);
                } catch (error) {
                    date = null;
                }

                return date;
            }

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
            $scope.updateOrderStatus = function (status, order_id) {

                Orders.updateOrderStatus(status, order_id).then(function (data) {

                },
                        function (data) {

                        });
            }
            $scope.getStoreExport = function () {
                console.log("Date", $scope.from, $scope.to);
                Orders.getStoreExport($scope.from, $scope.to).then(function (data) {

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