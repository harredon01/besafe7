angular.module('besafe')
        
        .service('Groups',['$q', '$http', function ($q, $http) {

            var getGroups = function () {
                var def = $q.defer();
                $http({
                    method: 'get',
                    url: '/api/groups' 
                })
                        .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to get nearby");
                        });

                return def.promise;
                /**/

            }
            var saveGroup = function (data) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/groups',
                        data: data, // pass in data as strings
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'}  // set the headers so angular passing info as form data (not request payload)
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to get nearby");
                        });

                return def.promise;
                /**/

            }
            return {
                saveGroup: saveGroup,
                getGroups:getGroups
            };
        }])