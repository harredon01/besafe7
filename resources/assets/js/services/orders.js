angular.module('besafe')
        
        .service('Orders',['$q', '$http', function ($q, $http) {

            var getOrders = function (where) {
                let url = '/api/orders' ;
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
                            def.reject("Failed to get orders");
                        });

                return def.promise;
                /**/

            }
            var getStoreExport = function (from,to) {
                let url = '/api/store/reports' ;
                var def = $q.defer();
                console.log("getStoreExport",from,to);
                $http({
                    method: 'POST',
                    url: url,
                    data:{from:from,to:to}
                })
                        .success(function (data) {
                            // console.log(data);
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to getStoreExport");
                        });

                return def.promise;
                /**/

            }
            var approveOrder = function (order_id) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/orders/'+order_id+"/approve",
                        data: {}, // pass in data as strings
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to approveOrder");
                        });
                return def.promise;
                /**/
            }

            var updateOrderStatus = function (status,order_id) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/orders/'+order_id+"/build",
                        data: {"status":status}, // pass in data as strings
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to updateOrderStatus");
                        });
                return def.promise;
                /**/
            }

            return {
                getOrders:getOrders,
                approveOrder:approveOrder,
                getStoreExport:getStoreExport,
                updateOrderStatus:updateOrderStatus
            };
        }])