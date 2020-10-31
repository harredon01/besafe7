angular.module('besafe')

        .controller('AddressesCtrl',['$scope', 'LocationService', 'Addresses', function ($scope, LocationService, Addresses) {
            $scope.data = {};
            $scope.addresses;
            $scope.regionVisible = false;
            $scope.editAddress = false;
            angular.element(document).ready(function () {
                $scope.getAddresses();
                var where = "";
                LocationService.getCountries(where).then(function (data) {
                    $scope.countries = data.data;
                },
                        function (data) {

                        });
            });
            $scope.save = function (isvalid) {
                $scope.submitted = true;
                if (isvalid) {
                    var existing = false;
                    if ($scope.data.address_id) {
                        existing = true;
                    }
                    Addresses.saveAddress($.param($scope.data)).then(function (data) {
                        if (existing) {
                            $scope.updateAddress(data.address);
                        } else {
                            $scope.addresses.push(data.address);
                        }

                        $scope.data = {};
                        $scope.submitted = false;
                        $scope.editAddress = false;
                    },
                            function (data) {

                            });
                }
            }
            $scope.getAddresses = function () {
                Addresses.getAddresses().then(function (data) {
                    $scope.addresses = data.addresses;

                },
                        function (data) {

                        });
            }
            $scope.deleteAddress = function (address_id) {
                Addresses.deleteAddress(address_id).then(function (data) {
                    address_id = "" + address_id;
                    for (item in $scope.addresses) {
                        if ($scope.addresses[item].address_id == address_id) {
                            console.log("deleting address", $scope.addresses[item]);
                            $scope.addresses.splice(item, 1);
                        }
                    }
                },
                        function (data) {
                        });
            }
            $scope.clean = function () {
                $scope.data = {};
                $scope.regionVisible = false;
                $scope.cityVisible = false;
            }
            $scope.updateAddress = function (address) {
                for (item in $scope.addresses) {
                    if ($scope.addresses[item].address_id == address.address_id) {
                        $scope.addresses.splice(item, 1);
                        $scope.addresses.push(address);
                    }
                }
            }
            $scope.selectCountry = function () {
                for (item in $scope.countries) {
                    if ($scope.countries[item].id == $scope.data.country_id) {
                        $scope.data.country = $scope.countries[item].country_iso;
                    }
                }
                LocationService.getRegionsCountry($scope.data.country_id).then(function (data) {
                    $scope.regions = data.data;
                    $scope.regionVisible = true;
                    $scope.cityVisible = false;

                },
                        function (data) {

                        });
            }
            $scope.selectRegion = function () {
                for (item in $scope.regions) {
                    if ($scope.countries[item].id == $scope.data.region_id) {
                        $scope.data.region = $scope.countries[item].name;
                    }
                }
            }
            $scope.selectPlace = function (country_id, region_id) {
                $scope.data.country_id = country_id;
                for (item in $scope.countries) {
                    if ($scope.countries[item].id == $scope.data.country_id) {
                        $scope.data.country = $scope.countries[item].country_iso;
                    }
                }
                LocationService.getRegionsCountry(country_id).then(function (data) {
                    $scope.regions = data.data;
                    $scope.regionVisible = true;
                    $scope.cityVisible = false;
                    $scope.data.region_id = region_id;
                    $scope.selectRegion();
                },
                        function (data) {

                        });
            }
            $scope.editAddress = function (address_id) {
                $scope.editAddress = true;
                for (item in $scope.addresses) {
                    if ($scope.addresses[item].id == address_id) {
                        $scope.data = $scope.addresses[item];
                        $scope.data.address_id = address_id;
                        $scope.selectPlace($scope.data.country_id, $scope.data.region_id);
                    }
                }
            }

        }])