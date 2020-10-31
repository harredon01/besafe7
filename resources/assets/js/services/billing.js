angular.module('besafe')
        
        .service('Billing',['$q', '$http', function ($q, $http) {

            var createSubscriptionExisting = function (data,platform) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: 'api/subscriptions/'+platform,
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
            var createSubscription = function (data,platform) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: 'api/subscriptions/'+platform,
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
            var editSubscription = function (data,subscription,platform) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: 'api/subscriptions/'+platform+"/"+subscription,
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
            var getSubscriptions = function () {
                var def = $q.defer();
                $http({
                        method: 'GET',
                        url: 'api/subscriptions',
                        
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to get sources");
                        });

                return def.promise;
                /**/

            }
            
            var getSubscriptionsTypeObject = function (type,object) {
                var def = $q.defer();
                
                $http({
                        method: 'GET',
                        url: 'api/subscriptions/object/'+type+"/"+object,
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to getSubscriptionsTypeObject");
                        });
                return def.promise;
            }
            
            var getPlans = function (where) {
                var url ="";
                if(where){
                    url = 'api/plans?' +where;
                } else {
                    url = 'api/plans';
                }
                var def = $q.defer();
                $http({
                        method: 'GET',
                        url: url,
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to get plans");
                        });
                return def.promise;
            }
            
            var deleteSubscription = function (subscription,platform) {
                var def = $q.defer();
                $http({
                        method: 'Delete',
                        url: 'api/subscriptions/'+platform+"/"+subscription,
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
            var createSource = function (data,platform) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: 'api/sources/'+platform,
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
            var getSources = function (platform) {
                var def = $q.defer();
                $http({
                        method: 'GET',
                        url: 'api/sources/'+platform,
                        
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to get sources");
                        });

                return def.promise;
                /**/

            }
            var deleteSource = function (source,platform) {
                var def = $q.defer();
                $http({
                        method: 'Delete',
                        url: 'api/sources/'+platform+"/"+source,
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
            var setAsDefault = function (data,platform) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: 'api/sources/'+platform+"/default",
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
            var makeCharge = function (data) {
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
            return {
                deleteSource: deleteSource,
                getSources:getSources,
                createSource: createSource,
                setAsDefault:setAsDefault,
                getPlans:getPlans,
                makeCharge: makeCharge,
                deleteSubscription:deleteSubscription,
                getSubscriptions:getSubscriptions,
                editSubscription:editSubscription,
                createSubscription:createSubscription,
                createSubscriptionExisting:createSubscriptionExisting,
                getSubscriptionsTypeObject:getSubscriptionsTypeObject
            };
        }])