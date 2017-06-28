angular.module('besafe')
        
        .service('Users', function ($q, $http) {

            var setAsBillingAddress = function (address_id) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/user/billingAddress/'+address_id
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to get nearby");
                        });

                return def.promise;
                /**/
            }
            var getAddresses = function () {
                var def = $q.defer();
                $http({
                    method: 'get',
                    url: '/api/addresses' 
                })
                        .success(function (data) {
                            // console.log(data);
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to get nearby");
                        });

                return def.promise;
                /**/

            }
            var saveAddress = function (data) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/addresses',
                        data: data, // pass in data as strings
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'}  // set the headers so angular passing info as form data (not request payload)
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to get nearby");
                        });

                return def.promise;
                /**/

            }
            var saveUser = function (data) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/user',
                        data: data, // pass in data as strings
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'}  // set the headers so angular passing info as form data (not request payload)
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to create user");
                        });

                return def.promise;
                /**/

            }
            var deleteAddress = function (address) {
                var def = $q.defer();
                $http({
                    method: 'DELETE',
                    url: '/api/addresses/' + address
                })
                        .success(function (data) {
                            console.log(data);
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to Delete Address");
                        });

                return def.promise;
                /**/

            }
            var getContacts = function (text) {
                var where = "";
                if(text.length>0){
                    where = "?name=\*"+text+"\*";
                }
                var def = $q.defer();
                $http({
                    method: 'get',
                    url: '/api/contacts' +where
                })
                        .success(function (data) {
                            // console.log(data);
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to get nearby");
                        });

                return def.promise;
                /**/

            }
            return {
                setAsBillingAddress: setAsBillingAddress,
                getAddresses:getAddresses,
                saveAddress:saveAddress,
                deleteAddress:deleteAddress,
                saveUser:saveUser,
                getContacts:getContacts
            };
        })