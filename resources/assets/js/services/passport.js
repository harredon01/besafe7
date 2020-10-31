angular.module('besafe')
        
        .service('Passport',['$q', '$http', function ($q, $http) {

            var getOauthClients = function () {
                var def = $q.defer();
                $http({
                        method: 'GET',
                        url: '/oauth/clients'
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to get clients");
                        });

                return def.promise;
                /**/
            }
            var createOauthClient = function (data) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/oauth/clients',
                        data: data // pass in data as strings
                        //headers: {'Content-Type': 'application/x-www-form-urlencoded'}  // set the headers so angular passing info as form data (not request payload)
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to create client");
                        });

                return def.promise;
                /**/

            }
            var updateOauthClient = function (data,id) {
                var def = $q.defer();
                $http({
                        method: 'PUT',
                        url: '/oauth/clients/'+id,
                        data: data, // pass in data as strings
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'}  // set the headers so angular passing info as form data (not request payload)
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to update client");
                        });

                return def.promise;
                /**/

            }
            var deleteOauthClient = function (client) {
                var def = $q.defer();
                $http({
                        method: 'DELETE',
                        url: '/oauth/clients/'+client
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to delete client");
                        });

                return def.promise;
                /**/
            }
            var getScopes = function () {
                var def = $q.defer();
                $http({
                    method: 'get',
                    url: '/oauth/scopes' 
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
            var getPersonalAccessTokens = function () {
                var def = $q.defer();
                $http({
                    method: 'get',
                    url: '/oauth/personal-access-tokens' 
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
            var createPersonalAccessToken = function (data) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/oauth/personal-access-tokens',
                        data: data, // pass in data as strings
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'}  // set the headers so angular passing info as form data (not request payload)
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to create client");
                        });

                return def.promise;
                /**/

            }
            var deletePersonalAccessTokens = function (client) {
                var def = $q.defer();
                $http({
                        method: 'DELETE',
                        url: '/oauth/personal-access-tokens/'+client
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to delete client");
                        });

                return def.promise;
                /**/
            }
            var getTokens = function () {
                var def = $q.defer();
                $http({
                    method: 'get',
                    url: '/api/user/authtokens' 
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
            var deleteTokens = function (token) {
                var def = $q.defer();
                $http({
                        method: 'DELETE',
                        url: '/oauth/tokens/'+token
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to delete client");
                        });

                return def.promise;
                /**/
            }
            
            return {
                getOauthClients: getOauthClients,
                createOauthClient:createOauthClient,
                updateOauthClient:updateOauthClient,
                deleteOauthClient:deleteOauthClient,
                getScopes:getScopes,
                getPersonalAccessTokens:getPersonalAccessTokens,
                deletePersonalAccessTokens:deletePersonalAccessTokens,
                getTokens:getTokens,
                deleteTokens:deleteTokens,
                createPersonalAccessToken:createPersonalAccessToken,

            };
        }])