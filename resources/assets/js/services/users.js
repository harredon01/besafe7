angular.module('besafe')
        
        .service('Users',['$q', '$http', function ($q, $http) {

            var setAsBillingAddress = function (address_id) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/user/billingAddress/'+address_id
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
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
                        .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
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
                        data: data, // pass in data as strings  // set the headers so angular passing info as form data (not request payload)
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
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
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to create user");
                        });

                return def.promise;
                /**/

            }
            var updatePassword = function (data) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/user/change_password',
                        data: data, // pass in data as strings
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to updatePassword");
                        });

                return def.promise;
                /**/

            }
            var getUser = function () {
                var def = $q.defer();
                $http({
                        method: 'GET',
                        url: '/api/user',
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to get user");
                        });
                return def.promise;
            }
            var deleteAddress = function (address) {
                var def = $q.defer();
                $http({
                    method: 'DELETE',
                    url: '/api/addresses/' + address
                })
                        .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
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
                        .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
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
                updatePassword:updatePassword,
                saveUser:saveUser,
                getContacts:getContacts,
                getUser:getUser
            };
        }])