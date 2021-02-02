angular.module('besafe')

        .controller('PaymentsCtrl', ['$scope', 'Payments', 'Merchants', function ($scope, Payments, Merchants) {
                $scope.data = {};
                $scope.payments;
                $scope.loadMore = true,
                        $scope.page = 0;
                $scope.regionVisible = false;
                $scope.editPayment = false;
                angular.element(document).ready(function () {
                    $scope.getPayments();
                });

                $scope.getPayments = function () {
                    $scope.page++;
                    let url = "includes=order,user&order_by=id,desc&page=" + $scope.page;
                    Payments.getPayments(url).then(function (data) {
                        if (data.page == data.last_page) {
                            $scope.loadMore = false;
                        }
                        $scope.payments = data.data;

                    },
                            function (data) {

                            });
                }

                $scope.approvePayment = function (payment) {
                    Payments.approvePayment(payment.id).then(function (data) {
                        payment.status = "approved";
                    },
                            function (data) {

                            });
                }

            }]).controller('PaymentsUserCtrl', ['$scope', 'Payments', function ($scope, Payments) {
        $scope.data = {};
        $scope.payments;
        $scope.loadMore = true,
                $scope.page = 0;
        $scope.regionVisible = false;
        $scope.editPayment = false;
        angular.element(document).ready(function () {
            $scope.getPayments();
        });

        $scope.getPayments = function () {
            $scope.page++;
            let url = "includes=order,user&order_by=id,desc&page=" + $scope.page;
            Payments.getPaymentsUser(url).then(function (data) {
                if (data.page == data.last_page) {
                    $scope.loadMore = false;
                }
                $scope.payments = data.data;

            },
                    function (data) {

                    });
        }

    }]).controller('PaymentsMerchantCtrl', ['$scope', 'Orders', 'Merchants', function ($scope, Orders, Merchants) {
        $scope.data = {};
        $scope.orders = [];
        $scope.merchants = [];
        $scope.merchant_id;
        $scope.loadMore = true,
                $scope.page = 0;
        $scope.regionVisible = false;
        $scope.editPayment = false;
        $scope.status = "pending";
        angular.element(document).ready(function () {
            $scope.getMerchants();
        });

        $scope.getOrders = function (merchant) {
            $scope.page++;
            let url = "includes=items,orderAddresses,orderConditions,user&order_by=id,asc&page=" + $scope.page + "&merchant_id=" + merchant + "&execution_status=" + $scope.status;
            Orders.getOrders(url).then(function (data) {
                if (data.page == data.last_page) {
                    $scope.loadMore = false;
                }
                $scope.orders = $scope.orders.concat(data.data);

            },
                    function (data) { 

                    });
        }
        $scope.loadMoreOrders = function () {
            $scope.getOrders($scope.merchant_id);
        }
        $scope.selectMerchant = function () {
            $scope.orders = [];
            $scope.page = 0;
            $scope.getOrders($scope.merchant_id);
        }
        $scope.getMerchants = function () {
            Merchants.getMerchantsUser().then(function (data) {
                if (data.status == "success") {
                    $scope.merchants = data.data;
                    if ($scope.orders.length == 0 && $scope.merchants.length > 0) {
                        $scope.getOrders($scope.merchants[0].id);
                    }
                }

            },
                    function (data) {

                    });
        }

        $scope.fullfillOrder = function (order) {
            let itemsArr = [];
            for (let item in order.items) {
                itemsArr.push(order.items[item].id);
            }
            let container = {
                order_id: order.id,
                items: itemsArr,
                status:"fullfill"
            }
            Orders.fullfillOrder(container).then(function (data) {
                if (data.status == "success") {
                    order.execution_status = "completed";
                    for (let item in order.items) {
                        order.items[item].fulfillment = "fulfilled"; 
                    }
                }

            },
                    function (data) {

                    });
        }

    }]).controller('PaymentsUserCtrl', ['$scope', 'Payments', function ($scope, Payments) {
        $scope.data = {};
        $scope.payments;
        $scope.loadMore = true,
                $scope.page = 0;
        $scope.regionVisible = false;
        $scope.editPayment = false;
        angular.element(document).ready(function () {
            $scope.getPayments();
        });

        $scope.getPayments = function () {
            $scope.page++;
            let url = "includes=order,user&order_by=id,desc&page=" + $scope.page;
            Payments.getPaymentsUser(url).then(function (data) {
                if (data.page == data.last_page) {
                    $scope.loadMore = false;
                }
                $scope.payments = data.data;

            },
                    function (data) {

                    });
        }

    }]).controller('PaymentDetailCtrl', ['$scope', 'Payments', '$rootScope','LocationService', function ($scope, Payments, $rootScope,LocationService) {
        $scope.data = {};
        $scope.payment = {};
        $scope.hasTransactionCost = false;
        angular.element(document).ready(function () {
            $scope.getPayments();
        });
        $rootScope.$on('removeTransactionCost', function (event, args) {
                    $scope.hasTransactionCost = false;
                });
                $rootScope.$on('addTransactionCost', function (event, args) {
                    $scope.hasTransactionCost = true;

                });

        $scope.getPayments = function () {
            $scope.page++;
            let url = window.location.href;
            let segments = url.split("/");
            console.log("Payment id", segments[segments.length - 1])
            let url2 = "id=" + segments[segments.length - 1] + "&includes=order.items,order.orderConditions,order.orderAddresses";
            Payments.getPaymentsUser(url2).then(function (data) {
                if (data.total > 0) {
                    let results = data.data;
                    $scope.payment = results[0];
                    for(let item in $scope.payment.order.items){
                        $scope.payment.order.items[item].attributes = JSON.parse($scope.payment.order.items[item].attributes);
                    }
                    console.log("Payment",$scope.payment);
                    if($scope.payment.transaction_cost>0){
                        $scope.hasTransactionCost = true;
                    }
                    $rootScope.activeOrder = $scope.payment.order;
                    LocationService.getRegion($rootScope.activeOrder.order_addresses[0].region_id).then(function (data) {
                        if(data.total && data.total == 1){
                            console.log("Response",data);
                            $rootScope.activeOrder.order_addresses[0].regionName = data.data[0].name;
                        }
                    },
                    function (data) {

                    });
                    LocationService.getCity($rootScope.activeOrder.order_addresses[0].city_id).then(function (data) {
                        if(data.total && data.total == 1){
                            console.log("Response",data);
                            $rootScope.activeOrder.order_addresses[0].cityName = data.data[0].name;
                        }
                    },
                    function (data) {

                    });
                    LocationService.getCountry($rootScope.activeOrder.order_addresses[0].country_id).then(function (data) {
                        if(data.total && data.total == 1){
                            console.log("Response",data);
                            $rootScope.activeOrder.order_addresses[0].countryCode = data.data[0].code;
                        }
                    },
                    function (data) {

                    });
                }

            },
                    function (data) {

                    });
        }

        $scope.addTransactionCosts = function () {
            Payments.addTransactionCosts($scope.payment.id).then(function (data) {
                if (data.status == "success") {
                    console.log("after addTransactionCosts");
                    $scope.payment = data.payment;
                } else {
                    this.api.toast('PAYMENTS.ERROR_CHANGE');
                }
            },
                    function (data) {

                    });
        }

        $scope.retryPayment = function () {
            Payments.retryPayment($scope.payment.id).then(function (data) {
                console.log("after addTransactionCosts", data);
                if (data.status == "success") {

                    $rootScope.activePayment = data.payment;
                    $rootScope.paymentActive = true;
                } else {
                    this.api.toast('PAYMENTS.ERROR_CHANGE');
                }
            },
                    function (data) {

                    });
        }

    }])