angular.module('besafe')
        .service('LocationService', function ($q, $location, $http, $rootScope,API) {

            var locations = function ( where ) {
                console.log($location.path());
                var def = $q.defer();
                $http({
                    method: "get",
                    url: "/locationsext"+where
                })
                        .success(function (data) {
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to get nearby");
                        });

                return def.promise;
            };
            var getCitiesRegion = function (regionId) {

                var def = $q.defer();

                $http({
                    method: "get",
                    url: API.address + "/cities?limit=50&region_id=" + regionId,
                })
                        .success(function (data) {
                            // console.log(data);
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to get nearby");
                        });

                return def.promise;
            };
            var getRegionFromCity = function (city) {
                var def = $q.defer();
                $http.post(API.address + "/cities/from", city
                        )
                        .success(function (data) {
                            // console.log(data);
                            def.resolve(data);
                        })
                        .error(function (data) {
                            def.reject(data);
                        });

                return def.promise;
            };
            var getRegionsCountry = function (countryID) {

                var def = $q.defer();

                $http({
                    method: "get",
                    url: API.address + "/regions?limit=40&country_id=" + countryID,
                })
                        .success(function (data) {
                            // console.log(data);
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to get nearby");
                        });

                return def.promise;
            };
            var getCountry = function (id) {
                var def = $q.defer();
                $http({
                    method: "get",
                    url: API.address + "/countries?id=" + id,
                })
                        .success(function (data) {
                            // console.log(data);
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to get nearby");
                        });

                return def.promise;
            };
            var getCountries = function (where) {
                var def = $q.defer();
                $http({
                    method: "get",
                    url: API.address + "/countries?limit=50&order_by=name,asc" + where,
                })
                        .success(function (data) {
                            // console.log(data);
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to get nearby");
                        });

                return def.promise;
            };

            return {
                locations: locations,
                getCitiesRegion:getCitiesRegion,
                getRegionsCountry:getRegionsCountry,
                getCountries:getCountries
            };
        })