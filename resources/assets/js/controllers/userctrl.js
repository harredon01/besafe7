angular.module('besafe')

        .controller('NotificationsCtrl', ['$scope', '$rootScope', 'Alerts', function ($scope, $rootScope, Alerts) {
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
                    } catch (err) {
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
    }]).controller('UserRegisterCtrl', ['$scope', 'LocationService', 'Users', function ($scope, LocationService, Users) {
        $scope.data = {};
        $scope.addresses;
        $scope.regionVisible = false;
        $scope.cityVisible = false;
        LocationService.getCountries("").then(function (data) {
            $scope.countries = data.data;
        },
                function (data) {

                });

    }]).controller('UserProfileCtrl', ['$scope', '$rootScope', 'Users','Modals', function ($scope, $rootScope, Users,Modals) {
        $scope.data = {};
        angular.element(document).ready(function () {
            $scope.data = $rootScope.user;
        });
        $rootScope.$on('user_loaded', function (event, args) {
            console.log("Loading user");
            $scope.data = $rootScope.user;
        });

        $scope.save = function (isvalid) {
            $scope.submitted = true;
            if (isvalid) {
                Modals.showLoader();
                Users.saveUser($scope.data).then(function (data) {
                    Modals.hideLoader();
                    console.log("Resp",data);
                    $scope.submitted = false;
                    if(data.status == "success"){
                        Modals.showToast("Actualizacion exitosa",$("#myaccountContent"));
                        $scope.data = data.user;
                    } else {
                        Modals.showToast("Hubo un error actualizando tus datos",$("#myaccountContent"));
                    }
                },
                        function (data) {

                        });
            }
        }

    }]).controller('UserPasswordCtrl', ['$scope', '$rootScope', 'Users','Modals', function ($scope, $rootScope, Users,Modals) {
        $scope.data = {};

        $scope.save = function (isvalid) {
            $scope.submitted = true;
            if (isvalid) {
                
                if($scope.data.password != $scope.data.password_confirmation){
                    return;
                }
                Modals.showLoader();
                Users.updatePassword($scope.data).then(function (data) {
                    Modals.hideLoader();
                    console.log("Resp",data);
                    $scope.submitted = false;
                    if(data.status == "success"){
                        Modals.showToast("Actualizacion exitosa",$("#myaccountContent"));
                        //$scope.data = data.user;
                    }
                },
                        function (data) {
                            Modals.hideLoader();
                            Modals.showToast("Contraseña incorrecta",$("#myaccountContent"));
                        });
            }
        }

    }])