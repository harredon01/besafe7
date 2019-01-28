angular.module('besafe')
        
        .service('Payments', function ($q, $http) {

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
                        .success(function (data) {
                            // console.log(data);
                            def.resolve(data);
                        })
                        .error(function () {
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
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to approvePayment");
                        });
                return def.promise;
                /**/
            }


            return {
                getPayments:getPayments,
                approvePayment:approvePayment
            };
        })