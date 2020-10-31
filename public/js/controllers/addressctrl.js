angular.module('besafe')

        .controller('AddressesCtrl', ['$scope', '$rootScope', 'LocationService', 'Addresses', '$mdDialog', 'Modals', '$cookies',
            function ($scope, $rootScope, LocationService, Addresses, $mdDialog, Modals, $cookies) {
                $scope.data = {};
                $scope.addresses;
                $scope.regionVisible = false;
                $scope.editAddress = false;
                angular.element(document).ready(function () {
                    $scope.getAddresses();
                    var where = "";
                    let checkAddress = $cookies.get("creating_address");
                    if (checkAddress) {
                        $cookies.remove("creating_address",{path: "/"});
                        console.log("creat",$cookies.get("creating_address"));
                        console.log("Loading address from cookie",$rootScope.shippingAddress);
                        $scope.editAddress = true;
                        $scope.data = $rootScope.shippingAddress;
                        $scope.data.address_id = $rootScope.shippingAddress.id;
                        if($scope.data.name && $scope.data.name.length > 0){
                            
                        } else {
                            $scope.data.name = $rootScope.user.name;
                        } 
                        if($scope.data.phone && $scope.data.phone.length > 0){
                            
                        } else {
                            $scope.data.phone = $rootScope.user.cellphone;
                        }
                        
                        $scope.selectPlace($scope.data.country_id, $scope.data.region_id);
                    }
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
                        $cookies.remove("creating_address",{path: "/"});
                        $cookies.remove("creating_address",{path: "/"});
                        $cookies.remove("creating_address",{path: "/"});
                        if ($scope.data.address_id) {
                            existing = true;
                        }
                        Addresses.saveAddress($scope.data).then(function (data) {
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
                $scope.newAddress = function () {
                    $mdDialog.show(Modals.getLocationPrompt()).then(function (answer) {
                        if (answer == "map") {
                            $cookies.put("locationRefferrer", window.location.href, {path: "/"});
                            $cookies.put("creating_address", true, {path: "/"});
                            window.location.href = "/location";
                        } else if (answer == 'address') {
                            showAdvanced();
                        }

                    }, function () {

                    });
                };
                $scope.deleteAddress = function (address) {
                    $cookies.remove("shippingAddress",{path: "/"});
                    Addresses.deleteAddress(address.id).then(function (data) {
                        for (item in $scope.addresses) {
                            console.log("a",$scope.addresses[item].id);
                            console.log("b",address.id);
                            console.log("c",$scope.addresses[item].id == address.id);
                            if ($scope.addresses[item].id == address.id) {
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
                        if ($scope.addresses[item].id == address.id) {
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
                    LocationService.getCitiesRegion($scope.data.region_id).then(function (data) {
                            $scope.cities = data.data;
                            $scope.cityVisible = true;
                            console.log("Setting cities", $scope.cities);
                        },
                                function (data) {
                                });
                }
                $scope.selectPlace = function (country_id, region_id, city_id) {
                    //$scope.$apply(function () {
                    $scope.data.country_id = country_id;
                    LocationService.getRegionsCountry(country_id).then(function (data) {
                        $scope.regions = data.data;
                        $scope.regionVisible = true;
                        $scope.cityVisible = false;
                        console.log("Setting region", $scope.data.region_id);
                        $scope.data.region_id = region_id;
                        LocationService.getCitiesRegion(region_id).then(function (data) {
                            $scope.cities = data.data;
                            $scope.cityVisible = true;
                            console.log("Setting city_id", $scope.data.city_id);
                            console.log("Setting cities", $scope.cities);
                            $scope.data.city_id = city_id;
                        },
                                function (data) {
                                });
                    },
                            function (data) {
                            });
                    //});
                }
                $scope.editAddressObj = function (address) {
                    $scope.editAddress = true;
                    if (address.lat) {
                        $cookies.put("shippingAddress", JSON.stringify(address), {path: "/"});
                        $cookies.put("locationRefferrer", window.location.href, {path: "/"});
                        $cookies.put("creating_address", true, {path: "/"});
                        window.location.href = "/location";
                    } else {
                        for (item in $scope.addresses) {
                            if ($scope.addresses[item].id == address.id) {
                                $scope.data = $scope.addresses[item];
                                $scope.data.address_id = address.id;
                                $scope.selectPlace($scope.data.country_id, $scope.data.region_id);
                            }
                        }
                    }
                }
            }])