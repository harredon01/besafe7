angular.module('besafe')

        .service('Booking', ['$q', '$http', function ($q, $http) {

                var getBookingsObject = function (objectB) {
                    let url = '/api/bookings';
                    var def = $q.defer();
                    $http({
                        method: 'get',
                        url: url,
                        params: objectB
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                                def.reject("Failed to getBookingsObject");
                            });

                    return def.promise;
                    /**/

                }
                var getBooking = function (objectB) {
                    let url = '/api/bookings/' + objectB;
                    var def = $q.defer();
                    $http({
                        method: 'get',
                        url: url
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                                def.reject("Failed to getBooking");
                            });

                    return def.promise;
                    /**/
                }
                var getObjectsWithBookingUser = function () {
                    let url = '/bookings/user';
                    var def = $q.defer();
                    $http({
                        method: 'get',
                        url: url
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                                def.reject("Failed to getObjectsWithBookingUser");
                            });
                    return def.promise;
                    /**/
                }
                var checkExistingBooking = function (booking_id) {
                    let url = '/api/bookings/' + booking_id + "/check";
                    var def = $q.defer();
                    $http({
                        method: 'get',
                        url: url
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                                def.reject("Failed to checkExistingBooking");
                            });

                    return def.promise;
                    /**/
                }
                var addBookingObject = function (data) {
                    var def = $q.defer();
                    $http({
                        method: 'POST',
                        url: '/api/bookings',
                        data: data, // pass in data as strings
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                                def.reject("Failed to addBookingObject");
                            });
                    return def.promise;
                    /**/
                }

                var editBookingObject = function (data) {
                    var def = $q.defer();
                    $http({
                        method: 'POST',
                        url: '/api/bookings/edit',
                        data: data, // pass in data as strings
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                                def.reject("Failed to editBookingObject");
                            });
                    return def.promise;
                    /**/
                }
                var immediateBookingObject = function (data) {
                    var def = $q.defer();
                    $http({
                        method: 'POST',
                        url: '/api/bookings/now',
                        data: data, // pass in data as strings
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                                def.reject("Failed to immediateBookingObject");
                            });
                    return def.promise;
                    /**/
                }
                var getAvailabilitiesObject = function (data) {
                    var def = $q.defer();
                    $http({
                        method: 'GET',
                        url: "/api/availabilities",
                        params: data, // pass in data as strings
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                                def.reject("Failed to getAvailabilitiesObject");
                            });
                    return def.promise;
                    /**/
                }
                var changeStatusBookingObject = function (data) {
                    var def = $q.defer();
                    $http({
                        method: 'POST',
                        url: "/api/bookings/status",
                        data: data, // pass in data as strings
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                                def.reject("Failed to changeStatusBookingObject");
                            });
                    return def.promise;
                    /**/
                }
                var deleteBookingObject = function (objectId) {
                    var def = $q.defer();
                    $http({
                        method: 'DELETE',
                        url: "/api/bookings/" + objectId
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                                def.reject("Failed to deleteBookingObject");
                            });
                    return def.promise;
                    /**/
                }
                var getMonthName = function (month) {
                    if (month == 0) {
                        return "Enero"
                    }
                    if (month == 1) {
                        return "Febrero"
                    }
                    if (month == 2) {
                        return "Marzo"
                    }
                    if (month == 3) {
                        return "Abril"
                    }
                    if (month == 4) {
                        return "Mayo"
                    }
                    if (month == 5) {
                        return "Junio"
                    }
                    if (month == 6) {
                        return "Julio"
                    }
                    if (month == 7) {
                        return "Agosto"
                    }
                    if (month == 8) {
                        return "Septiembre"
                    }
                    if (month == 9) {
                        return "Octubre"
                    }
                    if (month == 10) {
                        return "Noviembre"
                    }
                    if (month == 11) {
                        return "Diciembre"
                    }
                }

                return {
                    getBookingsObject: getBookingsObject,
                    getBooking: getBooking,
                    getObjectsWithBookingUser: getObjectsWithBookingUser,
                    checkExistingBooking: checkExistingBooking,
                    addBookingObject: addBookingObject,
                    editBookingObject: editBookingObject,
                    immediateBookingObject: immediateBookingObject,
                    getAvailabilitiesObject: getAvailabilitiesObject,
                    changeStatusBookingObject: changeStatusBookingObject,
                    deleteBookingObject:deleteBookingObject,
                    getMonthName:getMonthName
                };
            }])