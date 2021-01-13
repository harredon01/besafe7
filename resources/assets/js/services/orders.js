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
                        .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
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
                        .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to getStoreExport");
                        });
                return def.promise;
            }
            var fullfillOrder = function (data) {
                let url = '/api/items/status' ;
                var def = $q.defer();
                console.log("fullfillOrder",data);
                $http({
                    method: 'POST',
                    url: url,
                    data:data
                })
                        .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to fullfillOrder");
                        });
                return def.promise;
            }
            var approveOrder = function (order_id) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/orders/'+order_id+"/approve",
                        data: {}, // pass in data as strings
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
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
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to updateOrderStatus");
                        });
                return def.promise;
                /**/
            }
            var setShippingAddress = function (data) {
                console.log("Setting order address: ",data)
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/orders/shipping',
                        data: data // pass in data as strings
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to setShippingAddress");
                        });
                return def.promise;
                /**/
            }
            var setPlatformShippingCondition = function (order,platform) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/orders/platform/shipping/'+order,
                        data: platform, // pass in data as strings
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to setPlatformShippingCondition");
                        });
                return def.promise;
                /**/
            }
            var getPlatformShippingPrice = function (order,platform) {
                var def = $q.defer();
                $http({
                        method: 'GET',
                        url: '/api/orders/platform/shipping/'+order,
                        params: platform, // pass in data as strings
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to updateOrderStatus");
                        });
                return def.promise;
                /**/
            }
            var prepareOrder = function (data,platform) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/orders/prepare/'+platform,
                        data: data, // pass in data as strings
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to prepareOrder");
                        });
                return def.promise;
                /**/
            }
            var setDiscounts = function (order,platform) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/orders/discounts/'+platform+"/"+order,
                        data: {}, // pass in data as strings
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to setDiscounts");
                        });
                return def.promise;
                /**/
            }
            var setCoupon = function (coupon) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/orders/coupon',
                        data: {"coupon":coupon}, // pass in data as strings
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to setCoupon");
                        });
                return def.promise;
                /**/
            }
            var checkOrder = function (order,data) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/orders/check/'+order,
                        data: data, // pass in data as strings
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to checkOrder");
                        });
                return def.promise;
                /**/
            }
            var getOrder = function () {
                var def = $q.defer();
                $http({
                        method: 'GET',
                        url: '/api/orders/active',
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to getOrder");
                        });
                return def.promise;
                /**/
            }

            return {
                getOrders:getOrders,
                getOrder:getOrder,
                checkOrder:checkOrder,
                approveOrder:approveOrder,
                fullfillOrder:fullfillOrder,
                setShippingAddress:setShippingAddress,
                prepareOrder:prepareOrder,
                setPlatformShippingCondition:setPlatformShippingCondition,
                getPlatformShippingPrice:getPlatformShippingPrice,
                setDiscounts:setDiscounts,
                setCoupon:setCoupon,
                getStoreExport:getStoreExport,
                updateOrderStatus:updateOrderStatus
            };
        }])