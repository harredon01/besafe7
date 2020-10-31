angular.module('besafe')
        
        .service('Payu',['$q', '$http', function ($q, $http) {

            var setShippingAddress = function (address_id) {
                var def = $q.defer();
                $http({
                    method: 'POST',
                    url: '/checkout/shippingAddress/' + address_id
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
            var payCreditCard = function (data) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/pay/pay_cc',
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
            var getBanks = function () {
                var def = $q.defer();
                $http({
                        method: 'GET',
                        url: '/pay/banks',
                        
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
            var payDebitCard = function (data) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/pay/pay_debit',
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
            var payCash = function (data) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/pay/pay_cash',
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
            var setShippingCondition = function (condition_id) {
                var def = $q.defer();
                $http({
                    method: 'POST',
                    url: '/checkout/shippingCondition/' + condition_id
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
            var setBillingAddress = function (address_id) {
                var def = $q.defer();
                $http({
                    method: 'POST',
                    url: '/checkout/billingAddress/' + address_id
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
            var setCoupon = function (coupon) {
                var def = $q.defer();
                $http({
                    method: 'POST',
                    url: '/checkout/coupon/' + coupon
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
                setShippingAddress: setShippingAddress,
                payCreditCard:payCreditCard,
                setBillingAddress: setBillingAddress,
                setCoupon: setCoupon,
                payDebitCard:payDebitCard,
                payCash:payCash,
                getBanks:getBanks,
                setShippingCondition:setShippingCondition,
                getShippingConditions:getShippingConditions
            };
        }])