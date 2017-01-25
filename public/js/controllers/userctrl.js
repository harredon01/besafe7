angular.module('besafe')

        .controller('UserAddressesCtrl', function ($scope, LocationService, Users) {
            $scope.data = {};
            $scope.addresses;
            $scope.regionVisible = false;
            $scope.cityVisible = false;
            angular.element(document).ready(function () {
                $scope.getAddresses();
                LocationService.getCountries().then(function (data) {
                    $scope.countries = data.data;
                },
                        function (data) {

                        });
            });
            $scope.save = function (isvalid) {
                $scope.submitted = true;
                if (isvalid) {
                    Users.saveAddress($.param($scope.data)).then(function (data) {
                        $scope.getAddresses(); 
                        $scope.data = {};
                        $scope.submitted = false;
                    },
                            function (data) {

                            });
                }
            }
            $scope.getAddresses = function () {
                Users.getAddresses().then(function (data) {
                    $scope.addresses = data;

                },
                        function (data) {

                        });
            }
            $scope.deleteAddress = function (address_id) {
                Users.deleteAddress(address_id).then(function (data) {
                    $scope.getAddresses();
                },
                        function (data) {
                        });
            }
            $scope.billingAddress = function (address_id) {
                Users.setAsBillingAddress(address_id).then(function (data) {
                    $scope.getAddresses();
                },
                        function (data) {
                        });
            }
            $scope.clean = function () {
                $scope.data = {};
                $scope.regionVisible = false;
                $scope.cityVisible = false;
            }
            $scope.selectCountry = function (country_id) {
                LocationService.getRegionsCountry(country_id).then(function (data) {
                    $scope.regions = data.data;
                    $scope.regionVisible = true;
                    $scope.cityVisible = false;

                },
                        function (data) {

                        });
            }
            $scope.selectRegion = function (region_id) {
                LocationService.getCitiesRegion(region_id).then(function (data) {
                    $scope.cities = data.data;
                    $scope.cityVisible = true;

                },
                        function (data) {

                        });
            }
            $scope.selectPlace = function (country_id, region_id, city_id) {
                $scope.data.country_id = country_id;
                LocationService.getRegionsCountry(country_id).then(function (data) {
                    $scope.regions = data.data;
                    $scope.regionVisible = true;
                    $scope.cityVisible = false;
                    $scope.data.region_id = region_id;
                    LocationService.getCitiesRegion(region_id).then(function (data) {
                        $scope.cities = data.data;
                        $scope.cityVisible = true;
                        $scope.data.city_id = city_id;
                    },
                            function (data) {

                            });
                },
                        function (data) {

                        });
            }
            $scope.editAddress = function (address_id) {
                $scope.data.address_id = angular.element(document.querySelector('li#address-' + address_id + ' span.address_id')).html();
                $scope.data.firstName = angular.element(document.querySelector('li#address-' + address_id + ' span.firstName')).html();
                $scope.data.lastName = angular.element(document.querySelector('li#address-' + address_id + ' span.lastName')).html();
                $scope.data.address = angular.element(document.querySelector('li#address-' + address_id + ' span.address')).html();
                $scope.data.phone = angular.element(document.querySelector('li#address-' + address_id + ' span.phone')).html();
                $scope.data.postal = angular.element(document.querySelector('li#address-' + address_id + ' span.postal')).html();
                $scope.selectPlace(angular.element(document.querySelector('li#address-' + address_id + ' span.country_id')).html(),
                        angular.element(document.querySelector('li#address-' + address_id + ' span.region_id')).html(),
                        angular.element(document.querySelector('li#address-' + address_id + ' span.city_id')).html());
            }

        }).controller('NotificationsCtrl', function ($scope, $rootScope, Alerts) {
            // With the new view caching in Ionic, Controllers are only called
            // when they are recreated or on app start, instead of every page change.             // To listen for when this page is active (for example, to refresh data),
            // listen for the $ionicView.enter event:
            //
            $scope.notifications = [];
            $rootScope.$broadcast('updateNotifList');
            $scope.noavatar = API.avatar;
            $scope.delete = function (notification) {
                Alerts.deleteNotification(notification);
            };
            $scope.followNotification = function (id) {
                Alerts.get(id).then(function (data) {
                    console.log("After Get:");
                    console.log(JSON.stringify(data));
                    Alerts.followNotification(data);
                    console.log("After follow:");
                    console.log(JSON.stringify(data));
                },
                        function (data) {
                        });
            };
            $rootScope.$on('updateNotifList', function () {
                $scope.notifications = [];
                Alerts.loadNotificationsGlobal("50").then(function (data) {
                    for (notif in data) {
                        data[notif].created_at = new Date(data[notif].created_at);
                        if (data[notif].type == "location_first") {
                            data[notif].class = 'ion-android-pin';
                        } else if (data[notif].type == "location_last") {
                            data[notif].class = 'ion-android-pin';
                        } else if (data[notif].type == "notification_location") {
                            data[notif].class = 'ion-android-compass';
                        } else if (data[notif].type == "emergency") {
                            data[notif].class = 'ion-android-warning';
                        } else if (data[notif].type == "medical_emergency") {
                            data[notif].class = 'ion-ios-medkit';
                        } else if (data[notif].type == "emergency_end") {
                            data[notif].class = 'ion-flag';
                        } else if (data[notif].type == "user_message") {
                            data[notif].class = 'ion-email';
                        } else if (data[notif].type == "group_message") {
                            data[notif].class = 'ion-email';
                        } else if (data[notif].type == "new_contact") {
                            data[notif].class = 'ion-person-stalker';
                        } else if (data[notif].type == "new_group") {
                            data[notif].class = 'ion-person-stalker';
                        } else if (data[notif].type == "system") {
                            data[notif].class = 'ion-alert';
                        }
                        try {
                            $scope.notifications.push(data[notif]);
                        }
                        catch (err) {
                            console.log("Repetido");
                            console.log(JSON.stringify(data[notif]));
                        }

                    }
                    $rootScope.notificationsChanged = false;
                },
                        function (data) {
                        });
            });
            $rootScope.$on('receivedNotification', function (event, args) {
                $rootScope.$broadcast('updateNotifList');
            });
            $scope.$on('$stateChangeSuccess', function (event, toState, toParams, fromState, fromParams) {
                if (toState.name == 'tab.notifications') {
                    console.log("Entering notifications");
                    if ($rootScope.notificationsChanged == true) {
                        $rootScope.$broadcast('updateNotifList');
                    }
                }
            });
        })