angular.module('besafe')

        .controller('PaymentsCtrl', function ($scope, Payments) {
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
                let url = "includes=order&order_by=id,desc&page=" + $scope.page;
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

        })