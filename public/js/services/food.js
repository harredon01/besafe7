angular.module('besafe')
        
        .service('Food', function ($q, $http) {

            var getArticles = function (where) {
                let url = '/api/food/articles' ;
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
            var getScenarioStructure = function (scenario) {
                let url = '/api/food/get_scenario_structure/'+scenario ;
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
            var getSummaryShipping = function () {
                let url = '/api/food/summary' ;
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
            var updateArticle = function (article) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/food/articles/'+article.id,
                        data: article, // pass in data as strings
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to updateArticle");
                        });
                return def.promise;
                /**/
            }

            return {
                getArticles:getArticles,
                regenerateDeliveries:regenerateDeliveries,
                getScenarioStructure:getScenarioStructure,
                getSummaryShipping:getSummaryShipping,
                buildScenarioRouteId:buildScenarioRouteId,
                buildCompleteScenario:buildCompleteScenario,
                regenerateScenarios:regenerateScenarios,
                buildScenarioPositive:buildScenarioPositive,
                updateArticle:updateArticle,
                getLargestAddresses:getLargestAddresses
            };
        })