angular.module('besafe')
        .service('Categories',['$q', '$http', function ($q, $http) {

            var getCategories = function (typeCategory) {
                let url = "/api/categories/"+typeCategory;
                var def = $q.defer();
                $http({
                    method: 'get',
                    url: url 
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
                getCategories:getCategories,
            };
        }])