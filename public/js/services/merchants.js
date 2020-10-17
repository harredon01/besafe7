angular.module('besafe')

        .service('Merchants', function ($q, $http) {

            var getMerchants = function (where) {
                let url = "/api/merchants";
                if (where) {
                    url = url + "?" + where;
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
                            def.reject("Failed to get merchants");
                        });
                return def.promise;

            }
            var getMerchantsPrivate = function (where) {
                let url = "/api/merchants";
                if (where) {
                    url = url + "?" + where;
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
                            def.reject("Failed to get merchants");
                        });
                return def.promise;
            }
            var getStoreExport = function (data) {
                let url = '/api/admin/merchant/orders' ;
                var def = $q.defer();
                $http({
                    method: 'POST',
                    url: url,
                    data:data
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
            var getStoreContent = function (data) {
                let url = '/api/admin/merchant/content' ;
                var def = $q.defer();
                $http({
                    method: 'POST',
                    url: url,
                    data:data
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

            var searchMerchants = function (where) {
                let url = "/api/merchants/search";
                if (where) {
                    url = url + "?search=" + where;
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
                            def.reject("Failed to get merchants");
                        });
                return def.promise;

            }

            var saveMerchant = function (data) {
                var def = $q.defer();
                var url = "/api/merchants";
                var method = "POST";
                if (data.id) {
                    var url = "/api/merchants/" + data.id;
                    var method = "PATCH";
                }
                $http({
                    method: method,
                    url: url,
                    data: data, // pass in data as strings
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}  // set the headers so angular passing info as form data (not request payload)
                })
                        .success(function (data) {
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to save/create merchant");
                        });

                return def.promise;
                /**/

            }

            var deleteMerchant = function (merchant_id) {
                var def = $q.defer();
                $http({
                    method: 'DELETE',
                    url: '/api/merchants/' + merchant_id
                })
                        .success(function (data) {
                            console.log(data);
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to Delete Merchant");
                        });

                return def.promise;
                /**/

            }

            return {
                getMerchants: getMerchants,
                getMerchantsPrivate: getMerchantsPrivate,
                saveMerchant: saveMerchant,
                getStoreContent:getStoreContent,
                getStoreExport:getStoreExport,
                searchMerchants: searchMerchants,
                deleteMerchant: deleteMerchant
            };
        })