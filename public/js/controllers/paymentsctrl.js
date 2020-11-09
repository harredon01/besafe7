angular.module('besafe')

        .controller('PaymentsCtrl', ['$scope', 'Payments', function ($scope, Payments) {
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

    }]).controller('PaymentDetailCtrl', ['$scope', 'Payments', function ($scope, Payments) {
        $scope.data = {};
        $scope.payment = {};
        angular.element(document).ready(function () {
            $scope.getPayments();
        });

        $scope.getPayments = function () {
            $scope.page++;
            let url = window.location.href;
            let segments = url.split("/");
            console.log("Payment id", segments[segments.length + 1])
            let url = "id=" + segments[segments.length + 1] + "&includes=order.items,order.orderConditions";
            Payments.getPaymentsUser(url).then(function (data) {
                if (data.total > 0) {
                    let results = data.data;
                    $scope.payment = results[0]
                }

            },
                    function (data) {

                    });
        }

        $scope.addTransactionCosts = function (payment) {
            Payments.addTransactionCosts(payment.id).then(function (data) {
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
        
        $scope.retryPayment = function (payment) {
            Payments.retryPayment(payment.id).then(function (data) {
                if (data.status == "success") {
                    console.log("after addTransactionCosts");
                    
                } else {
                    this.api.toast('PAYMENTS.ERROR_CHANGE');
                }
            },
                    function (data) {

                    });
        }

    }])