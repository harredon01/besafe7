angular.module('besafe')
        .service('Categories', function ($q, $http) {

            var getCategories = function (typeCategory) {
                let url = "/api/categories/"+typeCategory;
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
                            def.reject("Failed to get nearby");
                        });

                return def.promise;
                /**/

            }

            return {
                getCategories:getCategories,
            };
        })