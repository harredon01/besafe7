angular.module('besafe')

        .controller('MerchantsCtrl', function ($scope, LocationService, Merchants) {
            $scope.data = {};
            $scope.merchants;
            $scope.regionVisible = false;
            $scope.editMerchant = false;
            $scope.submitted = false;
            angular.element(document).ready(function () {
                $scope.getMerchants();
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
                    if ($scope.data.id) {
                        existing = true;
                    }
                    Merchants.saveMerchant($.param($scope.data)).then(function (data) {
                        if (existing) {
                            $scope.updateMerchant(data.merchant);
                        } else {
                            $scope.merchantes.push(data.merchant);
                        }

                        $scope.data = {};
                        $scope.submitted = false;
                        $scope.editMerchant = false;
                    },
                            function (data) {

                            });
                }
            }
            $scope.getMerchants = function () {
                Merchants.getMerchants().then(function (data) {
                    $scope.merchants = data.merchants;

                },
                        function (data) {

                        });
            }
            $scope.deleteMerchant = function (merchant_id) {
                Merchants.deleteMerchant(merchant_id).then(function (data) {
                    merchant_id = "" + merchant_id;
                    for (item in $scope.merchants) {
                        if ($scope.merchants[item].merchant_id == merchant_id) {
                            console.log("deleting merchant", $scope.merchants[item]);
                            $scope.merchants.splice(item, 1);
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
            $scope.updateMerchant = function (merchant) {
                for (item in $scope.merchants) {
                    if ($scope.merchants[item].id == merchant.id) {
                        $scope.merchants.splice(item, 1);
                        $scope.merchants.push(merchant);
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
            $scope.editMerchant = function (merchant_id) {
                $scope.editMerchant = true;
                for (item in $scope.merchants) {
                    if ($scope.merchants[item].id == merchant_id) {
                        $scope.data = $scope.merchants[item];
                        $scope.selectPlace($scope.data.country_id, $scope.data.region_id);
                    }
                }
            }

        })