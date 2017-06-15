angular.module('besafe')
        .factory('Alerts', function ($q, $rootScope, $http, $ionicPopup, $cordovaToast, $state, MapService, $cordovaSQLite, $ionicHistory, Groups, Chats, Contacts, API) {
            // Might use a resource here that returns a JSON array
            var RED_MESSAGE_TYPE = 'emergency';
            var RED_MESSAGE_END = 'emergency_end';
            var USER_MESSAGE = 'user_message';
            var GROUP_MESSAGE = 'group_message';
            var RED_MESSAGE_MEDICAL_TYPE = 'medical_emergency';
            var NEW_CONTACT = 'new_contact';
            var NEW_GROUP = 'new_group';
            var TRACKING_LIMIT = 'tracking_limit';
            var LOCATION_LAST = 'location_last';
            var LOCATION_FIRST = 'location_first';
            var NOTIFICATION_LOCATION = 'notification_location';
            var LAST_NOTIFICATION = 'last_notification';
            var openNotifications = function (notifs) {
                var def = $q.defer();
                $http.post(API.address + "/alertsapi/read", notifs)
                        .success(function (data) {
                            for (notif in notifs) {
                                var query = "UPDATE notifications set status = 'read' where notification_id = ?";
                                $cordovaSQLite.execute(db, query, [notifs[notif].id]).then(function (res) {
                                    console.log("Notifications read");
                                    $rootScope.notificationsUnreadChanged = true;
                                }, function (err) {
                                    console.error(JSON.stringify(err));
                                });
                            }

                            def.resolve(data);
                        })
                        .error(function (data) {
                            console.log(data);

                            def.reject("Failure marking notifications as read");
                        });
                return def.promise;
            };
            var handleNewContact = function (message) {
                console.log("new contact message received");
            };
            var handleSystemMessage = function (message) {
                console.log("system message received");
            };
            var handleUserMessage = function (message) {
                console.log("user message received");
                console.log(JSON.stringify(message));
                var payload = message.payload;
                var finalData = payload.replace(/\\/g, "");
                var finalParsed = JSON.parse(finalData);
                var name = finalParsed.firstname + " " + finalParsed.lastname;
                message.name = name;
            };
            var handleGroupMessage = function (message) {
                console.log("group message received");
                var payload = message.payload;
                var finalData = payload.replace(/\\/g, "");
                var finalParsed = JSON.parse(finalData);
                var name = finalParsed.firstname + " " + finalParsed.lastname;
            };
            var handleEmergency = function (message) {
                console.log(message.type + " message received");
            };
            var handleEmergencyEnd = function (message) {
                console.log(message.type + " message received");
                Contacts.updateContactStatus(message.trigger_id, "normal").then(function (res2) {
                    MapService.removeFromUnknowns(message.trigger_id);
                }, function (err) {
                    console.error(JSON.stringify(err));
                });
                Contacts.clearData(message.trigger_id);
                showEmergency(message);
            };

            var followNotification = function (notification, contact_id) {
                var notifs = {id: notification.notification_id};
                var dareads = [notifs];
                console.log("Following notification: ");
                console.log(JSON.stringify(notification));
                notification.status = 'open';
                if (notification.type == USER_MESSAGE) {

                } else if (notification.type == NEW_CONTACT) {

                } else if (notification.type == NEW_GROUP) {

                } else if (notification.type == GROUP_MESSAGE) {

                } else if (notification.type == NOTIFICATION_LOCATION) {

                } else if (notification.type == RED_MESSAGE_TYPE || notification.type == RED_MESSAGE_MEDICAL_TYPE) {

                } else if (notification.type == LOCATION_FIRST) {

                } else if (notification.type == LOCATION_LAST) {

                } else if (notification.type == "system") {

                }
                else if (notification.type == RED_MESSAGE_END) {
                    $state.go('tab.contact-detail', {contactId: notification.trigger_id});
                }
                openNotifications(dareads);
            };

            var handleNewGroup = function (message) {
            };
            var handleNotificationLocation = function (message) {
            };
            var insertLocation = function (message) {
                console.log("Insert Location");
                MapService.removeFromUnknowns(message.trigger_id);
                console.log(JSON.stringify(message));
                var payload = message.payload;
                var info = [];
                info.id = message.id;
                info.user_id = message.trigger_id;
                info.lat = payload.location.lat;
                info.long = payload.location.long;
                info.report_time = payload.location.report_time.date;
                info.name = message.name;
                info.status = message.status;
                info.speed = payload.location.speed;
                info.activity = payload.location.activity;
                info.battery = payload.location.battery;
                info.islast = 1;
                var damarkers = [];
                damarkers.push(info);
                MapService.checkMarkers(damarkers);
            };
            var handleFirstLocation = function (message) {
                console.log("first location message received");
                console.log(JSON.stringify(message));
                $rootScope.$broadcast('StartTracking');
                var payload = message.payload;
                var finalData = payload.replace(/\\/g, "");
                var payload = JSON.parse(finalData);
                message.payload = payload;
                insertLocation(message);
                message.payload = JSON.stringify(payload);
            };
            var getTrip = function (user_id, trip) {
                var checkdata = {user_id: user_id, trip: trip};
                console.log("Check data");
                console.log(JSON.stringify(checkdata));
                MapService.saveTrip(checkdata);
            };
            var handleLastLocation = function (message) {
                console.log("last location message received");
                console.log(JSON.stringify(message));
                Contacts.updateContactStatus(message.trigger_id, "normal").then(function (res2) {
                    MapService.removeFromUnknowns(message.trigger_id);
                }, function (err) {
                    console.error(JSON.stringify(err));
                });
                var payload = message.payload;
                var finalData = payload.replace(/\\/g, "");
                var payload = JSON.parse(finalData);
                message.payload = payload;
                insertLocation(message);
                message.payload = JSON.stringify(payload);
                getTrip(message.trigger_id, payload.location.trip);
            };
            var cleanNotifications = function (type, trigger_id) {

            };
            var loadNotificationsTrigger = function (type, trigger) {
                var def = $q.defer();
                console.log("Trying sql load notifications trigger: " + trigger + " type: " + type);
                if (type == "user") {
                    var query = "SELECT * FROM notifications where trigger_id = ? and (type = '" + RED_MESSAGE_TYPE + "' " +
                            " or type = '" + RED_MESSAGE_END + "' " +
                            " or type = '" + USER_MESSAGE + "' " +
                            " or type = '" + RED_MESSAGE_MEDICAL_TYPE + "' " +
                            " or type = '" + LOCATION_FIRST + "' " +
                            " or type = '" + LOCATION_LAST + "' " +
                            " or type = '" + NOTIFICATION_LOCATION + "' " +
                            " or type = '" + NEW_CONTACT + "' " +
                            " ) order by id desc ";
                } else if (type == "group") {
                    var query = "SELECT * FROM notifications where trigger_id = ? and (type = '" + GROUP_MESSAGE + "' " +
                            " or type = '" + NEW_GROUP + "' " +
                            " ) order by id desc ";
                }
                console.log(query);
                $cordovaSQLite.execute(db, query, [trigger]).then(function (res) {
                    console.log("after sql load notifications trigger");
                    var danotifications = [];
                    console.log("results in database" + res.rows.length);
                    if (res.rows.length > 0) {
                        for (i = 0; i < res.rows.length; i++) {
                            res.rows.item(i).avatar = API.address + "/" + res.rows.item(i).trigger_id + ".jpg"
                            danotifications.push(res.rows.item(i));
                        }
                    } else {
                        console.log("No results found");
                    }
                    def.resolve(danotifications);
                }, function (err) {
                    console.log("Error loading notifications trigger");
                    console.error(JSON.stringify(err));
                });
                return def.promise;
            };
            var verifyActions = function (data) {
                var unreads = [];
                data.reverse();
                for (notification in data) {
                    message = data[notification];
                    if (message.status == "unread") {

                        $rootScope.notificationsChanged = true;
                        $rootScope.notificationsUnreadChanged = true;
                        //cleanNotifications(message.type, message.trigger_id);
                        unreads.push(message);
                        if (message.type == NOTIFICATION_LOCATION) {
                            handleNotificationLocation(message);
                        } else if (message.type == LOCATION_FIRST) {
                            handleFirstLocation(message);
                        } else if (message.type == LOCATION_LAST) {
                            handleLastLocation(message);
                        } else if (message.type == NEW_GROUP) {
                            handleNewGroup(message);
                        } else if (message.type == RED_MESSAGE_MEDICAL_TYPE || message.type == RED_MESSAGE_TYPE) {
                            handleEmergency(message);
                        } else if (message.type == USER_MESSAGE) {
                            handleUserMessage(message);
                        } else if (message.type == GROUP_MESSAGE) {
                            handleGroupMessage(message);
                        } else if (message.type == NEW_CONTACT) {
                            handleNewContact(message);
                        } else if (message.type == RED_MESSAGE_END) {
                            handleEmergencyEnd(message);
                        }
                        else if (message.type == "system") {
                            handleSystemMessage(message);
                        }
                    }
                }
            };
            var getNotificationsAfter = function () {

                var def = $q.defer();

                $http({
                    method: "get",
                    url: API.address + "/alertsapi/after/" + $rootScope.lastNotification,
                })
                        .success(function (data) {
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to get Contacts");
                        });

                return def.promise;
            };
            var deleteNotification = function (notificationId) {
                var def = $q.defer();

                $http.delete(API.address + "/alertsapi/" + notificationId)
                        .success(function (data) {
                            def.resolve(data);

                        })
                        .error(function () {
                            def.reject("Failed to get nearby");
                        });

                return def.promise;
            };

            return {
                deleteNotification: deleteNotification,
                loadNotificationsTrigger: loadNotificationsTrigger,
                openNotifications: openNotifications,
                followNotification: followNotification,
                cleanNotifications: cleanNotifications,
                getIdleNotifs: function () {
                    console.log('Getting notifications while idle after' + $rootScope.lastNotification);
                    getNotificationsAfter().then(function (data) {

                    },
                            function (data) {

                            });
                },
                verifyActions: verifyActions,
                getNotificationsAfter: getNotificationsAfter,
                getTrip: getTrip,
            };
        })