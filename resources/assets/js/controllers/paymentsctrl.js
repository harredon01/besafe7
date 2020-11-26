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

        $scope.approvePayment = function (payment) {
            Payments.approvePayment(payment.id).then(function (data) {
                payment.status = "approved";
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
        angular.element(document).ready(function () {
            $scope.getMerchants();
        });

        $scope.getOrders = function (merchant) {
            $scope.page++;
            let url = "includes=items,orderAddresses,user&order_by=id,desc&page=" + $scope.page + "&merchant_id=" + merchant;
            Orders.getOrders(url).then(function (data) {
                if (data.page == data.last_page) {
                    $scope.loadMore = false;
                }
                $scope.orders = data.data;

            },
                    function (data) {

                    });
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

        $scope.approvePayment = function (payment) {
            Payments.approvePayment(payment.id).then(function (data) {
                payment.status = "approved";
            },
                    function (data) {

                    });
        }

    }]).controller('PaymentDetailCtrl', ['$scope', 'Payments', '$rootScope', function ($scope, Payments, $rootScope) {
        $scope.data = {};
        $scope.payment = {};
        angular.element(document).ready(function () {
            $scope.getPayments();
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
                    $scope.payment = results[0]
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