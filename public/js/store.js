/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


var token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXUyJ9.eyJzdWIiOiI2IiwiaXNzIjoiaHR0cDpcL1wvd3d3Lmhvb3ZlcnQuY29tXC9hdXRoQXBpXC9sb2dpbiIsImlhdCI6IjE0MzcyNDE2NDUiLCJleHAiOiIxNDM3MjQ1MjQ1IiwibmJmIjoiMTQzNzI0MTY0NSIsImp0aSI6IjJlNWExMGVlNDQyMWNmZDMyOWJiMWJkZDEzN2M3NTRkIn0.NGFkMjJhMmQzMDUxODgxM2RlMTdkYTk3NmMwY2JmNDhiOWM1MDFlZGEzODkwM2JkZTdmZTUwOTNhMzhjYzUwOQ";
function login(email, password) {
    $.ajax({
        url: "authapi/login",
        type: "POST",
        data: {password: password, email: email},
        dataType: "json",
        success: function (data) {
            token = data.data.token;
        }
    });

}
function addContact(id) {
    $.ajax({
        url: "userapi/add/" + id,
        type: "POST",
        dataType: "json",
        headers: {
            "Authorization": "Bearer " + token
        },
    });
}
function deleteContact(id) {
    $.ajax({
        url: "userapi/contacts/" + id,
        type: "DELETE",
        dataType: "json",
        headers: {
            "Authorization": "Bearer " + token
        },
    });
}
function validateCode(code) {
    $.ajax({
        url: "authapi/validate_codes",
        type: "POST",
        dataType: "json",
        headers: {
            "Authorization": "Bearer " + token
        },
        data: {code: code}
    });
}
function getDacity() {
    $.ajax({
        url: '/cities/from',
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json",
        data: {name: "bogota", latitude: "4.653331", longitude: "-74.050203"}
    });
}

function moveLocations() {
    $.ajax({
        url: "/locations/moveold",
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json"
    });
}
function getObjectRest(url) {
    $.ajax({
        url: "/" + url,
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json"
    });
}
function getReportsServer(page, perpage) {
    $.ajax({
        url: '/merchants/reports/user',
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json",
        data: {page: page, per_page: perpage}
    });
}
function getReports(page, perpage, orderby, orderdir) {
    $.ajax({
        url: "/merchants/reports",
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json",
        data: {page: page, per_page: perpage, order_by: orderby, order_dir: orderdir}
    });
}
function approveReport(report_id) {
    $.ajax({
        url: "/merchants/reports/approve/" + report_id,
        type: "POST",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json"
    });
}
function deleteReport(report_id) {
    $.ajax({
        url: "/merchants/reports/" + report_id,
        type: "DELETE",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json"
    });
}
function getReport(report_id) {
    $.ajax({
        url: "/merchants/reports/" + report_id,
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json"
    });
}
function testPayu() {
    $.ajax({
        url: "/cart/test",
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json"
    });
}
function countContacts() {
    $.ajax({
        url: "/userapi/contacts_count",
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json"
    });
}
function getTrip(user_id, trip) {
    $.ajax({
        url: "/locations/user/trip",
        type: "POST",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json",
        data: {user_id: user_id, trip: trip},
    });
}
function getMedical(password) {
    $.ajax({
        url: "/authapi/verify_medical",
        type: "POST",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json",
        data: {password: password},
    });
}
function getMedicalEx(user_id, code) {
    $.ajax({
        url: "/authapi/unlock",
        type: "POST",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json",
        data: {user_id: user_id, code: code},
    });
}
function getNotifications(page, per_page) {
    $.ajax({
        url: "/alertsapi",
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json",
        data: {page: page, per_page: per_page},
    });
}
function getLocationsTrip(user_id, trip) {
    $.ajax({
        url: "/locations/user/trip",
        type: "POST",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json",
        data: {user_id: user_id, trip: trip},
    });
}
function getGroup(group) {
    $.ajax({
        url: "/groupsApi/" + group,
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json",
    });
}
function getGroups() {
    $.ajax({
        url: "/userapi/groups",
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json",
    });
}
function getContacts() {
    $.ajax({
        url: "/userapi/contacts",
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json",
    });
}
function checkServerContacts(total) {
    $.ajax({
        url: "/userapi/contacts_count",
        type: "POST",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json",
        data: {
            total: total
        },
    });
}
function checkServerGroups(total) {
    $.ajax({
        url: "/userapi/groups_count",
        type: "POST",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json",
        data: {
            total: total
        },
    });
}
function getContact(contact) {
    $.ajax({
        url: "/userapi/" + contact,
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json",
    });
}
function logout() {
    $.ajax({
        url: "/authapi/logout",
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json"
    });
}
function postContacts() {
    var contacts = [113, 114, 116];
    $.ajax({
        url: "/userapi/contacts",
        type: "POST",
        headers: {
            "authorization": "Bearer " + token
        },
        data: {
            contacts: contacts
        },
        dataType: "json",
        success: function (data) {
        }
    });

}
function postLocation() {
    $.ajax({
        url: "locations/user",
        type: "POST",
        headers: {
            "authorization": "Bearer " + token
        },
        data: {
            "lat": 4.655324,
            "long": -74.050259,
            "status": "regular",
            "location": {
                "coords": {
                    "latitude": 4.655324,
                    "longitude": -74.050259
                }
            },
            "message": "Hello"
        },
        dataType: "json",
        success: function (data) {
        }
    });

}

function register(firstName, lastName, cellphone, gender, docType, docNum, email, password) {
    $.ajax({
        url: "authapi/register",
        type: "POST",
        data: {
            firstName: firstName,
            lastName: lastName,
            cellphone: cellphone,
            docType: docType,
            gender: gender,
            docNum: docNum,
            email: email,
            password: password,
            password_confirmation: password
        },
        dataType: "json",
        success: function (data) {
            token = data.token;
        }
    });
}
function getProfile() {
    $.ajax({
        url: "userapi",
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json"
    });
}


function getVehicles() {
    $.ajax({
        url: "vehiclesapi/user",
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json"
    });
}
function getCargos() {
    $.ajax({
        url: "operationsapi/cargos",
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json"
    });
}
function getVehicleRoutes(vehicle_id, page, per_page, order_by, order_dir) {
    $.ajax({
        url: "vehiclesapi/routes",
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        data: {
            vehicle_id: vehicle_id,
            page: page,
            per_page: per_page,
            order_by: order_by,
            order_dir: order_dir,
        },
        dataType: "json"
    });
}
function postPushMessage(text, type, recipient_id) {
    $.ajax({
        url: "/messages/send",
        type: "POST",
        headers: {
            "Authorization": "Bearer " + token
        },
        data: {
            message: text,
            type: type,
            messageable_id: recipient_id
        },
        dataType: "json"
    });
}
function editProfile(firstName, lastName, cellphone, gender, docType, docNum, email) {
    $.ajax({
        url: "userapi",
        type: "POST",
        headers: {
            "Authorization": "Bearer " + token
        },
        data: {
            firstName: firstName,
            lastName: lastName,
            cellphone: cellphone,
            docType: docType,
            gender: gender,
            docNum: docNum,
            email: email
        },
        dataType: "json"
    });
}
function addOrUpdateAddress(firstName, lastName, address, city_id, region_id, country_id, lat, long, address_id) {
    $.ajax({
        url: "address",
        type: "POST",
        headers: {
            "Authorization": "Bearer " + token
        },
        data: {
            firstName: firstName,
            lastName: lastName,
            address: address,
            city_id: city_id,
            region_id: region_id,
            country_id: country_id,
            lat: lat,
            long: long,
            address_id: address_id
        },
        dataType: "json"
    });
}
function deleteAddress(address_id) {
    $.ajax({
        url: "address/" + address_id,
        type: "DELETE",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json"
    });
}

function changePassword(password, cpassword) {
    $.ajax({
        url: "userapi/change_password",
        type: "POST",
        headers: {
            "Authorization": "Bearer " + token
        },
        data: {
            password: password,
            password_confirmation: cpassword
        },
        dataType: "json"
    });
}

function getNearbyMerchants(lat, long, radius) {
    $.ajax({
        url: "merchants/nearby",
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json",
        data: {
            lat: lat,
            long: long,
            radius: radius
        }
    });
}
function searchMerchants(name) {
    $.ajax({
        url: "merchants/search",
        type: "POST",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json",
        data: {
            name: name
        }
    });
}
function getMerchant(id) {
    $.ajax({
        url: "merchants/" + id,
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json"
    });
}
function getPaymentMethodsMerchant(id) {
    $.ajax({
        url: "merchants/payment_methods/" + id,
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json"
    });
}
function getCart() {
    $.ajax({
        url: "ordersapi/cart",
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json"
    });
}
function addToCart(product_id, quantity) {
    $.ajax({
        url: "ordersapi/add_item",
        type: "POST",
        headers: {
            "Authorization": "Bearer " + token
        },
        data: {
            product_id: product_id,
            quantity: quantity,
        },
        dataType: "json"
    });
}
function updateCartItem(item_id, quantity) {
    $.ajax({
        url: "ordersapi/update_item",
        type: "POST",
        headers: {
            "Authorization": "Bearer " + token
        },
        data: {
            item_id: item_id,
            quantity: quantity,
        },
        dataType: "json"
    });
}
function clearCart() {
    $.ajax({
        url: "ordersapi/clear",
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json"
    });
}
function setShippingAddress(address_id) {
    $.ajax({
        url: "ordersapi/shipping",
        type: "POST",
        headers: {
            "Authorization": "Bearer " + token
        },
        data: {
            address_id: address_id,
        },
        dataType: "json"
    });
}

function setOrderDetails(payment_method_id, comments, cash_for_change) {
    $.ajax({
        url: "ordersapi/set_details",
        type: "POST",
        headers: {
            "Authorization": "Bearer " + token
        },
        data: {
            payment_method_id: payment_method_id,
            comments: comments,
            cash_for_change: cash_for_change
        },
        dataType: "json"
    });
}
function importTest() {
    $.ajax({
        url: "/merchants/import",
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json"
    });
}
function importUpdate() {
    $.ajax({
        url: "/merchants/import_update",
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json"
    });
}
function exportTest() {
    $.ajax({
        url: "/merchants/export",
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json"
    });
}
function exportTest() {
    $.ajax({
        url: "/merchants/export_orders",
        type: "GET",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json"
    });
}


