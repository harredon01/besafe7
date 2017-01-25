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
                    url: '/user/addresses' 
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
                        url: '/user/editAddress',
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
            var deleteAddress = function (address) {
                var def = $q.defer();
                $http({
                    method: 'DELETE',
                    url: '/user/addresses/' + address
                })
                        .success(function (data) {
                            // console.log(data);
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to Delete Address");
                        });

                return def.promise;
                /**/

            }
            var getShippingConditions = function (address_id) {
                var def = $q.defer();
                $http({
                    method: 'GET',
                    url: '/checkout/shippingConditions/' + address_id
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
                deleteAddress:deleteAddress
            };
        })