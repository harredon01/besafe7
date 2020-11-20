angular.module('besafe')
        
        .service('Addresses',['$q', '$http', function ($q, $http) {

            var getAddresses = function () {
                var def = $q.defer();
                $http({
                    method: 'get',
                    url: '/api/addresses' 
                })
                        .success(function (data) {
                            // console.log(data);
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to get nearby");
                        });

                return def.promise;
                /**/

            }
            var saveAddress = function (data) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/addresses',
                        data: data, // pass in data as strings
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

            var deleteAddress = function (address) {
                var def = $q.defer();
                $http({
                    method: 'DELETE',
                    url: '/api/addresses/' + address
                })
                        .success(function (data) {
                            console.log(data);
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to Delete Address");
                        });

                return def.promise;
                /**/

            }

            return {
                getAddresses:getAddresses,
                saveAddress:saveAddress,
                deleteAddress:deleteAddress
            };
        }])