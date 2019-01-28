angular.module('besafe')

        .controller('MenuCtrl', function ($scope, Food) {
            $scope.data = {};
            $scope.articles;
            $scope.loadMore = true,
            $scope.page = 0;
            angular.element(document).ready(function () {
                $scope.getArticles();
            });
            
            $scope.buildArticleData = function (article) {
                article.attributes = JSON.parse(article.attributes);
                return article;
            }

            $scope.getArticles = function () {
                $scope.page++;
                let url = "includes=order&order_by=id,desc&page=" + $scope.page;
                Food.getArticles(url).then(function (data) {
                    if (data.page == data.last_page) {
                        $scope.loadMore = false;
                    }
                    let articlesCont = data.data;
                    for (item in articlesCont) {
                        articlesCont[item] = $scope.buildArticleData(articlesCont[item]);
                    }
                    $scope.articles = articlesCont;

                },
                        function (data) {

                        });
            }
            $scope.updateArticle = function (article) {

                Food.updateArticle(article).then(function (data) {

                },
                        function (data) {

                        });
            }

        })