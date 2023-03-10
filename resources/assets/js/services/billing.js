angular.module('besafe')
        
        .service('Billing',['$q', '$http', function ($q, $http) {

            var payCreditCard = function (data,platform) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/billing/pay_cc/'+platform,
                        data: data, // pass in data as strings
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to payCreditCard");
                        });

                return def.promise;
                /**/

            }
            var payInBank = function (data) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/billing/pay_in_bank/Local',
                        data: data, // pass in data as strings
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to payInBank");
                        });

                return def.promise;
                /**/

            }
            var completePaidOrder = function (data) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/billing/complete_paid/Food',
                        data: data, // pass in data as strings
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to completePaidOrder");
                        });

                return def.promise;
                /**/

            }
            var retryPayment = function (payment) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/billing/retry/'+payment,
                        
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to retryPayment");
                        });

                return def.promise;
                /**/

            }
            
            var addTransactionCosts = function (payment) {
                var def = $q.defer();
                
                $http({
                        method: 'POST',
                        url: '/api/billing/add_transaction_costs/'+payment,
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to addTransactionCosts");
                        });
                return def.promise;
            }

            var payDebit = function (data,platform) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/billing/pay_debit/'+platform,
                        data: data, // pass in data as strings
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to payDebit");
                        });

                return def.promise;
                /**/

            }
            var payCash = function (data,platform) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/billing/pay_cash/'+platform,
                        data: data, // pass in data as strings
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to payCash");
                        });
                return def.promise;
                /**/
            }
            var payOnDelivery = function (data,platform) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/billing/pay_ondelivery/'+platform,
                        data: data, // pass in data as strings
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to payOnDelivery");
                        });
                return def.promise;
                /**/
            }
            var getBanks = function (where) {
                var url ="/api/payu/banks";
                var def = $q.defer();
                $http({
                        method: 'GET',
                        url: url,
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to getBanks");
                        });
                return def.promise;
            }
            return {
                payCreditCard: payCreditCard,
                payInBank:payInBank,
                completePaidOrder: completePaidOrder,
                retryPayment:retryPayment,
                payOnDelivery:payOnDelivery,
                addTransactionCosts:addTransactionCosts,
                payDebit: payDebit,
                payCash:payCash,
                getBanks:getBanks
            };
        }])