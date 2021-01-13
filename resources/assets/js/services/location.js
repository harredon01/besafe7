angular.module('besafe')
        .service('LocationService', ['$q', '$http', '$rootScope', function ($q, $http, $rootScope) {

                var shared = function (where) {
                    var def = $q.defer();
                    $http({
                        method: "get",
                        url: "/api/locations" + where,
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                                def.reject("Failed to get nearby");
                            });

                    return def.promise;
                };
                var getCityFromLat = function (lat, longit) {

                    var def = $q.defer();

                    $http({
                        method: "get",
                        headers: {
                            'Content-Type': "application/json",
                            'X-Auth-Token': undefined,
                            'Authorization': undefined
                        },
                        url: "http://maps.googleapis.com/maps/api/geocode/json?latlng=" + lat + "," + longit + "&sensor=true",
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                                def.reject("Failed to get nearby");
                            });

                    return def.promise;
                };
                var merchants = function (data) {

                    var def = $q.defer();
                    console.log("Getting merchants")
                    console.log(JSON.stringify(data));
                    $http({
                        method: "get",
                        url: "/api/merchants/nearby",
                        params: data
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                                console.log(JSON.stringify(data));
                                def.reject("Failed to get nearby merchants");
                            });

                    return def.promise;
                };
                var getLocationsTrip = function (data) {
                    console.log("Get locations trip: /historic_locations?target_id=" + data.user_id + "&trip_id=" + data.trip);
                    var def = $q.defer();
                    var url = "/historic_locations?target_id=" + data.user_id + "&trip_id=" + data.trip;
                    if (data.page) {
                        url = url + "&page=" + data.page;
                    }
                    if (data.perpage) {
                        url = url + "&per_page=" + data.perpage;
                    }
                    $http.get(url)
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                                def.reject("Failed to get nearby");
                            });

                    return def.promise;
                };
                var getUserHash = function (  ) {

                    var def = $q.defer();

                    $http({
                        method: "get",
                        url: "/api/locations/hash",
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                                console.log(JSON.stringify(data));
                                def.reject("Failed to get nearby");
                            });

                    return def.promise;
                };
                var getCitiesRegion = function (regionId) {

                    var def = $q.defer();

                    $http({
                        method: "get",
                        url: "/api/cities?limit=50&region_id=" + regionId,
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                                def.reject("Failed to get nearby");
                            });

                    return def.promise;
                };
                var getCities = function (where) {
                    let url = '/api/cities';
                    if (where) {
                        url = url + "?" + where;
                    }
                    var def = $q.defer();
                    $http({
                        method: "get",
                        url: url,
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                                def.reject("Failed to get nearby");
                            });
                    return def.promise;
                };
                var getRegionFromCity = function (city) {
                    var def = $q.defer();
                    $http.post("/api/cities/from", city
                            )
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                                def.reject(response);
                            });

                    return def.promise;
                };
                var getRegionsCountry = function (countryID) {

                    var def = $q.defer();

                    $http({
                        method: "get",
                        url: "/api/regions?limit=40&country_id=" + countryID,
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                                def.reject("Failed to get nearby");
                            });

                    return def.promise;
                };
                var getCountry = function (id) {
                    var def = $q.defer();
                    $http({
                        method: "get",
                        url: "/api/countries?id=" + id,
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                                def.reject("Failed to get nearby");
                            });

                    return def.promise;
                };
                var getCountries = function () {
                    var def = $q.defer();
                    $http({
                        method: "get",
                        url: "/api/countries?limit=50&order_by=name,asc",
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                                def.reject("Failed to get nearby");
                            });

                    return def.promise;
                };

                return {
                    shared: shared,
                    merchants: merchants,
                    getCountries: getCountries,
                    getCities:getCities,
                    getUserHash: getUserHash,
                    getCityFromLat: getCityFromLat,
                    getRegionFromCity: getRegionFromCity,
                    getLocationsTrip: getLocationsTrip,
                    getCitiesRegion: getCitiesRegion,
                    getRegionsCountry: getRegionsCountry,
                    getCountry: getCountry,
                    getRegionName: function (region_id) {
                        for (region in $rootScope.regions) {
                            var container = $rootScope.regions[region];
                            if (container.id == parseInt(region_id)) {
                                return container.name;
                            }
                        }
                        return null;
                    },
                    isRegionsLoaded: function () {
                        if ($rootScope.regions) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                    isCountriesLoaded: function () {
                        if ($rootScope.countries) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                    getRegions: function () {
                        return $rootScope.regions;
                    }
                };
            }])