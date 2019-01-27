angular.module('besafe')

        .controller('PaymentsCtrl', function ($scope, Payments) {
            $scope.data = {};
            $scope.payments;
            $scope.regionVisible = false;
            $scope.editPayment = false;
            angular.element(document).ready(function () {
                $scope.getPayments();
            });

            $scope.getPayments = function () {
                Payments.getPayments().then(function (data) {
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