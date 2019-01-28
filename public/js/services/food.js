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
                updateArticle:updateArticle
            };
        })