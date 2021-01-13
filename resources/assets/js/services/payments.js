angular.module('besafe')
        
        .service('Payments',['$q', '$http', function ($q, $http) {

            var getPayments = function (where) {
                let url = '/api/payments' ;
                if(where){
                    url = url+'?'+where;
                }
                var def = $q.defer();
                $http({
                    method: 'get',
                    url: url
                })
                        .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to get payments");
                        });

                return def.promise;
                /**/

            }
            var getPaymentsUser = function (where) {
                let url = '/api/billing/payments' ;
                if(where){
                    url = url+'?'+where;
                }
                var def = $q.defer();
                $http({
                    method: 'get',
                    url: url
                })
                        .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to get payments");
                        });

                return def.promise;
                /**/

            }
            var approvePayment = function (payment_id) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/payments/'+payment_id+"/approve",
                        data: {}, // pass in data as strings
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to approvePayment");
                        });
                return def.promise;
                /**/
            }
            var retryPayment = function (payment_id) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/billing/retry/'+payment_id,
                        data: {}, // pass in data as strings
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to retryPayment");
                        });
                return def.promise;
                /**/
            }
            var addTransactionCosts = function (payment_id) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/billing/add_transaction_costs/'+payment_id,
                        data: {}, // pass in data as strings
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to addTransactionCosts");
                        });
                return def.promise;
                /**/
            }


            return {
                getPayments:getPayments,
                getPaymentsUser:getPaymentsUser,
                retryPayment:retryPayment,
                addTransactionCosts:addTransactionCosts,
                approvePayment:approvePayment
            };
        }])