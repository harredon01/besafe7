angular.module('besafe')
        .service('LocationService', function ($q, $location, $http, $rootScope) {

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
                    url: "/cities?region_id=" + regionId,
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
            var getRegionsCountry = function (countryID) {

                var def = $q.defer();

                $http({
                    method: "get",
                    url: "/regions?country_id=" + countryID,
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
            var getCountries = function (  ) {

                var def = $q.defer();

                $http({
                    method: "get",
                    url: "/countries",
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