angular.module('besafe')
        
        .service('Routes', function ($q, $http) {

            var getRoutes = function (where) {
                let url = '/api/routes' ;
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
                            def.reject("Failed to get routes");
                        });

                return def.promise;
                /**/

            }
            var deleteRoute = function (route_id) {
                let url = '/api/routes/'+route_id ;
                var def = $q.defer();
                $http({
                    method: 'delete',
                    url: url 
                })
                        .success(function (data) {
                            // console.log(data);
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to deleteRoute");
                        });

                return def.promise;
                /**/
            }
            var deleteStop = function (stop_id) {
                let url = '/api/stops/'+stop_id ;
                var def = $q.defer();
                $http({
                    method: 'delete',
                    url: url 
                })
                        .success(function (data) {
                            // console.log(data);
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to deleteStop");
                        });

                return def.promise;
                /**/
            }
            var buildRoute = function (route_id) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/routes/'+route_id+"/build",
                        data: {}, // pass in data as strings
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to buildRoute");
                        });
                return def.promise;
                /**/
            }

            var updateRouteStop = function (route_id,stop_id) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/routes/'+route_id+"/stop/"+stop_id,
                        data: {}, // pass in data as strings
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to updateRouteStops");
                        });
                return def.promise;
                /**/
            }
            var updateRouteDelivery = function (route_id,delivery_id,stop_id) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/routes/add_delivery',
                        data: {
                            "route_id":route_id,
                            "delivery_id":delivery_id,
                            "stop_id":stop_id
                        }, // pass in data as strings
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to updateRouteDelivery");
                        });
                return def.promise;
                /**/
            }
            var sendStopToNewRoute = function (stop_id) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: "/api/routes/stop/"+stop_id,
                        data: {}, // pass in data as strings
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to sendStopToNewRoute");
                        });
                return def.promise;
                /**/
            }
            var addReturnStop = function (route_id,address_id) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: "/api/routes/"+route_id+"/return",
                        data: {address_id:address_id}, // pass in data as strings
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to sendStopToNewRoute");
                        });
                return def.promise;
                /**/
            }

            return {
                getRoutes:getRoutes,
                deleteStop:deleteStop,
                deleteRoute:deleteRoute,
                sendStopToNewRoute:sendStopToNewRoute,
                buildRoute:buildRoute,
                addReturnStop:addReturnStop,
                updateRouteStop:updateRouteStop,
                updateRouteDelivery:updateRouteDelivery
            };
        })