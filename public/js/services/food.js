angular.module('besafe')
        
        .service('Food', function ($q, $http) {

            var getMenu = function (where) {
                let url = '/api/food/menu' ;
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
                            def.reject("Failed to get getArticles");
                        });
                return def.promise;
                /**/
            }
            var getZones = function (where) {
                let url = '/api/food/zones' ;
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
                            def.reject("Failed to get getArticles");
                        });
                return def.promise;
                /**/
            }
            var getMessages = function (where) {
                let url = '/api/food/messages' ;
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
                            def.reject("Failed to get getArticles");
                        });
                return def.promise;
                /**/
            }
            var getLargestAddresses = function () {
                let url = '/api/food/largest_addresses' ;
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
                            def.reject("Failed to get getLargestAddresses");
                        });
                return def.promise;
            }
            var buildScenarioRouteId = function (route_id) {
                let url = '/api/food/build_route_id/'+route_id ;
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
                            def.reject("Failed to buildScenarioRouteId");
                        });
                return def.promise;
            }
            var buildScenarioPositive = function (scenario) {
                let url = '/api/food/build_scenario_positive/'+scenario ;
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
                            def.reject("Failed to buildScenarioPositive");
                        });
                return def.promise;
            }
            var buildCompleteScenario = function (scenario) {
                let url = '/api/food/build_complete_scenario/'+scenario ;
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
                            def.reject("Failed to buildCompleteScenario");
                        });
                return def.promise;
            }
            var getScenarioStructure = function (scenario,type) {
                let url = '/api/food/get_scenario_structure/'+scenario+"/"+type ;
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
                            def.reject("Failed to buildCompleteScenario");
                        });
                return def.promise;
            }
            var getSummaryShipping = function (type) {
                let url = '/api/food/summary/'+type ;
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
                            def.reject("Failed to buildCompleteScenario");
                        });
                return def.promise;
            }
            var getScenarioOrganizationStructure = function (type) {
                let url = '/api/food/route_organize/'+type ;
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
                            def.reject("Failed to getScenarioOrganizationStructure");
                        });
                return def.promise;
            }
            var regenerateScenarios = function () {
                let url = '/api/food/regenerate_scenarios';
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
                            def.reject("Failed to regenerateScenarios");
                        });
                return def.promise;
            }
            var regenerateDeliveries = function () {
                let url = '/api/food/regenerate_deliveries';
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
                            def.reject("Failed to regenerateDeliveries");
                        });
                return def.promise;
            }
            var updateMenuItem = function (item) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/food/menu/'+item.id,
                        data: item, // pass in data as strings
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to updateMenuItem");
                        });
                return def.promise;
                /**/
            }
            var updateZoneItem = function (item) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/food/zone/'+item.id,
                        data: item, // pass in data as strings
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to updateZoneItem");
                        });
                return def.promise;
                /**/
            }
            var updateMessageItem = function (item) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/food/messages/'+item.id,
                        data: item, // pass in data as strings
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to updateMessageItem");
                        });
                return def.promise;
                /**/
            }
            var deleteMessageItem = function (item) {
                var def = $q.defer();
                $http({
                        method: 'DELETE',
                        url: '/api/food/messages/'+item.id
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to deleteMessageItem");
                        });
                return def.promise;
                /**/
            }
            var deleteZoneItem = function (item) {
                var def = $q.defer();
                $http({
                        method: 'DELETE',
                        url: '/api/food/zone/'+item.id
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to deleteZoneItem");
                        });
                return def.promise;
                /**/
            }
            var deleteMenuItem = function (item) {
                var def = $q.defer();
                $http({
                        method: 'DELETE',
                        url: '/api/food/menu/'+item.id
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to deleteMenuItem");
                        });
                return def.promise;
                /**/
            }

            return {
                getMenu:getMenu,
                getZones:getZones,
                getMessages:getMessages,
                regenerateDeliveries:regenerateDeliveries,
                getScenarioStructure:getScenarioStructure,
                getSummaryShipping:getSummaryShipping,
                buildScenarioRouteId:buildScenarioRouteId,
                buildCompleteScenario:buildCompleteScenario,
                regenerateScenarios:regenerateScenarios,
                buildScenarioPositive:buildScenarioPositive,
                updateZoneItem:updateZoneItem,
                updateMenuItem:updateMenuItem,
                getScenarioOrganizationStructure:getScenarioOrganizationStructure, 
                updateMessageItem:updateMessageItem,
                deleteZoneItem:deleteZoneItem,
                deleteMenuItem:deleteMenuItem,
                deleteMessageItem:deleteMessageItem,
                getLargestAddresses:getLargestAddresses
            };
        })