angular.module('besafe')

        .controller('FoodAddressesCtrl', function ($scope, $rootScope, Routes, Food, MapService) {
            $scope.data = {};
            $scope.addresses;
            $scope.regionVisible = false;
            $scope.editAddress = false;
            $scope.mapActive = false;
            angular.element(document).ready(function () {
                $scope.getAddresses();
            });
            $scope.delegate = function (address) {
                Food.delegateAddress(address).then(function (data) {

                    },
                            function (data) {

                            });
            }

            $scope.buildAddressData = function (address) {
                return address;
            }
            $scope.getAddresses = function () {
                Food.getLargestAddresses().then(function (data) {
                    let addressesCont = data.data;
                    for (item in addressesCont) {
                        addressesCont[item] = $scope.buildAddressData(addressesCont[item]);
                    }
                    $scope.addresses = addressesCont;
                },
                        function (data) {

                        });
            }


        })