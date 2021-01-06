angular.module('besafe')

        .controller('BookingCtrl', ['$scope', '$rootScope', 'getParams', 'Booking', 'Modals', 'Cart','$mdDialog', function ($scope, $rootScope, getParams, Booking, Modals, Cart,$mdDialog) {
                $scope.availableDays = [];
                $scope.availableDates = [];
                $scope.selectedSpots = [];
                $scope.questions = [];
                $scope.meetingType;
                $scope.variant;
                $scope.availabilities = [];
                $scope.appointmentOptions = [];
                $scope.months = [];
                $scope.newVisit = true;
                $scope.dateSelected = false;
                $scope.virtualMeeting = 'physical';
                $scope.timeSelected = false;
                $scope.availabilitiesDate = [];
                $scope.weekday = [];
                $scope.weekday2 = [];
                $scope.typeObj;
                $scope.bookingObj = null;
                $scope.requiresAuth;
                $scope.dayName;
                $scope.atributesCont;
                $scope.product_variant_id = null;
                $scope.item_id = null;
                $scope.quantity = null;
                $scope.location = null;
                $scope.objectId;
                $scope.objectName;
                $scope.objectDescription;
                $scope.objectIcon;
                $scope.startDate;
                $scope.endDate;
                $scope.startDateS;
                $scope.expectedPrice;
                $scope.activeBooking;
                $scope.amount = "1";
                $scope.submitted = false;
                $scope.constructor = function () {

                    $scope.atributesCont = {};
                    $scope.weekday = new Array(7);
                    $scope.weekday[0] = "sunday";
                    $scope.weekday[1] = "monday";
                    $scope.weekday[2] = "tuesday";
                    $scope.weekday[3] = "wednesday";
                    $scope.weekday[4] = "thursday";
                    $scope.weekday[5] = "friday";
                    $scope.weekday[6] = "saturday";
                    $scope.weekday2 = new Array(7);
                    $scope.weekday2[0] = "Domingo";
                    $scope.weekday2[1] = "Lunes";
                    $scope.weekday2[2] = "Martes";
                    $scope.weekday2[3] = "Miercoles";
                    $scope.weekday2[4] = "Jueves";
                    $scope.weekday2[5] = "Viernes";
                    $scope.weekday2[6] = "Sabado";
                    $scope.questions = []

                    console.log("Get availableDates", $scope.availableDates);
                }
                $scope.ngOnInit = function () {
                    $scope.loadData();
                }
                $scope.cancel = function () {
                    $mdDialog.cancel();
                };
                $scope.keytab = function (event, maxlength) {
                    let nextInput = event.srcElement.nextElementSibling; // get the sibling element
                    console.log('nextInput', nextInput);
                    var target = event.target || event.srcElement;
                    console.log('target', target);
                    console.log('targetvalue', target.value);
                    console.log('targettype', target.nodeType);
                    if (target.value.length < maxlength) {
                        return;
                    }
                    if (nextInput == null)  // check the maxLength from here
                        return;
                    else
                        nextInput.focus();   // focus if not null
                }
                $scope.onError = function () {
                    console.log("IMG ERROR");
                    $scope.objectIcon = "/assets/avatar/Bentley.png";
                }
                $scope.loadData = function () {
                    let paramsArrived = getParams;
                    console.log("Params arrived: ", paramsArrived);
                    if (paramsArrived) {
                        $scope.typeObj = paramsArrived.type;
                        $scope.objectId = paramsArrived.objectId;
                        $scope.objectName = paramsArrived.objectName;
                        $scope.objectDescription = paramsArrived.objectDescription;
                        $scope.objectIcon = paramsArrived.objectIcon;
                        if (paramsArrived.expectedPrice) {
                            $scope.expectedPrice = paramsArrived.expectedPrice;
                        }
                        if (paramsArrived.questions) {
                            $scope.questions = paramsArrived.questions;
                        }
                        if (paramsArrived.variant) {
                            $scope.variant = paramsArrived.variant;
                        }
                        if (paramsArrived.location) {
                            $scope.location = paramsArrived.location;
                            if($scope.location == "zoom"){
                                $scope.virtualMeeting = "virtual";
                            }
                        }
                        if (paramsArrived.product_variant_id) {
                            $scope.product_variant_id = paramsArrived.product_variant_id;
                        }
                        if (paramsArrived.item_id) {
                            $scope.item_id = paramsArrived.item_id;
                        }
                        if (paramsArrived.quantity) {
                            $scope.quantity = paramsArrived.quantity;
                        }
                        if (paramsArrived.booking) {
                            $scope.bookingObj = paramsArrived.booking;
                            $scope.dateSelected = true;
                            $scope.timeSelected = true;
                            let container = {date: $scope.bookingObj.starts_at, status: 'active'};
                            $scope.selectDate(container);
                            $scope.expectedPrice = $scope.bookingObj.price;
                            $scope.atributesCont = $scope.bookingObj.options;
                            if ($scope.atributesCont) {
                                if ($scope.atributesCont.questions) {
                                    $scope.questions = $scope.atributesCont.questions;
                                }
                            }
                            console.log("Date", $scope.bookingObj.starts_at.toISOString());
                            $scope.startDateS = $scope.bookingObj.starts_at.toISOString();
                            $scope.selectStart();
                        }
                        if (paramsArrived.availabilities) {
                            $scope.dateSelected = false;
                            $scope.availabilities = paramsArrived.availabilities;
                            console.log("Availabilities", $scope.availabilities);
                            $scope.getAvailableDates($scope.availabilities);
                            $scope.getDates();
                            console.log("Get availableDays", $scope.availableDays);
                        } else {
                            $scope.getItems();
                        }
                        console.log("expectedPrice", $scope.expectedPrice);
                    } else {
                        $scope.getItems();
                    }

                    console.log("Get availableDays", $scope.dateSelected);
                }
                $scope.setOrder = function (item) {
                    let dateBase = new Date("1970-01-01 00:00:00");
                    let date = new Date("1970-01-01 " + item.from);
                    let orderAdd = (date.getTime() - dateBase.getTime()) / 1000000;
                    console.log("Date: ", orderAdd);
                    if (item.range == 'sunday') {
                        item.order = 0;
                    }
                    if (item.range == 'monday') {
                        item.order = 100;
                    }
                    if (item.range == 'tuesday') {
                        item.order = 200;
                    }
                    if (item.range == 'wednesday') {
                        item.order = 300;
                    }
                    if (item.range == 'thursday') {
                        item.order = 400;
                    }
                    if (item.range == 'friday') {
                        item.order = 500;
                    }
                    if (item.range == 'saturday') {
                        item.order = 600;
                    }
                    item.order += orderAdd;
                    return item;
                }

                $scope.getItems = function () {
                    let availabilities = [];
                    //        Modals.showLoader();
                    let where = {"type": "Merchant", "object_id": $scope.objectId};
                    Booking.getAvailabilitiesObject(where).then(function (data) {
                        //            Modals.hideLoader();
                        console.log("after getItems", data);
                        let results = data.data;
                        for (let one in results) {
                            results[one] = $scope.setOrder(results[one]);
                            availabilities.push(results[one]);
                        }
                        availabilities.sort((a, b) => (a.order > b.order) ? 1 : -1);
                        $scope.availabilities = availabilities;
                        console.log("Availabilities", $scope.availabilities);
                        $scope.getAvailableDates($scope.availabilities);
                        $scope.getDates();
                        console.log("in get items", $scope.bookingObj);
                        if ($scope.bookingObj) {
                            let container = {date: $scope.bookingObj.starts_at, status: 'active'};
                            $scope.selectDate(container);
                        }
                        console.log("Get availableDays", $scope.availableDays);
                        console.log(JSON.stringify(data));
                    }, function (data) {
                        //            Modals.hideLoader();
                        // Unable to log in
                        Modals.showToast('BOOKING.ERROR_GET_AVAILABILITIES');
                    });
                }
                $scope.goBack = function () {
                    console.log("goBack", $scope.dateSelected)
                    if ($scope.dateSelected) {
                        $scope.dateSelected = false;
                    } else {
                        $scope.navCtrl.back();
                    }

                }
                $scope.addBookingToCart = function (booking) {
                    if ($rootScope.items) {
                        let items = $rootScope.items;
                        for (let i in items) {
                            let attrs = items[i].attributes;
                            if (attrs.type == "Booking") {
                                if (attrs.id == booking.id) {
                                    Modals.showToast("Carrito actualizado");
                                    return;
                                }
                            }
                        }
                    }
                    $scope.addBookingToCartServer(booking);
                }
                $scope.addBookingToCartServer = function (booking) {
                    let extras = {
                        "type": "Booking",
                        "id": booking.id,
                        "name": "Reserva con: " + booking.bookable.name,
                        "from":booking.starts_at
                    }
                    let item = {
                        "name": "Reserva con: " + booking.bookable.name,
                        "price": booking.price,
                        "quantity": booking.quantity,
                        "tax": 0,
                        "merchant_id": $scope.objectId,
                        "cost": 0,
                        "extras": extras
                    };
                    console.log("addBookingToCartServer",item);
                    if ($scope.product_variant_id) {
                        let container = {
                            product_variant_id: $scope.product_variant_id,
                            quantity: $scope.quantity,
                            item_id: null,
                            merchant_id: $scope.objectId,
                            "extras": extras
                        };
                        Cart.postToServer(container).then(function (data) {
                            if(data.status == "success"){
                                $rootScope.$broadcast('loadHeadCart', data.cart);
                                $rootScope.cartMessage = "Carrito actualizado";
                            } else {
                                $rootScope.cartMessage = data.message;
                            }
                        }, function (data) {
                            console.log("Error addCustomCartItem");
                        });
                    } else if ($scope.item_id) {
                        let container = {
                            quantity: $scope.quantity,
                            item_id: $scope.item_id,
                            merchant_id: $scope.objectId,
                            "extras": extras
                        };
                        Cart.updateCartItem(container).then(function (data) {
                            if(data.status == "success"){
                                $rootScope.$broadcast('loadHeadCart', data.cart);
                                $rootScope.cartMessage = "Carrito actualizado";
                            } else {
                                $rootScope.cartMessage = data.message;
                            }
                        }, function (data) {
                            console.log("Error addCustomCartItem");
                        });
                    } else {
                        Cart.addCustomCartItem(item).then(function (data) {
                            if(data.status == "success"){
                                $rootScope.$broadcast('loadHeadCart', data.cart);
                                $rootScope.cartMessage = "Carrito actualizado";
                            } else {
                                $rootScope.cartMessage = data.message;
                            }
                        }, function (data) {
                            console.log("Error addCustomCartItem");
                        });
                    }
                }
                $scope.selectRadio = function (event) {
                    $scope.virtualMeeting = event.detail.value;
                    console.log("Virtual meeting radio ", event);
                    console.log("Virtual meeting radio ", $scope.virtualMeeting);
                }
                $scope.createBooking = function () {
                    if ($scope.submitted) {
                        return true;
                    }
                    $scope.submitted = true;
                    //Modals.showLoader();
                    let startDate = new Date($scope.startDate.getTime() - $scope.startDate.getTimezoneOffset() * 60000);
                    let strDate = startDate.toISOString();
                    let endDate = new Date($scope.endDate.getTime() - $scope.endDate.getTimezoneOffset() * 60000);
                    let ndDate = endDate.toISOString();
                    let virtual = false;
                    console.log("Virtual meeting", $scope.virtualMeeting);
                    if ($scope.virtualMeeting == 'virtual') {
                        virtual = true;
                        $scope.atributesCont.virtual_provider = "zoom";
                        $scope.atributesCont.virtual_meeting = true;
                    }
                    let data = {
                        "type": $scope.typeObj,
                        "object_id": $scope.objectId,
                        "from": strDate,
                        "to": ndDate,
                        "attributes": $scope.atributesCont,
                        "virtual_meeting": virtual
                    };
                    console.log("Start", $scope.startDate);
                    console.log("data", data);
                    Booking.addBookingObject(data).then(function (resp) {
                        Modals.hideLoader();
                        console.log("addBookingObject", resp);
                        $scope.submitted = false;
                        //$scope.presentAlertConfirm(data);
                        if (resp.status == "success") {
                            if (resp.requires_auth) {
                                $scope.presentAlertConfirm($scope.requiresAuth);
                            } else {
                                $scope.addBookingToCart(resp.booking);
                            }
                        } else {
                            $scope.showAlertTranslation("BOOKING." + resp.message);
                        }
                    }, function (data) {
                        $scope.submitted = false;
                        console.log("Error addBookingObject");
                        Modals.hideLoader();
                    });
                }
                $scope.saveOrCreateBooking = function () {
                    $scope.atributesCont.questions = $scope.questions;
                    if ($scope.bookingObj) {
                        $scope.editBooking()
                    } else {
                        $scope.createBooking();
                    }
                }
                $scope.editBooking = function () {
                    //        if ($scope.submitted) {
                    //            return true;
                    //        }
                    //        $scope.submitted = true; 
                    Modals.showLoader();
                    console.log("offset", $scope.startDate.getTimezoneOffset() * 60000);
                    let startDate = new Date($scope.startDate.getTime() - $scope.startDate.getTimezoneOffset() * 60000);
                    let strDate = startDate.toISOString();
                    let endDate = new Date($scope.endDate.getTime() - $scope.endDate.getTimezoneOffset() * 60000);
                    let ndDate = endDate.toISOString();
                    let virtual = false;
                    console.log("Virtual meeting", $scope.virtualMeeting);
                    if ($scope.virtualMeeting == 'virtual') {
                        virtual = true;
                        $scope.atributesCont.virtual_provider = "zoom";
                        $scope.atributesCont.virtual_meeting = true;
                    }
                    let data = {
                        "booking_id": $scope.bookingObj.id,
                        "type": $scope.typeObj,
                        "object_id": $scope.objectId,
                        "from": strDate,
                        "to": ndDate,
                        "attributes": $scope.atributesCont
                    };
                    console.log("Start", $scope.startDate);
                    console.log("data", data);
                    Booking.editBookingObject(data).then(function (resp) {
                        Modals.hideLoader();
                        console.log("editBookingObject", resp);
                        $scope.submitted = false;
                        //$scope.presentAlertConfirm(data);
                        if (resp.status == "success") {
                            let container = $scope.params.getParams();
                            container.booking = new Booking(resp.booking);
                            $scope.params.setParams(container);
                            $scope.navCtrl.back();
                        } else {
                            $scope.showAlertTranslation("BOOKING." + resp.message);
                        }
                    }, function (data) {
                        console.log("Error editBookingObject");
                        Modals.hideLoader();
                    });
                }

                $scope.showAlertTranslation = function (alert) {
                    Modals.showToast(alert);
                }

                $scope.presentAlertConfirm = function (message) {
                    console.log("Present alert", message);
//        let button = {
//            text: 'Ok',
//            handler: () => {
//                console.log('Confirm Okay');
//                if (message == $scope.requiresAuth) {
//                    $scope.navCtrl.back();
//                }
//            }
//        }
//        const alert = await $scope.alertsCtrl.create({
//            message: message,
//            buttons: [
//                button
//            ]
//        });
//        await alert.present();
                }

                $scope.getAvailableDates = function (availabilities) {
                    for (let item in availabilities) {
                        let container = availabilities[item];
                        if (!$scope.checkAvailableDays(container.range)) {
                            $scope.availableDays.push(container.range);
                        }
                    }
                }

                $scope.checkAvailableDays = function (day) {
                    for (let item in $scope.availableDays) {
                        if ($scope.availableDays[item] == day) {
                            return true;
                        }
                    }
                    return false;
                }

                $scope.getDates = function () {
                    console.log("Get dates");
                    var myDate = new Date();
                    if($scope.variant &&$scope.variant.is_shippable){
                        myDate.setDate(myDate.getDate() + 1);
                    }
                    let month = myDate.getMonth();
                    let monthcont = {month: month, days: [], title: Booking.getMonthName(month)};
                    for (let i = 0; i < 61; i++) {
                        let day = myDate.getDay();
                        let container = {date: new Date(myDate.getTime()), status: "closed"};
                        if ($scope.checkAvailableDays($scope.weekday[day])) {
                            container.status = "active"
                        }

                        if (myDate.getMonth() != monthcont.month) {
                            $scope.months.push(monthcont);
                            let month = myDate.getMonth();
                            monthcont = {month: month, days: [], title: Booking.getMonthName(month)};
                        }
                        monthcont.days.push(container);
                        $scope.availableDates.push(container);
                        myDate.setDate(myDate.getDate() + 1);
                    }
                    console.log("Get dates months", $scope.months);
                }
                $scope.returnDates = function () {
                    $scope.dateSelected = false;
                }
                $scope.selectStart = function () {
                    $scope.startDate = new Date($scope.startDateS);
                    $scope.endDate = new Date($scope.startDate.getTime() + (parseInt($scope.amount) * 50) * 60000);
                    $scope.timeSelected = true;
                }
                $scope.filterAvailabilities = function (day) {
                    $scope.availabilitiesDate = [];
                    for (let item in $scope.availabilities) {
                        if ($scope.availabilities[item].range == $scope.weekday[day]) {
                            $scope.availabilitiesDate.push(($scope.availabilities[item]));
                        }
                    }
                }
                $scope.selectSlot = function (item) {
                    $scope.startDate = item.start;
                    $scope.endDate = $scope.addMinutes(item.end, -5);
                    $scope.timeSelected = true;
                }
                $scope.getBookingsDay = function (selectedDate) {
                    let strDate = selectedDate.getFullYear() + "-" + (selectedDate.getMonth() + 1) + "-" + selectedDate.getDate();
                    let params = {
                        "from": strDate,
                        "query": "day",
                        "type": $scope.typeObj,
                        "object_id": $scope.objectId,
                    };
                    Booking.getBookingsObject(params).then(function (data) {
                        console.log("getBookingsObject", data);
                        let results = data.data;
                        for (let i in results) {
                            let bookitem = new Booking(results[i]);
                            $scope.selectedSpots.push(bookitem);
                        }
                        $scope.buildSlots();
                    }, function (data) {
                        console.log("Error getBookingsObject");
                    });
                }

                $scope.selectDate = function (item) {
                    if (item.status == 'active') {
                        let selectedDate = item.date;
                        let day = selectedDate.getDay();
                        $scope.dateSelected = true;
                        $scope.timeSelected = false;
                        console.log("select date", selectedDate);
//                        Modals.showLoader();
                        $scope.dayName = $scope.weekday2[day];
                        $scope.startDate = selectedDate;
                        $scope.startDateS = selectedDate.toISOString();
                        Modals.showToast('Buscando horarios disponibles');
                        $scope.filterAvailabilities(day);
                        $scope.getBookingsDay(selectedDate);
                        console.log("Availabilities", $scope.availabilitiesDate);
                    } else {
                        Modals.showToast('BOOKING.NOT_AVAILABLE');
                    }

                }
                $scope.addMinutes = function (date, minutes) {
                    return new Date(date.getTime() + minutes * 60000);
                }
                $scope.buildSlots = function () {
                    $scope.appointmentOptions = [];
                    let appointmentLength = 30;
                    if($scope.variant &&$scope.variant.is_shippable){
                        appointmentLength = 60;
                    }
                    let current = new Date();
                    current = $scope.addMinutes(current, appointmentLength)
                    for (let item in $scope.availabilitiesDate) {
                        let container = $scope.availabilitiesDate[item];
                        let open = true;
                        console.log("Building date " + $scope.startDate.getFullYear() + "/" + ($scope.startDate.getMonth() + 1) + "/" + $scope.startDate.getDate() + " " + container.from.replace(" ", ":00 "))
                        let start = new Date($scope.startDate.getFullYear() + "/" + ($scope.startDate.getMonth() + 1) + "/" + $scope.startDate.getDate() + " " + container.from.replace(" ", ":00 "));
                        let end = new Date($scope.startDate.getFullYear() + "/" + ($scope.startDate.getMonth() + 1) + "/" + $scope.startDate.getDate() + " " + container.to.replace(" ", ":00 "));
                        while (open) {
                            let endApp = $scope.addMinutes(start, appointmentLength);
                            if (endApp <= end) {
                                let containerSlot = {start: start, end: endApp};
                                if (start > current) {
                                    if (!$scope.checkFilledDate(containerSlot)) {
                                        $scope.appointmentOptions.push(containerSlot);
                                    }
                                }
                                start = $scope.addMinutes(start, appointmentLength)
                            } else {
                                open = false;
                            }
                        }
                    }
                    if ($scope.bookingObj) {
                        console.log("Loaded booking obj", $scope.bookingObj);
                        let container = {start: $scope.bookingObj.starts_at, end: $scope.bookingObj.ends_at}
                        $scope.selectSlot(container);
                    }
                }
                $scope.checkFilledDate = function (container) {
                    for (let item in $scope.selectedSpots) {
                        let checking = $scope.selectedSpots[item];
                        console.log("Test")
                        console.log("comparing: ", container.start.getTime(), " ", container.end.getTime())
                        console.log("comparing: ", checking.starts_at.getTime(), " ", checking.ends_at.getTime())
                        console.log("difference: ", container.start.getTime() - checking.starts_at.getTime(), " ", container.end.getTime() - checking.ends_at.getTime())
                        if ((container.start.getTime() == checking.starts_at.getTime()) ||
                                (container.start.getTime() > checking.starts_at.getTime() && container.start.getTime() < checking.ends_at.getTime()) ||
                                (container.start.getTime() < checking.starts_at.getTime() && container.end.getTime() > checking.starts_at.getTime())) {
                            console.log("True");
                            return true;
                        }
                    }
                    return false;

                }
                $scope.changeDate = function () {
                    $scope.dateSelected = false;
                    $scope.timeSelected = false;
                }
                $scope.changeTime = function () {
                    $scope.timeSelected = false;
                }
                $scope.constructor();
                $scope.ngOnInit();
            }])