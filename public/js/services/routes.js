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

            var updateRouteStops = function (route_id,stop_id) {
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

            return {
                getRoutes:getRoutes,
                sendStopToNewRoute:sendStopToNewRoute,
                buildRoute:buildRoute,
                updateRouteStops:updateRouteStops
            };
        })