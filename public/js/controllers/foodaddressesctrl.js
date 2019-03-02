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
            $scope.delegateAddress = function (address,complete ) {
                let provider = "";
                if(address.provider == "Rapigo"){
                    provider = "Basilikum";
                } else {
                    provider = "Rapigo";
                }
                let theDate = new Date(address.delivery);
                let container = {
                    "address":address.address,
                    "provider":provider,
                    "complete":complete,
                    "merchant_id":address.merchant_id
                };
                Food.delegateDeliveriesAddress(container).then(function (data) {

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